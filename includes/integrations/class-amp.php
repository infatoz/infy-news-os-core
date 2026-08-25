<?php
/**
 * Official AMP plugin integration (ampproject/amp-wp).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Makes news content AMP-eligible and keeps our scripts off AMP pages.
 */
class INOS_AMP {

	const PLUGIN_SLUG = 'amp';
	const PLUGIN_FILE = 'amp/amp.php';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'amp_supportable_post_types', array( __CLASS__, 'supportable_post_types' ), 20 );
		add_filter( 'amp_skip_post', array( __CLASS__, 'skip_web_stories' ), 10, 2 );
		add_filter( 'amp_default_options', array( __CLASS__, 'default_options' ) );
		add_action( 'init', array( __CLASS__, 'configure_paired_mode' ), 5 );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
	}

	/**
	 * Wrap AMP responses last so later plugins (Site Kit GTG) cannot re-inject JS.
	 *
	 * Must run on plugins_loaded before Site Kit starts its own buffer.
	 */
	public static function start_custom_js_guard() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		if ( ! self::is_amp_url() ) {
			return;
		}
		if ( ! defined( 'LITESPEED_NO_OPTM' ) ) {
			define( 'LITESPEED_NO_OPTM', true );
		}
		if ( defined( 'INOS_AMP_JS_GUARD' ) ) {
			return;
		}
		define( 'INOS_AMP_JS_GUARD', true );
		ob_start( array( __CLASS__, 'strip_custom_javascript' ) );
	}

	/**
	 * Drop non-AMP <script> tags and HTML event handlers (onload, etc.).
	 *
	 * Site Kit Google tag gateway prepends google_tags_first_party / /wvns/
	 * scripts after the AMP plugin sanitizer. Those are DISALLOWED_SCRIPT_TAG.
	 *
	 * @param string $html Buffered HTML.
	 * @return string
	 */
	public static function strip_custom_javascript( $html ) {
		if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<script' ) ) {
			return $html;
		}

		$html = preg_replace_callback(
			'#<script\b([^>]*)>(.*?)</script>#is',
			static function ( $m ) {
				$attrs = $m[1];
				if ( preg_match( '/type\s*=\s*([\'"])application\/(?:ld\+)?json\1/i', $attrs ) ) {
					return $m[0];
				}
				if ( preg_match( '/template\s*=\s*([\'"])amp-mustache\1/i', $attrs ) ) {
					return $m[0];
				}
				if ( preg_match( '/target\s*=\s*([\'"])amp-script\1/i', $attrs ) ) {
					return $m[0];
				}
				if ( false !== stripos( $attrs, 'cdn.ampproject.org' ) ) {
					return $m[0];
				}
				return '';
			},
			$html
		);

		if ( ! is_string( $html ) ) {
			return '';
		}

		// Keep AMP's on="tap:..." ; strip onload/onclick/onerror.
		$stripped = preg_replace( '/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\')/i', '', $html );
		return is_string( $stripped ) ? $stripped : $html;
	}

	/**
	 * Transitional + /amp/ path for new AMP installs.
	 *
	 * Canonical URLs stay the default theme. AMP is only served when the URL
	 * includes the /amp/ suffix (or ?amp=1 before this config is applied).
	 *
	 * @param array<string, mixed> $defaults AMP plugin defaults.
	 * @return array<string, mixed>
	 */
	public static function default_options( $defaults ) {
		if ( ! is_array( $defaults ) ) {
			$defaults = array();
		}
		if ( class_exists( 'AMP_Theme_Support' ) ) {
			$defaults['theme_support'] = AMP_Theme_Support::TRANSITIONAL_MODE_SLUG;
		} else {
			$defaults['theme_support'] = 'transitional';
		}
		$defaults['paired_url_structure'] = 'path_suffix';
		$defaults['mobile_redirect']      = false;
		return $defaults;
	}

	/**
	 * Keep the official AMP plugin in paired (Transitional) mode.
	 *
	 * Standard/AMP-first would render AMP HTML on every URL. Infy News OS
	 * serves the normal theme unless the request path ends in /amp/.
	 */
	public static function configure_paired_mode() {
		if ( ! class_exists( 'AMP_Options_Manager' ) || ! class_exists( 'AMP_Theme_Support' ) ) {
			return;
		}

		$want_mode      = AMP_Theme_Support::TRANSITIONAL_MODE_SLUG;
		$want_structure = 'path_suffix';
		$current_mode   = (string) AMP_Options_Manager::get_option( 'theme_support' );
		$current_path   = (string) AMP_Options_Manager::get_option( 'paired_url_structure' );
		$mobile         = AMP_Options_Manager::get_option( 'mobile_redirect' );
		$mobile_on      = ( true === $mobile || 1 === $mobile || '1' === $mobile );

		if ( $current_mode === $want_mode && $current_path === $want_structure && ! $mobile_on ) {
			return;
		}

		AMP_Options_Manager::update_options(
			array(
				'theme_support'        => $want_mode,
				'paired_url_structure' => $want_structure,
				'mobile_redirect'      => false,
				'plugin_configured'    => true,
			)
		);

		update_option( 'inos_flush_rewrites', '1' );
	}

	/**
	 * Whether the official AMP plugin is available.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return function_exists( 'amp_is_request' ) || self::plugin_active();
	}

	/**
	 * Whether the current request is an AMP document.
	 *
	 * @return bool
	 */
	public static function is_request() {
		if ( function_exists( 'amp_is_request' ) && amp_is_request() ) {
			return true;
		}
		return self::is_amp_url();
	}

	/**
	 * Detect an AMP URL before amp_is_request() is ready (head/enqueue).
	 *
	 * @return bool
	 */
	public static function is_amp_url() {
		if ( isset( $_GET['amp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$amp = strtolower( (string) wp_unslash( $_GET['amp'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' === $amp || '1' === $amp || 'true' === $amp ) {
				return true;
			}
		}
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$path = wp_parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		if ( ! is_string( $path ) || '' === $path ) {
			return false;
		}
		return (bool) preg_match( '#/amp/?$#', $path );
	}

	/**
	 * News articles and live blogs may be served as AMP.
	 *
	 * @param string[] $post_types Types.
	 * @return string[]
	 */
	public static function supportable_post_types( $post_types ) {
		$post_types   = is_array( $post_types ) ? $post_types : array();
		$post_types[] = 'post';
		$post_types[] = 'page';
		$post_types[] = 'inos_live_blog';
		return array_values( array_unique( $post_types ) );
	}

	/**
	 * Official Web Stories are already AMP documents; do not wrap them again.
	 *
	 * @param bool $skip    Skip.
	 * @param int  $post_id Post ID.
	 * @return bool
	 */
	public static function skip_web_stories( $skip, $post_id ) {
		if ( 'web-story' === get_post_type( $post_id ) ) {
			return true;
		}
		return $skip;
	}

	/**
	 * Prompt to install/activate AMP on our settings screens.
	 */
	public static function notice() {
		if ( self::is_active() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'inos' ) ) {
			return;
		}

		echo '<div class="notice notice-info"><p>';
		echo esc_html__( 'Infy News OS uses the official AMP plugin for AMP pages. Install it to serve AMP versions of articles and live blogs.', 'infy-news-os-core' );
		echo ' ';
		echo self::action_link_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</p></div>';
	}

	/**
	 * Install or activate link.
	 *
	 * @return string
	 */
	public static function action_link_html() {
		if ( self::plugin_installed() && current_user_can( 'activate_plugin', self::PLUGIN_FILE ) ) {
			$url  = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( self::PLUGIN_FILE ) ), 'activate-plugin_' . self::PLUGIN_FILE );
			$text = __( 'Activate AMP', 'infy-news-os-core' );
		} elseif ( current_user_can( 'install_plugins' ) ) {
			$url  = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . self::PLUGIN_SLUG ), 'install-plugin_' . self::PLUGIN_SLUG );
			$text = __( 'Install AMP', 'infy-news-os-core' );
		} else {
			$url  = 'https://wordpress.org/plugins/amp/';
			$text = __( 'Get AMP', 'infy-news-os-core' );
		}
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
	}

	/**
	 * Plugin file present.
	 *
	 * @return bool
	 */
	public static function plugin_installed() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		return isset( $plugins[ self::PLUGIN_FILE ] );
	}

	/**
	 * Plugin active.
	 *
	 * @return bool
	 */
	public static function plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( self::PLUGIN_FILE );
	}
}
