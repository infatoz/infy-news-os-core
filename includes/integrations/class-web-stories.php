<?php
/**
 * Official Web Stories plugin integration (Google).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Homepage Web Stories rail powered by the official plugin.
 */
class INOS_Web_Stories {

	const PLUGIN_SLUG = 'web-stories';
	const PLUGIN_FILE = 'web-stories/web-stories.php';
	const POST_TYPE   = 'web-story';
	const POSTER_META = 'web_stories_poster';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
	}

	/**
	 * Whether stories can be queried.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return post_type_exists( self::POST_TYPE ) || self::plugin_active();
	}

	/**
	 * Homepage section should render.
	 *
	 * @return bool
	 */
	public static function show_on_home() {
		return (bool) inos_get_option( 'show_home_web_stories', 1 ) && self::is_active() && self::has_stories();
	}

	/**
	 * Published stories exist.
	 *
	 * @return bool
	 */
	public static function has_stories() {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return false;
		}
		$q = new WP_Query(
			array(
				'post_type'           => self::POST_TYPE,
				'post_status'         => 'publish',
				'posts_per_page'      => 1,
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		return $q->have_posts();
	}

	/**
	 * Markup for the homepage rail (official Story_Query / shortcode, then a poster fallback).
	 *
	 * @param string $view  Optional view override.
	 * @param int    $count Optional count override.
	 * @return string
	 */
	public static function home_embed_html( $view = '', $count = 0 ) {
		if ( ! self::is_active() ) {
			return '';
		}

		$view  = $view ? sanitize_key( $view ) : (string) inos_get_option( 'web_stories_view', 'circles' );
		$count = $count ? absint( $count ) : max( 1, absint( inos_get_option( 'web_stories_count', 10 ) ) );
		if ( ! in_array( $view, array( 'circles', 'carousel', 'grid' ), true ) ) {
			$view = 'circles';
		}

		if ( class_exists( '\Google\Web_Stories\Story_Query' ) ) {
			$query = new \Google\Web_Stories\Story_Query(
				array(
					'view_type'         => $view,
					'show_title'        => ( 'circles' !== $view ),
					'show_archive_link' => false,
					'circle_size'       => 96,
					'class'             => 'inos-web-stories__embed',
				),
				array(
					'posts_per_page' => $count,
					'post_status'    => 'publish',
					'orderby'        => 'post_date',
					'order'          => 'DESC',
				)
			);
			$html = method_exists( $query, 'render' ) ? $query->render() : '';
			if ( $html ) {
				return $html;
			}
		}

		if ( shortcode_exists( 'web_stories' ) ) {
			$html = do_shortcode(
				sprintf(
					'[web_stories view="%1$s" number_of_stories="%2$d" title="%3$s" circle_size="96" class="inos-web-stories__embed"]',
					esc_attr( $view ),
					$count,
					( 'circles' === $view ) ? 'false' : 'true'
				)
			);
			if ( $html && false === strpos( $html, '[web_stories' ) ) {
				return $html;
			}
		}

		return self::fallback_html( $count );
	}

	/**
	 * Archive URL for “all stories”.
	 *
	 * @return string
	 */
	public static function archive_url() {
		if ( ! post_type_exists( self::POST_TYPE ) ) {
			return '';
		}
		$link = get_post_type_archive_link( self::POST_TYPE );
		return $link ? $link : '';
	}

	/**
	 * Simple poster rail if the plugin renderer is unavailable.
	 *
	 * @param int $count Count.
	 * @return string
	 */
	public static function fallback_html( $count ) {
		$stories = get_posts(
			array(
				'post_type'           => self::POST_TYPE,
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, absint( $count ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( ! $stories ) {
			return '';
		}

		ob_start();
		echo '<ul class="inos-web-stories__track">';
		foreach ( $stories as $story ) {
			$url   = get_permalink( $story );
			$title = get_the_title( $story );
			$img   = get_the_post_thumbnail_url( $story, 'medium' );
			if ( ! $img ) {
				$poster = get_post_meta( $story->ID, self::POSTER_META, true );
				if ( is_array( $poster ) && ! empty( $poster['url'] ) ) {
					$img = (string) $poster['url'];
				}
			}
			$target = ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) ? ' target="_top"' : '';
			echo '<li class="inos-web-stories__item">';
			echo '<a class="inos-web-stories__link" href="' . esc_url( $url ) . '"' . $target . '>';
			if ( $img ) {
				echo '<span class="inos-web-stories__poster"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" loading="lazy" decoding="async" /></span>';
			} else {
				echo '<span class="inos-web-stories__poster inos-web-stories__poster--empty" aria-hidden="true"></span>';
			}
			echo '<span class="inos-web-stories__label">' . esc_html( $title ) . '</span>';
			echo '</a></li>';
		}
		echo '</ul>';
		return (string) ob_get_clean();
	}

	/**
	 * Prompt when homepage Stories are on but the plugin is missing.
	 */
	public static function notice() {
		if ( self::is_active() || ! inos_get_option( 'show_home_web_stories', 1 ) ) {
			return;
		}
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'inos' ) ) {
			return;
		}

		echo '<div class="notice notice-info"><p>';
		echo esc_html__( 'The homepage Web Stories section uses the official Web Stories plugin. Install it, then publish stories to show the rail.', 'infy-news-os-core' );
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
			$text = __( 'Activate Web Stories', 'infy-news-os-core' );
		} elseif ( current_user_can( 'install_plugins' ) ) {
			$url  = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . self::PLUGIN_SLUG ), 'install-plugin_' . self::PLUGIN_SLUG );
			$text = __( 'Install Web Stories', 'infy-news-os-core' );
		} else {
			$url  = 'https://wordpress.org/plugins/web-stories/';
			$text = __( 'Get Web Stories', 'infy-news-os-core' );
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
