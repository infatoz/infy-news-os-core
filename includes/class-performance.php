<?php
/**
 * Optional front-end performance tweaks.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Emoji / embed cleanup.
 */
class INOS_Performance {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_disable_emojis' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_disable_embeds' ), 1 );
	}

	/**
	 * Disable emoji assets.
	 */
	public static function maybe_disable_emojis() {
		if ( ! inos_get_option( 'disable_emojis', 1 ) ) {
			return;
		}
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Disable embed script.
	 */
	public static function maybe_disable_embeds() {
		if ( inos_get_option( 'disable_embeds', 0 ) ) {
			wp_dequeue_script( 'wp-embed' );
		}
	}
}
