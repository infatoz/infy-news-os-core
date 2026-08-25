<?php
/**
 * Optional front-end performance tweaks.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Emoji / embed cleanup, unused CSS, and console-noise endpoints.
 */
class INOS_Performance {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_disable_emojis' ), 20 );
		add_action( 'init', array( __CLASS__, 'maybe_quiet_wvns' ), 0 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_unused' ), 100 );
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

	/**
	 * Drop Gutenberg CSS on PHP listings; keep it on articles and inner pages.
	 */
	public static function dequeue_unused() {
		if ( is_admin() ) {
			return;
		}
		$keep_blocks = is_singular() && ! is_front_page();
		if ( $keep_blocks ) {
			return;
		}
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'core-block-supports' );
	}

	/**
	 * SWG / tooling sometimes requests /wvns/ and Lighthouse flags the 404.
	 */
	public static function maybe_quiet_wvns() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$path = wp_parse_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return;
		}
		$path = untrailingslashit( $path );
		if ( '/wvns' !== $path ) {
			return;
		}
		status_header( 204 );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		header( 'Cache-Control: public, max-age=86400' );
		exit;
	}
}
