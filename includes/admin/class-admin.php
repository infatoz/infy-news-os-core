<?php
/**
 * Settings admin UI.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Infy News OS settings hub.
 */
class INOS_Admin {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'customizer_menu' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . INOS_CORE_BASENAME, array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Settings link.
	 *
	 * @param array<int, string> $links Links.
	 * @return array<int, string>
	 */
	public static function action_links( $links ) {
		$url     = admin_url( 'admin.php?page=inos-settings' );
		$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'infy-news-os-core' ) . '</a>';
		if ( class_exists( 'INOS_Setup' ) ) {
			$links[] = '<a href="' . esc_url( INOS_Setup::url( 'welcome' ) ) . '">' . esc_html__( 'Setup wizard', 'infy-news-os-core' ) . '</a>';
		}
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=inos-settings&tab=demo' ) ) . '">' . esc_html__( 'Import demo', 'infy-news-os-core' ) . '</a>';
		return $links;
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'Infy News OS', 'infy-news-os-core' ),
			__( 'Infy News OS', 'infy-news-os-core' ),
			'manage_options',
			'inos-settings',
			array( __CLASS__, 'render' ),
			'dashicons-media-document',
			58
		);

		add_submenu_page(
			'inos-settings',
			__( 'Settings', 'infy-news-os-core' ),
			__( 'Settings', 'infy-news-os-core' ),
			'manage_options',
			'inos-settings',
			array( __CLASS__, 'render' )
		);

		add_submenu_page(
			'inos-settings',
			__( 'Subscribers', 'infy-news-os-core' ),
			__( 'Subscribers', 'infy-news-os-core' ),
			'manage_options',
			'inos-subscribers',
			array( 'INOS_Newsletter', 'admin_page' )
		);
	}

	/**
	 * Add a Customizer shortcut under Infy News OS.
	 */
	public static function customizer_menu() {
		global $submenu;
		if ( empty( $submenu['inos-settings'] ) ) {
			return;
		}
		if ( current_user_can( 'edit_theme_options' ) ) {
			$url = add_query_arg(
				array(
					'autofocus[section]' => 'inos_theme_light',
					'url'              => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Theme colors', 'infy-news-os-core' ),
				'edit_theme_options',
				$url,
			);
			$fonts_url = add_query_arg(
				array(
					'autofocus[section]' => 'inos_theme_fonts',
					'url'              => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Fonts', 'infy-news-os-core' ),
				'edit_theme_options',
				$fonts_url,
			);
			$look_url = add_query_arg(
				array(
					'autofocus[section]' => 'inos_theme_look',
					'url'                => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Site look', 'infy-news-os-core' ),
				'edit_theme_options',
				$look_url,
			);
			$layout_url = add_query_arg(
				array(
					'autofocus[section]' => 'inos_theme_layout',
					'url'                => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Layout', 'infy-news-os-core' ),
				'edit_theme_options',
				$layout_url,
			);
			$header_url = add_query_arg(
				array(
					'autofocus[panel]' => 'inos_header',
					'url'              => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Header', 'infy-news-os-core' ),
				'edit_theme_options',
				$header_url,
			);
			$footer_url = add_query_arg(
				array(
					'autofocus[panel]' => 'inos_footer',
					'url'              => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Footer', 'infy-news-os-core' ),
				'edit_theme_options',
				$footer_url,
			);
		}
		if ( current_user_can( 'manage_options' ) ) {
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Homepage builder', 'infy-news-os-core' ),
				'manage_options',
				'admin.php?page=inos-settings&tab=builder',
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Article sidebar', 'infy-news-os-core' ),
				'manage_options',
				'admin.php?page=inos-settings&tab=article-sidebar',
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Mobile menu', 'infy-news-os-core' ),
				'manage_options',
				'admin.php?page=inos-settings&tab=drawer',
			);
			$submenu['inos-settings'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				__( 'Demo content', 'infy-news-os-core' ),
				'manage_options',
				'admin.php?page=inos-settings&tab=demo',
			);
		}
	}

	/**
	 * Admin CSS.
	 *
	 * @param string $hook Hook.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( $hook, 'inos-' ) && 'toplevel_page_inos-settings' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'inos-admin', INOS_CORE_URL . 'admin/css/inos-admin.css', array(), INOS_CORE_VERSION );
		wp_enqueue_script( 'inos-admin', INOS_CORE_URL . 'admin/js/inos-admin.js', array( 'jquery' ), INOS_CORE_VERSION, true );

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( in_array( $tab, array( 'builder', 'article-sidebar', 'drawer' ), true ) ) {
			self::enqueue_builder( $tab );
		}
		if ( 'demo' === $tab ) {
			wp_enqueue_script( 'inos-demo', INOS_CORE_URL . 'admin/js/inos-demo.js', array(), INOS_CORE_VERSION, true );
			wp_localize_script(
				'inos-demo',
				'inosDemo',
				array(
					'ajax'     => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'inos_demo' ),
					'starting' => __( 'Preparing the newsroom…', 'infy-news-os-core' ),
					'error'    => __( 'Import failed. Try again, or check the PHP error log.', 'infy-news-os-core' ),
				)
			);
		}
	}

	/**
	 * Homepage or article sidebar builder script.
	 *
	 * @param string $tab builder|article-sidebar|drawer.
	 */
	private static function enqueue_builder( $tab ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'inos-builder',
			INOS_CORE_URL . 'admin/js/inos-builder.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			INOS_CORE_VERSION,
			true
		);
		$tax = class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::tax_choices() : array( 'categories' => array(), 'tags' => array() );
		$authors = class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::author_choices() : array( 0 => __( 'All authors', 'infy-news-os-core' ) );
		$orderby = array(
			'date'          => __( 'Latest', 'infy-news-os-core' ),
			'modified'      => __( 'Recently updated', 'infy-news-os-core' ),
			'comment_count' => __( 'Most commented', 'infy-news-os-core' ),
			'views'         => __( 'Most viewed', 'infy-news-os-core' ),
			'rand'          => __( 'Random', 'infy-news-os-core' ),
		);
		$slots = array(
			'between_cards' => __( 'Between cards', 'infy-news-os-core' ),
			'header'        => __( 'Header', 'infy-news-os-core' ),
			'below_ticker'  => __( 'Below ticker', 'infy-news-os-core' ),
			'sidebar'       => __( 'Sidebar', 'infy-news-os-core' ),
			'footer'        => __( 'Footer', 'infy-news-os-core' ),
		);
		if ( 'drawer' === $tab && class_exists( 'INOS_Drawer' ) ) {
			wp_localize_script(
				'inos-builder',
				'inosBuilder',
				array(
					'types'          => INOS_Drawer::types(),
					'layouts'        => array(
						'hero'    => array(),
						'block'   => array(),
						'stories' => array(),
						'split'   => array(),
					),
					'cats'           => $tax['categories'],
					'tags'           => $tax['tags'],
					'authors'        => $authors,
					'menus'          => INOS_Drawer::menu_choices(),
					'defaults'       => INOS_Drawer::defaults(),
					'blank'          => INOS_Drawer::blank( 'menu' ),
					'orderby'        => $orderby,
					'slots'          => $slots,
					'defaultAdSlot'  => 'sidebar',
					'resetConfirm'   => __( 'Replace the stack with the default mobile menu (date, search, navigation, sections, social, widgets, subscribe)?', 'infy-news-os-core' ),
				)
			);
			return;
		}
		if ( 'article-sidebar' === $tab && class_exists( 'INOS_Article_Sidebar' ) ) {
			wp_localize_script(
				'inos-builder',
				'inosBuilder',
				array(
					'types'          => INOS_Article_Sidebar::types(),
					'layouts'        => array(
						'hero'    => array(),
						'block'   => INOS_Article_Sidebar::layouts(),
						'stories' => array(),
						'split'   => array(),
					),
					'cats'           => $tax['categories'],
					'tags'           => $tax['tags'],
					'authors'        => $authors,
					'defaults'       => INOS_Article_Sidebar::defaults(),
					'blank'          => INOS_Article_Sidebar::blank( 'posts' ),
					'orderby'        => $orderby,
					'slots'          => $slots,
					'defaultAdSlot'  => 'sidebar',
					'resetConfirm'   => __( 'Replace the stack with the default article sidebar (ad, trending, WordPress widgets)?', 'infy-news-os-core' ),
				)
			);
			return;
		}
		wp_localize_script(
			'inos-builder',
			'inosBuilder',
			array(
				'types'          => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::types() : array(),
				'layouts'        => array(
					'hero'    => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::layouts( 'hero' ) : array(),
					'block'   => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::layouts( 'block' ) : array(),
					'stories' => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::layouts( 'stories' ) : array(),
					'split'   => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::layouts( 'split' ) : array(),
				),
				'cats'           => $tax['categories'],
				'tags'           => $tax['tags'],
				'authors'        => $authors,
				'defaults'       => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::defaults() : array(),
				'blank'          => class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::blank( 'posts' ) : array(),
				'orderby'        => $orderby,
				'slots'          => $slots,
				'defaultAdSlot'  => 'between_cards',
				'resetConfirm'   => __( 'Replace the stack with the current homepage layout (hero, sections, latest/trending, newsletter)?', 'infy-news-os-core' ),
			)
		);
	}

	/**
	 * Save settings POST.
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['inos_settings_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inos_settings_nonce'] ) ), 'inos_save_settings' ) ) {
			return;
		}

		$posted = isset( $_POST['inos'] ) && is_array( $_POST['inos'] ) ? wp_unslash( $_POST['inos'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		INOS_Settings::update( $posted );

		if ( isset( $_POST['inos_labels'] ) && is_array( $_POST['inos_labels'] ) ) {
			INOS_Labels::save( wp_unslash( $_POST['inos_labels'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( isset( $_POST['inos_home_modules'] ) ) {
			INOS_Home_Builder::save( wp_unslash( $_POST['inos_home_modules'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( isset( $_POST['inos_article_sidebar'] ) ) {
			INOS_Article_Sidebar::save( wp_unslash( $_POST['inos_article_sidebar'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( isset( $_POST['inos_drawer'] ) ) {
			INOS_Drawer::save( wp_unslash( $_POST['inos_drawer'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		add_settings_error( 'inos_settings', 'inos_saved', __( 'Settings saved.', 'infy-news-os-core' ), 'updated' );
	}

	/**
	 * Tabs.
	 *
	 * @return array<string, string>
	 */
	public static function tabs() {
		return array(
			'dashboard'   => __( 'Dashboard', 'infy-news-os-core' ),
			'general'     => __( 'General', 'infy-news-os-core' ),
			'labels'      => __( 'Language / Labels', 'infy-news-os-core' ),
			'publisher'   => __( 'Publisher', 'infy-news-os-core' ),
			'seo'         => __( 'SEO / Search', 'infy-news-os-core' ),
			'google-news' => __( 'Google News', 'infy-news-os-core' ),
			'tracking'    => __( 'Tracking', 'infy-news-os-core' ),
			'schema'      => __( 'Schema', 'infy-news-os-core' ),
			'editorial'   => __( 'Editorial', 'infy-news-os-core' ),
			'homepage'    => __( 'Homepage', 'infy-news-os-core' ),
			'builder'     => __( 'Homepage builder', 'infy-news-os-core' ),
			'article-sidebar' => __( 'Article sidebar', 'infy-news-os-core' ),
			'drawer'      => __( 'Mobile menu', 'infy-news-os-core' ),
			'ads'         => __( 'Ads', 'infy-news-os-core' ),
			'newsletter'  => __( 'Newsletter', 'infy-news-os-core' ),
			'performance' => __( 'Performance', 'infy-news-os-core' ),
			'images'      => __( 'Images', 'infy-news-os-core' ),
			'amp-stories' => __( 'AMP & Stories', 'infy-news-os-core' ),
			'demo'        => __( 'Demo content', 'infy-news-os-core' ),
		);
	}

	/**
	 * Sidebar groups for the admin panel.
	 *
	 * @return array<string, array{label:string, tabs:string[]}>
	 */
	public static function tab_groups() {
		return array(
			'overview' => array(
				'label' => __( 'Overview', 'infy-news-os-core' ),
				'tabs'  => array( 'dashboard' ),
			),
			'design'   => array(
				'label' => __( 'Design', 'infy-news-os-core' ),
				'tabs'  => array( 'builder', 'homepage', 'article-sidebar', 'drawer', 'labels' ),
			),
			'newsroom' => array(
				'label' => __( 'Newsroom', 'infy-news-os-core' ),
				'tabs'  => array( 'general', 'editorial', 'publisher', 'demo' ),
			),
			'search'   => array(
				'label' => __( 'Search', 'infy-news-os-core' ),
				'tabs'  => array( 'seo', 'google-news', 'schema', 'tracking' ),
			),
			'grow'     => array(
				'label' => __( 'Grow', 'infy-news-os-core' ),
				'tabs'  => array( 'ads', 'newsletter', 'amp-stories' ),
			),
			'speed'    => array(
				'label' => __( 'Speed', 'infy-news-os-core' ),
				'tabs'  => array( 'performance', 'images' ),
			),
		);
	}

	/**
	 * Dashboard metric cards.
	 *
	 * @return array<int, array{label:string, value:string, url:string}>
	 */
	public static function dashboard_stats() {
		$posts = wp_count_posts( 'post' );
		$live  = post_type_exists( 'inos_live_blog' ) ? wp_count_posts( 'inos_live_blog' ) : null;
		$subs  = 0;
		if ( class_exists( 'INOS_Newsletter' ) ) {
			global $wpdb;
			$table = INOS_Newsletter::table();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$subs = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );
		}
		$mods = class_exists( 'INOS_Home_Builder' ) ? count( INOS_Home_Builder::all() ) : 0;
		$amp  = class_exists( 'INOS_AMP' ) && INOS_AMP::is_active();
		$ws   = class_exists( 'INOS_Web_Stories' ) && INOS_Web_Stories::is_active();

		return array(
			array(
				'label' => __( 'Published stories', 'infy-news-os-core' ),
				'value' => isset( $posts->publish ) ? (string) $posts->publish : '0',
				'url'   => admin_url( 'edit.php' ),
			),
			array(
				'label' => __( 'Live blogs', 'infy-news-os-core' ),
				'value' => ( $live && isset( $live->publish ) ) ? (string) $live->publish : '0',
				'url'   => admin_url( 'edit.php?post_type=inos_live_blog' ),
			),
			array(
				'label' => __( 'Subscribers', 'infy-news-os-core' ),
				'value' => (string) $subs,
				'url'   => admin_url( 'admin.php?page=inos-subscribers' ),
			),
			array(
				'label' => __( 'Homepage blocks', 'infy-news-os-core' ),
				'value' => (string) $mods,
				'url'   => admin_url( 'admin.php?page=inos-settings&tab=builder' ),
			),
			array(
				'label' => __( 'AMP', 'infy-news-os-core' ),
				'value' => $amp ? __( 'On', 'infy-news-os-core' ) : __( 'Off', 'infy-news-os-core' ),
				'url'   => admin_url( 'admin.php?page=inos-settings&tab=amp-stories' ),
			),
			array(
				'label' => __( 'Web Stories', 'infy-news-os-core' ),
				'value' => $ws ? __( 'On', 'infy-news-os-core' ) : __( 'Off', 'infy-news-os-core' ),
				'url'   => admin_url( 'admin.php?page=inos-settings&tab=amp-stories' ),
			),
		);
	}

	/**
	 * Render settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		settings_errors( 'inos_settings' );
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tabs = self::tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'dashboard';
		}
		$s = INOS_Settings::all();
		include INOS_CORE_PATH . 'admin/views/settings-page.php';
	}
}
