<?php
/**
 * Main plugin controller.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Boots all Infy News OS Core modules.
 */
class INOS_Plugin {

	/**
	 * Singleton.
	 *
	 * @var INOS_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Instance.
	 *
	 * @return INOS_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register modules.
	 */
	public function init() {
		INOS_Labels::init();
		INOS_Taxonomies::init();
		INOS_Images::init();
		INOS_Meta::init();
		INOS_Author::init();
		INOS_Pages::init();
		INOS_Statuses::init();
		INOS_Breaking::init();
		INOS_Liveblog::init();
		INOS_SEO::init();
		INOS_Tracking::init();
		INOS_Schema::init();
		INOS_Sitemaps::init();
		INOS_Feeds::init();
		INOS_Ads::init();
		INOS_Newsletter::init();
		INOS_Trending::init();
		INOS_Performance::init();
		INOS_Admin::init();
		INOS_Setup::init();
		INOS_Metabox::init();
		INOS_Presets::init();
		INOS_Customizer::init();
		INOS_Archives::init();
		INOS_Demo::init();
		INOS_GitHub_Updater::init();
		INOS_AMP::init();
		if ( class_exists( 'INOS_Push' ) ) {
			INOS_Push::init();
		}
		INOS_Web_Stories::init();

		INOS_Activator::maybe_upgrade();
		add_action( 'init', array( 'INOS_Activator', 'maybe_flush_rewrites' ), 99 );
	}
}
