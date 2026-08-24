<?php
/**
 * Plugin Name:       Infy News OS Core
 * Plugin URI:        https://infatoz.com
 * Description:       Core engine for Infy News OS — settings, editorial workflow, schema, Google News/Discover/Search optimization, AMP and Web Stories (official plugins), ads, and newsletter.
 * Version:           1.6.22
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Infatoz Technologies LLP
 * Author URI:        https://infatoz.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       infy-news-os-core
 * Domain Path:       /languages
 * Update URI:        https://infatoz.com
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

define( 'INOS_CORE_VERSION', '1.6.22' );
define( 'INOS_CORE_FILE', __FILE__ );
define( 'INOS_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'INOS_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'INOS_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'INOS_CORE_OPTION', 'inos_settings' );

require_once INOS_CORE_PATH . 'includes/class-autoloader.php';
INOS_Autoloader::register();

require_once INOS_CORE_PATH . 'includes/class-helpers.php';

register_activation_hook( INOS_CORE_FILE, array( 'INOS_Activator', 'activate' ) );
register_deactivation_hook( INOS_CORE_FILE, array( 'INOS_Activator', 'deactivate' ) );
add_action( 'upgrader_process_complete', array( 'INOS_Activator', 'on_upgrader_complete' ), 10, 2 );

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'infy-news-os-core', false, dirname( INOS_CORE_BASENAME ) . '/languages' );
		INOS_Plugin::instance()->init();
	}
);
