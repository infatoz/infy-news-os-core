<?php
/**
 * Google Tag Manager, share UTM, and Preferred Sources.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end tracking and Google publisher widgets.
 */
class INOS_Tracking {

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_head', array( __CLASS__, 'head' ), 0 );
		add_action( 'wp_body_open', array( __CLASS__, 'body_open' ), 0 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Validated GTM container ID, or empty.
	 *
	 * @return string
	 */
	public static function container_id() {
		return self::sanitize_container_id( (string) inos_get_option( 'gtm_container_id', '' ) );
	}

	/**
	 * Normalize GTM-XXXX IDs.
	 *
	 * @param string $id Raw ID.
	 * @return string
	 */
	public static function sanitize_container_id( $id ) {
		$id = strtoupper( preg_replace( '/[^A-Z0-9\-]/i', '', (string) $id ) );
		if ( ! preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ) {
			return '';
		}
		return $id;
	}

	/**
	 * Host used for Preferred Sources (production domain on staging).
	 *
	 * @return string
	 */
	public static function preferred_source_host() {
		$override = trim( (string) inos_get_option( 'preferred_source_domain', '' ) );
		if ( $override ) {
			$host = wp_parse_url( esc_url_raw( $override ), PHP_URL_HOST );
			if ( $host ) {
				return $host;
			}
			return strtolower( preg_replace( '#^https?://#i', '', $override ) );
		}
		$host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		return $host ? $host : '';
	}

	/**
	 * Deeplink into Google source preferences.
	 *
	 * @return string
	 */
	public static function preferred_source_url() {
		$host = self::preferred_source_host();
		if ( ! $host ) {
			return '';
		}
		return 'https://www.google.com/preferences/source?q=' . rawurlencode( $host );
	}

	/**
	 * dataLayer bootstrap + GTM snippet.
	 */
	public static function head() {
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}

		$payload = self::page_payload();
		echo "<script>window.dataLayer=window.dataLayer||[];\n";
		if ( $payload ) {
			echo 'window.dataLayer.push(' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ");\n";
		}
		echo "</script>\n";

		$id = self::container_id();
		if ( ! $id || ! inos_get_option( 'enable_gtm', 0 ) ) {
			return;
		}

		echo "<!-- Google Tag Manager (Infy News OS) -->\n";
		echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js( $id ) . "');</script>\n";
		echo "<!-- End Google Tag Manager -->\n";
	}

	/**
	 * GTM noscript iframe.
	 */
	public static function body_open() {
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		$id = self::container_id();
		if ( ! $id || ! inos_get_option( 'enable_gtm', 0 ) ) {
			return;
		}
		echo '<!-- Google Tag Manager (noscript) -->';
		echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $id ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
		echo '<!-- End Google Tag Manager (noscript) -->' . "\n";
	}

	/**
	 * Article context for GTM / GA4.
	 *
	 * @return array<string, mixed>|null
	 */
	private static function page_payload() {
		if ( ! is_singular( array( 'post', 'inos_live_blog' ) ) ) {
			return array(
				'event'      => 'inos_page_view',
				'page_type'  => self::page_type(),
				'page_title' => wp_get_document_title(),
			);
		}

		$post_id = get_the_ID();
		$section = function_exists( 'inos_get_primary_section' ) ? inos_get_primary_section( $post_id ) : null;
		$type    = is_singular( 'inos_live_blog' ) ? 'live_blog' : 'article';

		return array(
			'event'             => 'inos_article_view',
			'page_type'         => $type,
			'content_type'      => $type,
			'item_id'           => (string) $post_id,
			'item_name'         => wp_strip_all_tags( get_the_title( $post_id ) ),
			'item_category'     => $section ? $section->name : '',
			'item_category_id'  => $section ? (string) $section->term_id : '',
			'author'            => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
			'publication_date'  => get_post_time( DATE_W3C, true, $post_id ),
		);
	}

