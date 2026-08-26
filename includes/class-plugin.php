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
		$modules = array(
			'INOS_Labels',
			'INOS_Taxonomies',
			'INOS_Images',
			'INOS_Meta',
			'INOS_Author',
			'INOS_Pages',
			'INOS_Statuses',
			'INOS_Breaking',
			'INOS_Liveblog',
			'INOS_SEO',
			'INOS_Tracking',
			'INOS_Schema',
			'INOS_Sitemaps',
			'INOS_Feeds',
			'INOS_Ads',
			'INOS_Newsletter',
			'INOS_Trending',
			'INOS_Performance',
			'INOS_Admin',
			'INOS_Setup',
			'INOS_Metabox',
			'INOS_Presets',
			'INOS_Customizer',
			'INOS_Archives',
			'INOS_Demo',
			'INOS_GitHub_Updater',
			'INOS_AMP',
			'INOS_Web_Stories',
		);

		foreach ( $modules as $class ) {
			try {
				if ( class_exists( $class ) ) {
					$class::init();
				}
			} catch ( \Throwable $e ) {
				self::log_boot_error( $class, $e );
			}
		}

		try {
			INOS_Activator::maybe_upgrade();
		} catch ( \Throwable $e ) {
			self::log_boot_error( 'INOS_Activator::maybe_upgrade', $e );
		}

		add_action( 'init', array( 'INOS_Activator', 'maybe_flush_rewrites' ), 99 );
	}

	/**
	 * Record a boot failure without taking the whole site down.
	 *
	 * @param string     $where Module or method.
	 * @param \Throwable $e     Error.
	 */
	private static function log_boot_error( $where, $e ) {
		$line = gmdate( 'c' ) . ' ' . $where . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
		if ( defined( 'WP_CONTENT_DIR' ) ) {
			@file_put_contents( WP_CONTENT_DIR . '/inos-last-fatal.txt', $line, FILE_APPEND );
		}
		if ( function_exists( 'error_log' ) ) {
			error_log( 'Infy News OS Core ' . $line );
		}
	}
}
