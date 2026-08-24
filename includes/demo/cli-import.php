<?php
/**
 * CLI-only demo import. Not a web endpoint.
 *
 * @package InfyNewsOS
 */

if ( defined( 'ABSPATH' ) ) {
	return;
}

if ( php_sapi_name() !== 'cli' ) {
	exit;
}

define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 5 ) . '/wp-load.php';

if ( ! class_exists( 'INOS_Demo' ) ) {
	fwrite( STDERR, "Infy News OS Core is not active.\n" );
	exit( 1 );
}

$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
if ( ! empty( $admins[0] ) ) {
	wp_set_current_user( (int) $admins[0] );
}

$result = INOS_Demo::import_all();
if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}

$state = INOS_Demo::state();
printf(
	"Imported %d stories, %d live blogs, %d web stories, %d media files.\n",
	count( array_unique( $state['posts'] ) ),
	count( $state['live_blogs'] ),
	count( $state['web_stories'] ),
	count( array_unique( $state['attachments'] ) )
);
exit( 0 );