	/**
	 * Coarse page type for GTM.
	 *
	 * @return string
	 */
	private static function page_type() {
		if ( is_front_page() ) {
			return 'home';
		}
		if ( is_category() ) {
			return 'section';
		}
		if ( is_tag() ) {
			return 'topic';
		}
		if ( is_author() ) {
			return 'author';
		}
		if ( is_search() ) {
			return 'search';
		}
		if ( is_page() ) {
			return 'page';
		}
		return 'other';
	}

	/**
	 * Preferred Sources library + share tracking.
	 */
	public static function assets() {
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		$singular       = is_singular( array( 'post', 'inos_live_blog' ) );
		$listing        = ( is_archive() || is_home() || is_search() ) && ! is_front_page();
		$show_preferred = inos_get_option( 'enable_preferred_source', 1 ) && $singular;

		if ( $show_preferred ) {
			wp_enqueue_script(
				'inos-preferred-source',
				'https://news.google.com/swg/js/v1/publisher.js',
				array(),
				null,
				array(
					'strategy'  => 'async',
					'in_footer' => false,
				)
			);
		}

		$needs_share = $singular || $listing;

		if ( ! $needs_share ) {
			return;
		}

		wp_enqueue_script(
			'inos-share',
			INOS_CORE_URL . 'public/js/inos-share.js',
			array(),
			INOS_CORE_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		$item_id      = '';
		$item_name    = '';
		$content_type = self::page_type();
		$campaign     = (string) inos_get_option( 'utm_campaign', 'article-share' );

		if ( $singular ) {
			$post_id      = get_the_ID();
			$item_id      = (string) $post_id;
			$item_name    = wp_strip_all_tags( get_the_title( $post_id ) );
			$content_type = 'article';
		} elseif ( function_exists( 'inos_get_archive_share_context' ) ) {
			$ctx          = inos_get_archive_share_context();
			$item_id      = (string) $ctx['utm_content'];
			$item_name    = (string) $ctx['title'];
			$content_type = (string) $ctx['type'];
			if ( '' === $campaign || 'article-share' === $campaign ) {
				$campaign = 'archive-share';
			}
		}

		wp_localize_script(
			'inos-share',
			'inosShare',
			array(
				'copied'      => inos_label( 'link_copied' ),
				'copyFailed'  => inos_label( 'copy_failed' ),
				'shared'      => inos_label( 'shared' ),
				'itemId'      => $item_id,
				'itemName'    => $item_name,
				'contentType' => $content_type,
				'utmCampaign' => $campaign,
				'utmMedium'   => (string) inos_get_option( 'utm_medium', 'social' ),
			)
		);
	}

	/**
	 * Official Google Preferred Sources button + deeplink fallback.
	 */
	public static function render_preferred_source_button() {
		if ( ! inos_get_option( 'enable_preferred_source', 1 ) ) {
			return;
		}

		$url   = self::preferred_source_url();
		$theme = (string) inos_get_option( 'preferred_source_theme', 'light' );
		$lang  = sanitize_text_field( (string) inos_get_option( 'preferred_source_lang', '' ) );
		if ( ! in_array( $theme, array( 'light', 'dark' ), true ) ) {
			$theme = 'light';
		}

		echo '<div class="inos-preferred-source">';
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			if ( $url ) {
				echo '<p class="inos-preferred-source__fallback">';
				echo '<a class="inos-preferred-source__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">';
				echo esc_html( inos_label( 'preferred_source' ) );
				echo '</a></p>';
			}
			echo '</div>';
			return;
		}
		echo '<div google-add-preferred-source-btn data-theme="' . esc_attr( $theme ) . '"';
		if ( $lang ) {
			echo ' data-lang="' . esc_attr( $lang ) . '"';
		}
		echo '></div>';

		if ( $url ) {
			echo '<p class="inos-preferred-source__fallback">';
			echo '<a class="inos-preferred-source__link" data-inos-preferred-source="deeplink" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">';
			echo esc_html( inos_label( 'preferred_source' ) );
			echo '</a></p>';
			echo '<noscript><p><a href="' . esc_url( $url ) . '">' . esc_html( inos_label( 'preferred_source' ) ) . '</a></p></noscript>';
		}
		echo '</div>';
	}
}
