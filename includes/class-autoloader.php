<?php
/**
 * Class autoloader.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'INOS_Autoloader' ) ) {
	return;
}

/**
 * Maps INOS_* classes to files.
 */
class INOS_Autoloader {

	/**
	 * Class map.
	 *
	 * @var array<string, string>
	 */
	private static $map = array(
		'INOS_Activator'    => 'includes/class-activator.php',
		'INOS_Plugin'       => 'includes/class-plugin.php',
		'INOS_Settings'     => 'includes/class-settings.php',
		'INOS_Labels'       => 'includes/class-labels.php',
		'INOS_Home_Builder' => 'includes/class-home-builder.php',
		'INOS_Fonts'        => 'includes/class-fonts.php',
		'INOS_Presets'      => 'includes/class-presets.php',
		'INOS_Article_Sidebar' => 'includes/class-article-sidebar.php',
		'INOS_Drawer'          => 'includes/class-drawer.php',
		'INOS_Taxonomies'   => 'includes/content/class-taxonomies.php',
		'INOS_Meta'         => 'includes/content/class-meta.php',
		'INOS_Images'       => 'includes/content/class-images.php',
		'INOS_Author'       => 'includes/content/class-author.php',
		'INOS_Pages'        => 'includes/content/class-pages.php',
		'INOS_Statuses'     => 'includes/editorial/class-statuses.php',
		'INOS_Breaking'     => 'includes/editorial/class-breaking.php',
		'INOS_Liveblog'     => 'includes/liveblog/class-liveblog.php',
		'INOS_SEO'          => 'includes/seo/class-seo.php',
		'INOS_Tracking'     => 'includes/tracking/class-tracking.php',
		'INOS_Schema'       => 'includes/schema/class-schema.php',
		'INOS_Sitemaps'     => 'includes/sitemaps/class-sitemaps.php',
		'INOS_Feeds'        => 'includes/feeds/class-feeds.php',
		'INOS_Ads'          => 'includes/ads/class-ads.php',
		'INOS_Newsletter'   => 'includes/newsletter/class-newsletter.php',
		'INOS_Trending'     => 'includes/trending/class-trending.php',
		'INOS_Admin'        => 'includes/admin/class-admin.php',
		'INOS_Setup'        => 'includes/admin/class-setup.php',
		'INOS_Metabox'      => 'includes/admin/class-metabox.php',
		'INOS_Performance'  => 'includes/class-performance.php',
		'INOS_Customizer'   => 'includes/customizer/class-customizer.php',
		'INOS_Customize_Post_Control' => 'includes/customizer/class-control-post.php',
		'INOS_Demo'         => 'includes/demo/class-demo.php',
		'INOS_Demo_Catalog' => 'includes/demo/class-demo-catalog.php',
		'INOS_GitHub_Updater' => 'includes/class-github-updater.php',
		'INOS_AMP'          => 'includes/integrations/class-amp.php',
		'INOS_Archives'     => 'includes/class-archives.php',
		'INOS_Web_Stories'  => 'includes/integrations/class-web-stories.php',
	);

	/**
	 * Register spl autoload.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * Load a class file.
	 *
	 * @param string $class Class name.
	 */
	public static function load( $class ) {
		if ( empty( self::$map[ $class ] ) ) {
			return;
		}

		$file = INOS_CORE_PATH . self::$map[ $class ];
		if ( ! is_readable( $file ) ) {
			return;
		}
		try {
			require_once $file;
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				@file_put_contents(
					WP_CONTENT_DIR . '/inos-last-fatal.txt',
					gmdate( 'c' ) . ' autoload ' . $class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n",
					FILE_APPEND
				);
			}
		}
	}
}
