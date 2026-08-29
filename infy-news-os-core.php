<?php
/**
 * Plugin Name:       Infy News OS Core
 * Plugin URI:        https://infatoz.com
 * Description:       Core engine for Infy News OS — settings, editorial workflow, schema, Google News/Discover/Search optimization, AMP and Web Stories (official plugins), ads, and newsletter.
 * Version:           1.6.42
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Infatoz Technologies LLP
 * Author URI:        https://infatoz.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       infy-news-os-core
 * Domain Path:       /languages
 * Update URI:        https://github.com/infatoz/infy-news-os-core
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'INOS_CORE_VERSION' ) ) {
	return;
}

define( 'INOS_CORE_VERSION', '1.6.42' );
define( 'INOS_CORE_FILE', __FILE__ );
define( 'INOS_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'INOS_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'INOS_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'INOS_CORE_OPTION', 'inos_settings' );

require_once INOS_CORE_PATH . 'includes/class-autoloader.php';
INOS_Autoloader::register();
require_once INOS_CORE_PATH . 'includes/class-activator.php';

register_activation_hook( INOS_CORE_FILE, array( 'INOS_Activator', 'activate' ) );
register_deactivation_hook( INOS_CORE_FILE, array( 'INOS_Activator', 'deactivate' ) );
add_action( 'upgrader_process_complete', array( 'INOS_Activator', 'on_upgrader_complete' ), 10, 2 );

register_shutdown_function(
	static function () {
		$err = error_get_last();
		if ( ! is_array( $err ) ) {
			return;
		}
		$fatal = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
		if ( ! in_array( (int) $err['type'], $fatal, true ) ) {
			return;
		}
		$line = gmdate( 'c' ) . ' ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] . "\n";
		$dir  = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : dirname( INOS_CORE_PATH );
		@file_put_contents( $dir . '/inos-last-fatal.txt', $line, FILE_APPEND );
	}
);

/*
 * Plugins → Activate includes this file after the theme has already loaded.
 * Theme fallbacks define inos_get_option, and Hostinger can run activate()
 * before $wp_rewrite exists. Load only what activation needs, then return.
 */
if ( defined( 'WP_SANDBOX_SCRAPING' ) ) {
	return;
}

require_once INOS_CORE_PATH . 'includes/class-helpers.php';

/*
 * Site Kit (and similar) open output buffers on plugins_loaded and can inject
 * custom <script> tags after the AMP sanitizer. Start our strip buffer first.
 */
add_action(
	'plugins_loaded',
	static function () {
		try {
			if ( class_exists( 'INOS_AMP' ) ) {
				INOS_AMP::start_custom_js_guard();
			}
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				@file_put_contents(
					WP_CONTENT_DIR . '/inos-last-fatal.txt',
					gmdate( 'c' ) . ' AMP guard: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n",
					FILE_APPEND
				);
			}
		}
	},
	-999
);

add_action(
	'init',
	static function () {
		try {
			load_plugin_textdomain( 'infy-news-os-core', false, dirname( INOS_CORE_BASENAME ) . '/languages' );
			INOS_Plugin::instance()->init();
		} catch ( \Throwable $e ) {
			$line = gmdate( 'c' ) . ' boot: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				@file_put_contents( WP_CONTENT_DIR . '/inos-last-fatal.txt', $line, FILE_APPEND );
			}
		}
	},
	1
);
