<?php
/**
 * First-run setup wizard.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Guides publishers after the plugin and theme are activated.
 */
class INOS_Setup {

	const OPTION    = 'inos_setup_complete';
	const TRANSIENT = 'inos_setup_redirect';
	const PAGE      = 'inos-setup';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 12 );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'after_switch_theme', array( __CLASS__, 'on_theme_switch' ) );
		add_filter( 'plugin_action_links_' . INOS_CORE_BASENAME, array( __CLASS__, 'plugin_links' ), 20 );
		add_filter( 'install_plugin_complete_actions', array( __CLASS__, 'install_complete_actions' ), 10, 3 );
	}

	/**
	 * Fire after a fresh plugin activation.
	 */
	public static function schedule_redirect() {
		if ( self::is_complete() ) {
			return;
		}
		set_transient( self::TRANSIENT, 1, 120 );
	}

	/**
	 * Theme switched to Infy News OS.
	 */
	public static function on_theme_switch() {
		if ( self::is_complete() ) {
			return;
		}
		if ( 'infy-news-os' !== get_template() ) {
			return;
		}
		set_transient( self::TRANSIENT, 1, 120 );
	}

	/**
	 * Whether the wizard was finished or skipped.
	 *
	 * @return bool
	 */
	public static function is_complete() {
		return (bool) get_option( self::OPTION, 0 );
	}

	/**
	 * Mark finished.
	 */
	public static function mark_complete() {
		update_option( self::OPTION, 1, true );
		delete_transient( self::TRANSIENT );
	}

	/**
	 * Wizard URL.
	 *
	 * @param string $step Step id.
	 * @return string
	 */
	public static function url( $step = 'welcome' ) {
		return add_query_arg(
			array(
				'page' => self::PAGE,
				'step' => sanitize_key( $step ),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Hidden under Infy News OS.
	 */
	public static function menu() {
		add_submenu_page(
			'inos-settings',
			__( 'Setup wizard', 'infy-news-os-core' ),
			__( 'Setup wizard', 'infy-news-os-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Redirect once after activation.
	 */
	public static function maybe_redirect() {
		if ( ! get_transient( self::TRANSIENT ) ) {
			return;
		}
		if ( self::is_complete() || ! current_user_can( 'manage_options' ) ) {
			delete_transient( self::TRANSIENT );
			return;
		}
		if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}
		if ( is_network_admin() ) {
			return;
		}
		if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( self::PAGE === $page ) {
			delete_transient( self::TRANSIENT );
			return;
		}
		delete_transient( self::TRANSIENT );
		wp_safe_redirect( self::url( 'welcome' ) );
		exit;
	}

	/**
	 * Finish / skip / save options.
	 */
	public static function handle_post() {
		if ( isset( $_GET['inos_setup_skip'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( wp_verify_nonce( $nonce, 'inos_setup_skip' ) ) {
				self::mark_complete();
				wp_safe_redirect( admin_url( 'admin.php?page=inos-settings' ) );
				exit;
			}
		}

		if ( ! isset( $_POST['inos_setup_nonce'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inos_setup_nonce'] ) ), 'inos_setup' ) ) {
			return;
		}

		$action = isset( $_POST['inos_setup_action'] ) ? sanitize_key( wp_unslash( $_POST['inos_setup_action'] ) ) : '';

		if ( 'skip' === $action ) {
			self::mark_complete();
			wp_safe_redirect( admin_url( 'admin.php?page=inos-settings' ) );
			exit;
		}

		if ( 'save' === $action ) {
			self::save_quick_options();
			self::mark_complete();
			wp_safe_redirect( self::url( 'done' ) );
			exit;
		}

		if ( 'finish' === $action ) {
			self::mark_complete();
			wp_safe_redirect( admin_url( 'admin.php?page=inos-settings' ) );
			exit;
		}
	}

	/**
	 * Skip link (not a nested form).
	 */
	public static function skip_button() {
		$url = wp_nonce_url(
			add_query_arg(
				array(
					'page'            => self::PAGE,
					'inos_setup_skip' => '1',
				),
				admin_url( 'admin.php' )
			),
			'inos_setup_skip'
		);
		echo '<a class="button-link inos-setup__skip" href="' . esc_url( $url ) . '">' . esc_html__( 'Skip setup', 'infy-news-os-core' ) . '</a>';
	}

	/**
	 * Persist wizard checkboxes and text.
	 */
	private static function save_quick_options() {
		$posted = isset( $_POST['inos'] ) && is_array( $_POST['inos'] ) ? wp_unslash( $_POST['inos'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$checks = array(
			'sticky_header_desktop',
			'sticky_header_mobile',
			'show_breaking_ticker',
			'show_subscribe_cta',
			'show_home_newsletter',
			'enable_seo',
			'enable_news_sitemap',
			'enable_schema',
			'show_progress_bar',
			'article_reader_tools',
			'sticky_share',
			'show_reading_time',
		);
		foreach ( $checks as $key ) {
			$posted[ $key ] = empty( $posted[ $key ] ) ? 0 : 1;
		}
		INOS_Settings::update( $posted );

		if ( ! empty( $_POST['inos_pretty_permalinks'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_option( 'permalink_structure', '/%category%/%postname%/' );
			flush_rewrite_rules( false );
		}

		if ( ! empty( $_POST['inos_static_home'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			self::ensure_static_home();
		}
	}

	/**
	 * Homepage builder needs a static front page.
	 */
	private static function ensure_static_home() {
		if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) ) {
			return;
		}
		$page = get_page_by_path( 'home' );
		if ( ! $page ) {
			$id = wp_insert_post(
				array(
					'post_title'  => __( 'Home', 'infy-news-os-core' ),
					'post_name'   => 'home',
					'post_status' => 'publish',
					'post_type'   => 'page',
					'post_content'=> '',
				),
				true
			);
			if ( is_wp_error( $id ) || ! $id ) {
				return;
			}
		} else {
			$id = (int) $page->ID;
		}
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $id );
	}

	/**
	 * Nag until the wizard is finished.
	 */
	public static function notice() {
		if ( self::is_complete() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( self::PAGE === $page ) {
			return;
		}
		echo '<div class="notice notice-info"><p>';
		echo esc_html__( 'Finish the Infy News OS setup wizard to pick a look, install companion plugins, and turn on news essentials.', 'infy-news-os-core' );
		echo ' <a href="' . esc_url( self::url( 'welcome' ) ) . '">' . esc_html__( 'Open setup wizard', 'infy-news-os-core' ) . '</a>';
		echo '</p></div>';
	}

	/**
	 * Plugin row links.
	 *
	 * @param array<int, string> $links Links.
	 * @return array<int, string>
	 */
	public static function plugin_links( $links ) {
		$links[] = '<a href="' . esc_url( self::url( 'plugins' ) ) . '">' . esc_html__( 'Required plugins', 'infy-news-os-core' ) . '</a>';
		$look    = add_query_arg(
			array(
				'autofocus[section]' => 'inos_theme_look',
				'url'                => home_url( '/' ),
			),
			admin_url( 'customize.php' )
		);
		if ( current_user_can( 'edit_theme_options' ) ) {
			$links[] = '<a href="' . esc_url( $look ) . '">' . esc_html__( 'Site look', 'infy-news-os-core' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Assets on the wizard screen.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, 'inos-setup' ) ) {
			return;
		}
		wp_enqueue_style( 'inos-admin', INOS_CORE_URL . 'admin/css/inos-admin.css', array(), INOS_CORE_VERSION );
	}

	/**
	 * After installing AMP / Stories from the wizard, offer a way back.
	 *
	 * @param array<string, string> $actions Actions.
	 * @param object|array          $api     Plugin API.
	 * @param string                $plugin_file Plugin file.
	 * @return array<string, string>
	 */
	public static function install_complete_actions( $actions, $api, $plugin_file ) {
		$slug = '';
		if ( is_object( $api ) && ! empty( $api->slug ) ) {
			$slug = (string) $api->slug;
		}
		if ( ! in_array( $slug, array( 'amp', 'web-stories', 'akismet' ), true ) ) {
			return $actions;
		}
		$actions['inos_setup'] = '<a href="' . esc_url( self::url( 'plugins' ) ) . '">' . esc_html__( 'Return to Infy News OS setup', 'infy-news-os-core' ) . '</a>';
		return $actions;
	}

	/**
	 * PHP, theme, and permalink checks.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function environment() {
		$theme_ok   = 'infy-news-os' === get_template();
		$theme      = wp_get_theme();
		$pretty     = (string) get_option( 'permalink_structure' );
		$front_page = 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' );
		$php_ok     = version_compare( PHP_VERSION, '7.4', '>=' );

		return array(
			array(
				'label'  => __( 'Infy News OS Core', 'infy-news-os-core' ),
				'ok'     => true,
				'detail' => sprintf(
					/* translators: %s: plugin version */
					__( 'Active · %s', 'infy-news-os-core' ),
					INOS_CORE_VERSION
				),
			),
			array(
				'label'  => __( 'Infy News OS theme', 'infy-news-os-core' ),
				'ok'     => $theme_ok,
				'detail' => $theme_ok
					? sprintf(
						/* translators: %s: theme version */
						__( 'Active · %s', 'infy-news-os-core' ),
						$theme->get( 'Version' )
					)
					: __( 'Activate Infy News OS so Core settings, AMP pairing, and the homepage builder render on the public site.', 'infy-news-os-core' ),
			),
			array(
				'label'  => __( 'PHP', 'infy-news-os-core' ),
				'ok'     => $php_ok,
				'detail' => $php_ok
					? PHP_VERSION
					: sprintf(
						/* translators: %s: detected PHP version */
						__( '%s — 7.4 or newer is required.', 'infy-news-os-core' ),
						PHP_VERSION
					),
			),
			array(
				'label'  => __( 'Pretty permalinks', 'infy-news-os-core' ),
				'ok'     => (bool) $pretty,
				'detail' => $pretty
					? $pretty
					: __( 'Plain IDs break AMP /amp/ URLs and news sitemaps. Use /%category%/%postname%/.', 'infy-news-os-core' ),
			),
			array(
				'label'  => __( 'Homepage', 'infy-news-os-core' ),
				'ok'     => true,
				'detail' => $front_page
					? __( 'A static front page is set. The homepage builder will render there.', 'infy-news-os-core' )
					: __( 'Latest posts is fine — front-page.php still loads the builder. A static Home page is optional.', 'infy-news-os-core' ),
			),
		);
	}

	/**
	 * Companion plugins and the required theme.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function companions() {
		return array(
			array(
				'id'       => 'theme',
				'kind'     => 'theme',
				'required' => true,
				'name'     => __( 'Infy News OS', 'infy-news-os-core' ),
				'why'      => __( 'Required front-end theme. Core settings, schema, and AMP pairing expect this theme.', 'infy-news-os-core' ),
				'slug'     => 'infy-news-os',
				'url'      => 'https://github.com/infatoz/infy-news-os',
			),
			array(
				'id'       => 'amp',
				'kind'     => 'plugin',
				'required' => true,
				'name'     => __( 'AMP', 'infy-news-os-core' ),
				'why'      => __( 'Official AMP plugin. Infy News OS does not generate AMP HTML itself. Needed for /amp/ article URLs.', 'infy-news-os-core' ),
				'slug'     => 'amp',
				'file'     => 'amp/amp.php',
				'url'      => 'https://wordpress.org/plugins/amp/',
			),
			array(
				'id'       => 'web-stories',
				'kind'     => 'plugin',
				'required' => true,
				'name'     => __( 'Web Stories', 'infy-news-os-core' ),
				'why'      => __( 'Official Web Stories plugin. Powers the homepage Stories rail. Stories are already AMP documents.', 'infy-news-os-core' ),
				'slug'     => 'web-stories',
				'file'     => 'web-stories/web-stories.php',
				'url'      => 'https://wordpress.org/plugins/web-stories/',
			),
			array(
				'id'       => 'akismet',
				'kind'     => 'plugin',
				'required' => false,
				'name'     => __( 'Akismet Anti-Spam', 'infy-news-os-core' ),
				'why'      => __( 'Recommended for article comments so spam does not land on news pages.', 'infy-news-os-core' ),
				'slug'     => 'akismet',
				'file'     => 'akismet/akismet.php',
				'url'      => 'https://wordpress.org/plugins/akismet/',
			),
		);
	}

	/**
	 * Status for one companion.
	 *
	 * @param array<string, mixed> $item Catalog row.
	 * @return array<string, mixed>
	 */
	public static function companion_status( $item ) {
		$out = $item;
		$out['state']  = 'missing';
		$out['action'] = '';
		if ( 'theme' === $item['kind'] ) {
			$theme = wp_get_theme( 'infy-news-os' );
			if ( $theme->exists() ) {
				$out['state'] = ( 'infy-news-os' === get_template() ) ? 'active' : 'installed';
				if ( 'installed' === $out['state'] && current_user_can( 'switch_themes' ) ) {
					$out['action'] = sprintf(
						'<a class="button button-primary" href="%s">%s</a>',
						esc_url( wp_nonce_url( admin_url( 'themes.php?action=activate&stylesheet=infy-news-os' ), 'switch-theme_infy-news-os' ) ),
						esc_html__( 'Activate theme', 'infy-news-os-core' )
					);
				}
			} else {
				$out['action'] = sprintf(
					'<a class="button" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $item['url'] ),
					esc_html__( 'Get theme', 'infy-news-os-core' )
				);
			}
			return $out;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$file = isset( $item['file'] ) ? $item['file'] : '';
		$all  = get_plugins();
		if ( $file && isset( $all[ $file ] ) ) {
			$out['state'] = is_plugin_active( $file ) ? 'active' : 'installed';
			if ( 'installed' === $out['state'] && current_user_can( 'activate_plugin', $file ) ) {
				$out['action'] = sprintf(
					'<a class="button button-primary" href="%s">%s</a>',
					esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $file ) ), 'activate-plugin_' . $file ) ),
					esc_html__( 'Activate', 'infy-news-os-core' )
				);
			}
		} elseif ( current_user_can( 'install_plugins' ) && ! empty( $item['slug'] ) ) {
			$out['action'] = sprintf(
				'<a class="button button-primary" href="%s">%s</a>',
				esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $item['slug'] ), 'install-plugin_' . $item['slug'] ) ),
				esc_html__( 'Install', 'infy-news-os-core' )
			);
		} else {
			$out['action'] = sprintf(
				'<a class="button" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $item['url'] ),
				esc_html__( 'Get plugin', 'infy-news-os-core' )
			);
		}
		return $out;
	}

	/**
	 * Steps.
	 *
	 * @return array<string, string>
	 */
	public static function steps() {
		return array(
			'welcome' => __( 'Welcome', 'infy-news-os-core' ),
			'plugins' => __( 'Required plugins', 'infy-news-os-core' ),
			'options' => __( 'Quick options', 'infy-news-os-core' ),
			'done'    => __( 'Done', 'infy-news-os-core' ),
		);
	}

	/**
	 * Render wizard.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$steps = self::steps();
		$step  = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : 'welcome'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $steps[ $step ] ) ) {
			$step = 'welcome';
		}
		$s = class_exists( 'INOS_Settings' ) ? INOS_Settings::all() : array();
		include INOS_CORE_PATH . 'admin/views/setup-wizard.php';
	}
}
