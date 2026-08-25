<?php
/**
 * Ad slots and ads.txt.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Monetization slots.
 */
class INOS_Ads {

	/**
	 * Slot ids.
	 *
	 * @return string[]
	 */
	public static function slots() {
		return array( 'header', 'below_ticker', 'in_article', 'sidebar', 'between_cards', 'sticky_mobile', 'footer' );
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrites' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_ads_txt' ), 0 );
		add_filter( 'the_content', array( __CLASS__, 'inject_in_article' ), 20 );
	}

	/**
	 * ads.txt rewrite.
	 */
	public static function register_rewrites() {
		add_rewrite_rule( '^ads\.txt$', 'index.php?inos_ads_txt=1', 'top' );
	}

	/**
	 * Query var.
	 *
	 * @param array<int, string> $vars Vars.
	 * @return array<int, string>
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'inos_ads_txt';
		return $vars;
	}

	/**
	 * Serve ads.txt.
	 */
	public static function maybe_ads_txt() {
		if ( ! get_query_var( 'inos_ads_txt' ) ) {
			return;
		}
		$content = (string) inos_get_option( 'ads_txt', '' );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo $content ? $content : '# ads.txt managed by Infy News OS Core'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Render a slot.
	 *
	 * @param string $id Slot id.
	 */
	public static function render( $id ) {
		$id = sanitize_key( $id );
		if ( ! in_array( $id, self::slots(), true ) ) {
			return;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		if ( ! inos_get_option( 'ad_' . $id . '_enable', 0 ) ) {
			return;
		}
		$html = (string) inos_get_option( 'ad_' . $id . '_html', '' );
		if ( '' === trim( $html ) ) {
			return;
		}
		$min = absint( inos_get_option( 'ad_' . $id . '_min_height', 90 ) );
		$class = 'inos-ad inos-ad--' . $id;
		if ( 'sticky_mobile' === $id ) {
			$class .= ' inos-ad--sticky-mobile';
		}
		echo '<aside class="' . esc_attr( $class ) . '" style="min-height:' . esc_attr( (string) $min ) . 'px" aria-label="' . esc_attr__( 'Advertisement', 'infy-news-os-core' ) . '">';
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin-managed ad tags.
		echo '</aside>';
	}

	/**
	 * Insert in-article ad after N paragraphs.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public static function inject_in_article( $content ) {
		if ( is_admin() || ! is_singular( array( 'post', 'inos_live_blog' ) ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return $content;
		}
		if ( ! inos_get_option( 'ad_in_article_enable', 0 ) ) {
			return $content;
		}
		$html = (string) inos_get_option( 'ad_in_article_html', '' );
		if ( '' === trim( $html ) ) {
			return $content;
		}

		$n    = max( 1, absint( inos_get_option( 'in_article_paragraph', 2 ) ) );
		$min  = absint( inos_get_option( 'ad_in_article_min_height', 250 ) );
		$slot = '<aside class="inos-ad inos-ad--in_article" style="min-height:' . esc_attr( (string) $min ) . 'px" aria-label="' . esc_attr__( 'Advertisement', 'infy-news-os-core' ) . '">' . $html . '</aside>';

		$parts = preg_split( '/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! $parts ) {
			return $content;
		}

		$p = 0;
		$out = '';
		foreach ( $parts as $part ) {
			$out .= $part;
			if ( preg_match( '/<\/p>/i', $part ) ) {
				++$p;
				if ( $p === $n ) {
					$out .= $slot;
				}
			}
		}
		return $out;
	}
}
