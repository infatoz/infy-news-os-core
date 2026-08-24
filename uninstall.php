<?php
/**
 * Uninstall cleanup.
 *
 * @package InfyNewsOS
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'inos_settings' );
delete_option( 'inos_flush_rewrites' );
delete_option( 'inos_core_version' );
delete_option( 'inos_pages_seeded' );
delete_option( 'inos_article_types_seeded' );
delete_option( 'inos_demo_state' );
delete_option( 'inos_sitemap_rewrite' );
delete_option( 'inos_setup_complete' );

global $wpdb;
$table = $wpdb->prefix . 'inos_subscribers';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
