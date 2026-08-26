<?php
/**
 * Image sizes, lazy-load, and Google News / Discover / Search / Image Search.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers image sizes and front-end optimization.
 */
class INOS_Images {

	const DISCOVER_MIN_WIDTH = 1200;

	/**
	 * First in-content image on this request.
	 *
	 * @var int
	 */
	private static $content_img_index = 0;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'after_setup_theme', array( __CLASS__, 'register_sizes' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_editor' ) );
		add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'thumbnail_notice' ), 10, 3 );

		add_filter( 'image_editor_output_format', array( __CLASS__, 'output_format' ) );
		add_filter( 'jpeg_quality', array( __CLASS__, 'jpeg_quality' ) );
		add_filter( 'wp_editor_set_quality', array( __CLASS__, 'jpeg_quality' ) );
		add_filter( 'big_image_size_threshold', array( __CLASS__, 'big_image_threshold' ) );

		add_filter( 'wp_lazy_loading_enabled', array( __CLASS__, 'lazy_loading' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'attachment_attrs' ), 10, 3 );
		add_filter( 'wp_calculate_image_sizes', array( __CLASS__, 'image_sizes_attr' ), 10, 5 );
		add_filter( 'wp_content_img_tag', array( __CLASS__, 'content_img_tag' ), 10, 3 );
		add_filter( 'wp_get_loading_optimization_attributes', array( __CLASS__, 'loading_optimization' ), 10, 4 );

		add_action( 'wp_head', array( __CLASS__, 'preload_lcp' ), 2 );
		add_action( 'add_attachment', array( __CLASS__, 'auto_alt_on_upload' ) );
		add_filter( 'wp_insert_attachment_data', array( __CLASS__, 'descriptive_title' ), 10, 4 );

		if ( get_option( 'inos_images_rewrite', '' ) !== '1.4.1' ) {
			update_option( 'inos_flush_rewrites', '1' );
			update_option( 'inos_images_rewrite', '1.4.1' );
		}
	}

	/**
	 * Custom image sizes used by the theme and Google surfaces.
	 */
	public static function register_sizes() {
		add_image_size( 'inos-discover', 1200, 675, true );  // 16:9 Discover / Top Stories.
		add_image_size( 'inos-photo-4x3', 1200, 900, true ); // 4:3 Discover.
		add_image_size( 'inos-photo-1x1', 1200, 1200, true ); // 1:1 Discover.
		add_image_size( 'inos-og', 1200, 630, true );         // Open Graph.
		add_image_size( 'inos-card', 800, 450, true );
		add_image_size( 'inos-thumb', 400, 225, true );
	}

	/**
	 * Serve WebP when the editor supports it.
	 *
	 * @param array<string, string> $formats Formats.
	 * @return array<string, string>
	 */
	public static function output_format( $formats ) {
		if ( ! inos_get_option( 'image_webp', 1 ) ) {
			return $formats;
		}
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}

	/**
	 * JPEG / WebP quality.
	 *
	 * @param int $quality Quality.
	 * @return int
	 */
	public static function jpeg_quality( $quality ) {
		$q = absint( inos_get_option( 'image_quality', 82 ) );
		if ( $q < 60 ) {
			$q = 60;
		}
		if ( $q > 95 ) {
			$q = 95;
		}
		return $q;
	}

	/**
	 * Keep a high-resolution original for Image Search / Discover.
	 *
	 * @param int $threshold Threshold.
	 * @return int|false
	 */
	public static function big_image_threshold( $threshold ) {
		if ( ! inos_get_option( 'keep_original_images', 1 ) ) {
			return $threshold;
		}
		return 2560;
	}

	/**
	 * Native lazy-load for images and iframes.
	 *
	 * @param bool   $default  Default.
	 * @param string $tag_name Tag.
	 * @param string $context  Context.
	 * @return bool
	 */
	public static function lazy_loading( $default, $tag_name, $context ) {
		unset( $context );
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return $default;
		}
		if ( ! inos_get_option( 'enable_lazy_load', 1 ) ) {
			return false;
		}
		if ( 'iframe' === $tag_name ) {
			return (bool) inos_get_option( 'lazy_iframes', 1 );
		}
		return true;
	}

	/**
	 * Attachment img attributes: lazy/eager, decoding, alt, dimensions.
	 *
	 * @param array<string, mixed> $attr       Attributes.
	 * @param WP_Post              $attachment Attachment.
	 * @param string|int[]         $size       Size.
	 * @return array<string, mixed>
	 */
	public static function attachment_attrs( $attr, $attachment, $size ) {
		if ( is_admin() || ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) ) {
			return $attr;
		}

		$lcp = ! empty( $attr['data-inos-lcp'] ) || ( ! empty( $attr['fetchpriority'] ) && 'high' === $attr['fetchpriority'] );
		unset( $attr['data-inos-lcp'] );

		if ( $lcp && inos_get_option( 'skip_lcp_lazy', 1 ) ) {
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
		} elseif ( inos_get_option( 'enable_lazy_load', 1 ) ) {
			if ( empty( $attr['loading'] ) ) {
				$attr['loading'] = 'lazy';
			}
			if ( empty( $attr['fetchpriority'] ) ) {
				$attr['fetchpriority'] = 'auto';
			}
		}

		if ( empty( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}

		if ( empty( $attr['alt'] ) ) {
			$attr['alt'] = self::alt_for_attachment( (int) $attachment->ID );
		}

		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( $meta ) {
			$dims = is_string( $size ) ? image_get_intermediate_size( $attachment->ID, $size ) : false;
			if ( $dims && ! empty( $dims['width'] ) && empty( $attr['width'] ) ) {
				$attr['width']  = (int) $dims['width'];
				$attr['height'] = (int) $dims['height'];
			} elseif ( ! empty( $meta['width'] ) && empty( $attr['width'] ) ) {
				$attr['width']  = (int) $meta['width'];
				$attr['height'] = (int) $meta['height'];
			}
		}

		return $attr;
	}

	/**
	 * Responsive sizes for Infy image sizes.
	 *
	 * @param string       $sizes   Sizes attr.
	 * @param string|int[] $size    Size.
	 * @param string|null  $image_src Src.
	 * @param array|null   $image_meta Meta.
	 * @param int          $attachment_id ID.
	 * @return string
	 */
	public static function image_sizes_attr( $sizes, $size, $image_src = null, $image_meta = null, $attachment_id = 0 ) {
		unset( $image_src, $image_meta, $attachment_id );
		$name = is_array( $size ) ? '' : (string) $size;
		$map  = array(
			'inos-discover'  => '(max-width: 1200px) 100vw, 1200px',
			'inos-og'        => '(max-width: 1200px) 100vw, 1200px',
			'inos-photo-4x3' => '(max-width: 1200px) 100vw, 1200px',
			'inos-photo-1x1' => '(max-width: 1200px) 100vw, 1200px',
			'inos-card'      => '(max-width: 700px) 100vw, 400px',
			'inos-thumb'     => '(max-width: 700px) 40vw, 200px',
		);
		return isset( $map[ $name ] ) ? $map[ $name ] : $sizes;
	}

	/**
	 * Content images: lazy-load except a possible LCP when there is no featured image.
	 *
	 * @param string $filtered_image HTML.
	 * @param string $context        Context.
	 * @param int    $attachment_id  ID.
	 * @return string
	 */
	public static function content_img_tag( $filtered_image, $context, $attachment_id ) {
		unset( $context );
		if ( is_admin() || ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) ) {
			return $filtered_image;
		}

		self::$content_img_index++;
		$is_lcp = ( 1 === self::$content_img_index )
			&& is_singular( array( 'post', 'inos_live_blog', 'page' ) )
			&& ! has_post_thumbnail()
			&& inos_get_option( 'skip_lcp_lazy', 1 );

		if ( false === strpos( $filtered_image, ' decoding=' ) ) {
			$filtered_image = str_replace( '<img ', '<img decoding="async" ', $filtered_image );
		}

		if ( $is_lcp ) {
			$filtered_image = preg_replace( '/\sloading=["\'][^"\']*["\']/', '', $filtered_image );
			$filtered_image = preg_replace( '/\sfetchpriority=["\'][^"\']*["\']/', '', $filtered_image );
			$filtered_image = str_replace( '<img ', '<img loading="eager" fetchpriority="high" ', $filtered_image );
		} elseif ( inos_get_option( 'enable_lazy_load', 1 ) && false === strpos( $filtered_image, ' loading=' ) ) {
			$filtered_image = str_replace( '<img ', '<img loading="lazy" ', $filtered_image );
		}

		if ( $attachment_id && false !== strpos( $filtered_image, 'alt=""' ) ) {
			$alt            = self::alt_for_attachment( (int) $attachment_id );
			$filtered_image = str_replace( 'alt=""', 'alt="' . esc_attr( $alt ) . '"', $filtered_image );
		}

		return $filtered_image;
	}

	/**
	 * Cooperate with WP 6.3+ LCP heuristics for the featured image.
	 *
	 * @param array<string, mixed> $attrs    Attrs.
	 * @param string               $tag_name Tag.
	 * @param array<string, mixed> $attr     Element attrs.
	 * @param string               $context  Context.
	 * @return array<string, mixed>
	 */
	public static function loading_optimization( $attrs, $tag_name, $attr, $context ) {
		unset( $attr );
		if ( 'img' !== $tag_name || is_admin() ) {
			return $attrs;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return $attrs;
		}

		$featured_contexts = array( 'the_post_thumbnail', 'wp_get_attachment_image', 'get_the_post_thumbnail' );
		$is_featured       = in_array( $context, $featured_contexts, true ) && is_singular( array( 'post', 'inos_live_blog', 'page' ) );

		if ( $is_featured && inos_get_option( 'skip_lcp_lazy', 1 ) ) {
			$attrs['loading']       = 'eager';
			$attrs['fetchpriority'] = 'high';
			$attrs['decoding']      = 'async';
		}

		return $attrs;
	}

	/**
	 * Preload the LCP featured image (Discover / Core Web Vitals).
	 */
	public static function preload_lcp() {
		if ( is_admin() || ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) ) {
			return;
		}
		if ( ! inos_get_option( 'preload_lcp_image', 1 ) || ! inos_get_option( 'skip_lcp_lazy', 1 ) ) {
			return;
		}
		if ( ! is_singular( array( 'post', 'inos_live_blog', 'page' ) ) && ! is_front_page() ) {
			return;
		}

		$post_id = is_front_page() ? 0 : get_the_ID();
		$size    = is_singular() ? 'inos-discover' : 'inos-discover';
		$thumb   = 0;

		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$thumb = (int) get_post_thumbnail_id( $post_id );
		} elseif ( is_front_page() && function_exists( 'inos_get_lead_post' ) ) {
			$lead = inos_get_lead_post();
			if ( $lead && has_post_thumbnail( $lead ) ) {
				$thumb = (int) get_post_thumbnail_id( $lead );
			}
		}

		if ( ! $thumb ) {
			return;
		}

		$src = wp_get_attachment_image_src( $thumb, $size );
		if ( ! $src || empty( $src[0] ) ) {
			return;
		}

		$srcset = wp_get_attachment_image_srcset( $thumb, $size );
		$sizes  = wp_get_attachment_image_sizes( $thumb, $size );
		$html   = '<link rel="preload" as="image" href="' . esc_url( $src[0] ) . '" fetchpriority="high"';
		if ( $srcset ) {
			$html .= ' imagesrcset="' . esc_attr( $srcset ) . '"';
		}
		if ( $sizes ) {
			$html .= ' imagesizes="' . esc_attr( $sizes ) . '"';
		}
		$html .= ' />' . "\n";
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Fill empty alt text from the parent headline or a cleaned filename.
	 *
	 * @param int $attachment_id Attachment.
	 */
	public static function auto_alt_on_upload( $attachment_id ) {
		if ( ! inos_get_option( 'auto_image_alt', 1 ) ) {
			return;
		}
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}
		$existing = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $existing ) {
			return;
		}
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', self::alt_for_attachment( (int) $attachment_id ) );
	}

	/**
	 * Use a readable attachment title from the filename (helps Image Search).
	 *
	 * @param array<string, mixed> $data        Sanitized data.
	 * @param array<string, mixed> $post        Raw.
	 * @param array<string, mixed> $unsanitized Unsanitized.
	 * @param bool                 $update      Whether this is an update.
	 * @return array<string, mixed>
	 */
	public static function descriptive_title( $data, $post, $unsanitized = array(), $update = false ) {
		unset( $post, $unsanitized );
		if ( $update ) {
			return $data;
		}
		if ( empty( $data['post_title'] ) || false === strpos( (string) $data['post_title'], '-' ) ) {
			return $data;
		}
		if ( ! empty( $data['post_mime_type'] ) && 0 !== strpos( (string) $data['post_mime_type'], 'image/' ) ) {
			return $data;
		}
		$title = str_replace( array( '-', '_' ), ' ', (string) $data['post_title'] );
		$title = preg_replace( '/\.(jpe?g|png|gif|webp|avif)$/i', '', $title );
		$title = trim( preg_replace( '/\s+/', ' ', (string) $title ) );
		if ( $title ) {
			$data['post_title'] = ucwords( $title );
		}
		return $data;
	}

	/**
	 * Alt text: attachment alt, caption, parent title, then filename.
	 *
	 * @param int $attachment_id ID.
	 * @return string
	 */
	public static function alt_for_attachment( $attachment_id ) {
		$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $alt ) {
			return wp_strip_all_tags( $alt );
		}
		$att = get_post( $attachment_id );
		if ( $att && $att->post_excerpt ) {
			return wp_strip_all_tags( $att->post_excerpt );
		}
		if ( $att && $att->post_parent ) {
			$parent = get_the_title( $att->post_parent );
			if ( $parent ) {
				return wp_strip_all_tags( $parent );
			}
		}
		if ( $att && $att->post_title ) {
			return wp_strip_all_tags( $att->post_title );
		}
		$file = get_attached_file( $attachment_id );
		if ( $file ) {
			$base = preg_replace( '/\.[^.]+$/', '', wp_basename( $file ) );
			$base = str_replace( array( '-', '_' ), ' ', (string) $base );
			return ucwords( trim( (string) $base ) );
		}
		return '';
	}

	/**
	 * Attributes for a theme thumbnail.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $size    Size.
	 * @param bool   $lcp     LCP candidate.
	 * @return array<string, string>
	 */
	public static function theme_attrs( $post_id, $size = 'inos-card', $lcp = false ) {
		unset( $size );
		$alt  = '';
		$tid  = get_post_thumbnail_id( $post_id );
		if ( $tid ) {
			$alt = self::alt_for_attachment( (int) $tid );
		}
		if ( ! $alt ) {
			$alt = wp_strip_all_tags( get_the_title( $post_id ) );
		}

		$attr = array(
			'class'    => 'inos-card__img',
			'alt'      => $alt,
			'decoding' => 'async',
		);

		if ( $lcp && inos_get_option( 'skip_lcp_lazy', 1 ) ) {
			$attr['loading']         = 'eager';
			$attr['fetchpriority']   = 'high';
			$attr['data-inos-lcp']   = '1';
		} elseif ( inos_get_option( 'enable_lazy_load', 1 ) ) {
			$attr['loading']       = 'lazy';
			$attr['fetchpriority'] = 'auto';
		}

		return $attr;
	}

	/**
	 * Featured figure HTML for articles.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function featured_figure_html( $post_id = 0 ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		if ( ! $post_id || ! has_post_thumbnail( $post_id ) ) {
			return '';
		}

		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		self::maybe_subsizes( $thumb_id );
		$img      = get_the_post_thumbnail(
			$post_id,
			'inos-discover',
			array_merge(
				self::theme_attrs( $post_id, 'inos-discover', true ),
				array( 'class' => 'inos-article__img' )
			)
		);

		$caption = wp_get_attachment_caption( $thumb_id );
		$credit  = (string) get_post_meta( $post_id, '_inos_image_credit', true );
		$bits    = array_filter( array( $caption, $credit ) );

		$html  = '<figure class="inos-article__figure" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">';
		$html .= $img;
		$url   = wp_get_attachment_image_url( $thumb_id, 'inos-discover' );
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( $thumb_id, 'full' );
		}
		if ( $url ) {
			$html .= '<meta itemprop="url" content="' . esc_url( $url ) . '" />';
			$html .= '<meta itemprop="contentUrl" content="' . esc_url( $url ) . '" />';
		}
		$rights = self::license_fields( $post_id, $thumb_id );
		if ( ! empty( $rights['license'] ) ) {
			$html .= '<link itemprop="license" href="' . esc_url( $rights['license'] ) . '" />';
		}
		if ( ! empty( $rights['acquireLicensePage'] ) ) {
			$html .= '<link itemprop="acquireLicensePage" href="' . esc_url( $rights['acquireLicensePage'] ) . '" />';
		}
		if ( ! empty( $rights['copyrightNotice'] ) ) {
			$html .= '<meta itemprop="copyrightNotice" content="' . esc_attr( $rights['copyrightNotice'] ) . '" />';
		}
		if ( ! empty( $rights['creditText'] ) ) {
			$html .= '<meta itemprop="creditText" content="' . esc_attr( (string) $rights['creditText'] ) . '" />';
		}
		if ( ! empty( $rights['creator']['name'] ) ) {
			$creator_type = isset( $rights['creator']['@type'] ) ? (string) $rights['creator']['@type'] : 'Person';
			if ( 'Person' !== $creator_type ) {
				$creator_type = 'Organization';
			}
			$html .= '<span itemprop="creator" itemscope itemtype="https://schema.org/' . esc_attr( $creator_type ) . '">';
			$html .= '<meta itemprop="name" content="' . esc_attr( $rights['creator']['name'] ) . '" />';
			if ( ! empty( $rights['creator']['url'] ) ) {
				$html .= '<link itemprop="url" href="' . esc_url( $rights['creator']['url'] ) . '" />';
			}
			$html .= '</span>';
		}
		if ( $bits ) {
			$html .= '<figcaption itemprop="caption">' . esc_html( implode( ' — ', $bits ) ) . '</figcaption>';
		}
		$html .= '</figure>';

		return $html;
	}

	/**
	 * Warn if featured image is under 1200px wide.
	 *
	 * @param string $content  HTML.
	 * @param int    $post_id  Post ID.
	 * @param int    $thumb_id Attachment ID.
	 * @return string
	 */
	public static function thumbnail_notice( $content, $post_id, $thumb_id ) {
		unset( $post_id );
		if ( ! $thumb_id ) {
			return $content . '<p class="inos-image-qa inos-image-qa--warn">' . esc_html__( 'Google Discover and Top Stories need a featured image at least 1200px wide (16:9, 4:3, or 1:1).', 'infy-news-os-core' ) . '</p>';
		}

		$meta = wp_get_attachment_metadata( $thumb_id );
		$w    = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
		$mime = get_post_mime_type( $thumb_id );
		if ( $w > 0 && $w < self::DISCOVER_MIN_WIDTH ) {
			$content .= '<p class="inos-image-qa inos-image-qa--warn">' . sprintf(
				/* translators: 1: actual width, 2: required width */
				esc_html__( 'This image is %1$dpx wide. Use at least %2$dpx for Google Discover, Top Stories, and Image Search.', 'infy-news-os-core' ),
				$w,
				self::DISCOVER_MIN_WIDTH
			) . '</p>';
		}
		if ( $mime && false !== strpos( $mime, 'gif' ) ) {
			$content .= '<p class="inos-image-qa inos-image-qa--warn">' . esc_html__( 'Animated GIFs are a poor Discover / News lead image. Use a JPEG, PNG, or WebP still.', 'infy-news-os-core' ) . '</p>';
		}
		$alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		if ( ! $alt ) {
			$content .= '<p class="inos-image-qa inos-image-qa--warn">' . esc_html__( 'Add alt text on this image for Google Search and Image Search.', 'infy-news-os-core' ) . '</p>';
		}

		return $content;
	}

	/**
	 * Block editor featured-image width check.
	 *
	 * @param string $hook Hook.
	 */
	public static function enqueue_editor( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, array( 'post', 'inos_live_blog' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'inos-editor-image-qa',
			INOS_CORE_URL . 'admin/js/inos-admin.js',
			array( 'jquery', 'wp-data', 'wp-editor' ),
			INOS_CORE_VERSION,
			true
		);

		wp_localize_script(
			'inos-editor-image-qa',
			'inosAdmin',
			array(
				'minWidth' => self::DISCOVER_MIN_WIDTH,
				'warnText' => sprintf(
					/* translators: %d: minimum pixel width */
					__( 'Featured image should be at least %dpx wide for Google Discover.', 'infy-news-os-core' ),
					self::DISCOVER_MIN_WIDTH
				),
				'ajax'     => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'inos_search_posts' ),
			)
		);

		wp_enqueue_style(
			'inos-admin',
			INOS_CORE_URL . 'admin/css/inos-admin.css',
			array(),
			INOS_CORE_VERSION
		);
	}

	/**
	 * Best social/Discover image URL for a post (never a logo for articles).
	 *
	 * @param int  $post_id    Post ID.
	 * @param bool $allow_logo Fallback to publisher logo.
	 * @return string
	 */
	public static function og_image_url( $post_id, $allow_logo = true ) {
		if ( has_post_thumbnail( $post_id ) ) {
			foreach ( array( 'inos-og', 'inos-discover', 'full' ) as $size ) {
				$url = get_the_post_thumbnail_url( $post_id, $size );
				if ( $url ) {
					return $url;
				}
			}
		}
		return $allow_logo ? (string) inos_publisher_logo_url() : '';
	}

	/**
	 * Width/height/mime for an OG image.
	 *
	 * @param int $post_id Post ID.
	 * @return array{url:string,width:int,height:int,type:string,alt:string}|null
	 */
	public static function og_image_meta( $post_id ) {
		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumb_id ) {
			return null;
		}
		self::maybe_subsizes( (int) $thumb_id );
		$size = wp_get_attachment_image_src( $thumb_id, 'inos-og' );
		if ( ! $size ) {
			$size = wp_get_attachment_image_src( $thumb_id, 'inos-discover' );
		}
		if ( ! $size ) {
			$size = wp_get_attachment_image_src( $thumb_id, 'full' );
		}
		if ( ! $size ) {
			return null;
		}
		$mime = get_post_mime_type( $thumb_id );
		return array(
			'url'    => $size[0],
			'width'  => (int) $size[1],
			'height' => (int) $size[2],
			'type'   => $mime ? $mime : 'image/jpeg',
			'alt'    => self::alt_for_attachment( (int) $thumb_id ),
		);
	}

	/**
	 * ImageObject data (single or 16:9 / 4:3 / 1:1 set for Discover).
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>|array<int, array<string, mixed>>|null
	 */
	public static function image_object( $post_id ) {
		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumb_id ) {
			return null;
		}

		self::maybe_subsizes( (int) $thumb_id );

		if ( inos_get_option( 'schema_multi_aspect', 1 ) ) {
			$set = array();
			foreach ( array( 'inos-discover', 'inos-photo-4x3', 'inos-photo-1x1', 'full' ) as $size ) {
				$obj = self::image_object_for_size( (int) $thumb_id, $size, $post_id );
				if ( $obj ) {
					$set[] = $obj;
				}
			}
			return $set ? $set : null;
		}

		return self::image_object_for_size( (int) $thumb_id, 'full', $post_id );
	}

	/**
	 * One ImageObject for a size.
	 *
	 * @param int    $thumb_id Attachment.
	 * @param string $size     Size.
	 * @param int    $post_id  Parent post.
	 * @return array<string, mixed>|null
	 */
	public static function image_object_for_size( $thumb_id, $size, $post_id = 0 ) {
		$src = wp_get_attachment_image_src( $thumb_id, $size );
		if ( ! $src ) {
			return null;
		}

		$alt     = self::alt_for_attachment( $thumb_id );
		$caption = wp_get_attachment_caption( $thumb_id );
		$credit  = $post_id ? (string) get_post_meta( $post_id, '_inos_image_credit', true ) : '';
		$rights  = self::license_fields( $post_id, $thumb_id );

		$obj = array(
			'@type'      => 'ImageObject',
			'url'        => $src[0],
			'contentUrl' => $src[0],
			'width'      => (int) $src[1],
			'height'     => (int) $src[2],
		);
		if ( $alt ) {
			$obj['name']        = $alt;
			$obj['description'] = $alt;
		}
		if ( $caption ) {
			$obj['caption'] = wp_strip_all_tags( $caption );
		} elseif ( $credit ) {
			$obj['caption'] = $credit;
		}
		foreach ( $rights as $key => $value ) {
			if ( '' !== $value && null !== $value ) {
				$obj[ $key ] = $value;
			}
		}
		$thumb = wp_get_attachment_image_url( $thumb_id, 'inos-thumb' );
		if ( $thumb ) {
			$obj['thumbnailUrl'] = $thumb;
		}
		if ( 'full' === $size || 'inos-discover' === $size ) {
			$obj['representativeOfPage'] = true;
		}
		return $obj;
	}

	/**
	 * Image Search license metadata (Google Image rich results).
	 *
	 * @param int $post_id  Parent post.
	 * @param int $thumb_id Attachment.
	 * @return array<string, mixed>
	 */
	public static function license_fields( $post_id, $thumb_id = 0 ) {
		$post_id  = absint( $post_id );
		$thumb_id = absint( $thumb_id );
		$credit   = $post_id ? trim( (string) get_post_meta( $post_id, '_inos_image_credit', true ) ) : '';
		$org      = function_exists( 'inos_get_option' ) ? (string) inos_get_option( 'org_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );
		$year     = $post_id ? get_post_time( 'Y', true, $post_id ) : gmdate( 'Y' );
		if ( ! $year ) {
			$year = gmdate( 'Y' );
		}

		$license = function_exists( 'inos_get_option' ) ? esc_url_raw( (string) inos_get_option( 'image_license_url', '' ) ) : '';
		if ( ! $license && function_exists( 'inos_policy_page_url' ) ) {
			$license = (string) inos_policy_page_url( 'editorial-policy' );
		}
		if ( ! $license && function_exists( 'inos_get_option' ) ) {
			$license = esc_url_raw( (string) inos_get_option( 'contact_page_url', '' ) );
		}
		if ( ! $license && function_exists( 'inos_policy_page_url' ) ) {
			$license = (string) inos_policy_page_url( 'contact' );
		}
		if ( ! $license ) {
			$license = home_url( '/' );
		}

		$acquire = function_exists( 'inos_get_option' ) ? esc_url_raw( (string) inos_get_option( 'image_acquire_license_url', '' ) ) : '';
		if ( ! $acquire && function_exists( 'inos_get_option' ) ) {
			$acquire = esc_url_raw( (string) inos_get_option( 'contact_page_url', '' ) );
		}
		if ( ! $acquire && function_exists( 'inos_policy_page_url' ) ) {
			$acquire = (string) inos_policy_page_url( 'contact' );
		}
		if ( ! $acquire ) {
			$acquire = $license;
		}

		$notice = function_exists( 'inos_get_option' ) ? trim( (string) inos_get_option( 'image_copyright_notice', '' ) ) : '';
		if ( ! $notice ) {
			$notice = sprintf(
				/* translators: 1: year, 2: publisher name */
				__( '© %1$s %2$s. All rights reserved.', 'infy-news-os-core' ),
				$year,
				$org
			);
			if ( $credit ) {
				$notice .= ' ' . $credit;
			}
		}

		$creator = null;
		if ( $credit ) {
			$creator = array(
				'@type' => 'Person',
				'name'  => $credit,
			);
		} elseif ( $post_id ) {
			$author_id = (int) get_post_field( 'post_author', $post_id );
			$name      = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
			if ( $name ) {
				$creator = array(
					'@type' => 'Person',
					'name'  => $name,
					'url'   => get_author_posts_url( $author_id ),
				);
			}
		}
		if ( ! $creator && $org ) {
			$creator = array(
				'@type' => 'Organization',
				'name'  => $org,
				'url'   => home_url( '/' ),
			);
		}

		$credit_text = $credit ? $credit : $org;

		$out = array(
			'license'            => $license,
			'acquireLicensePage' => $acquire,
			'copyrightNotice'    => $notice,
			'creditText'         => $credit_text,
			'copyrightHolder'    => array(
				'@type' => 'Organization',
				'name'  => $org,
				'url'   => home_url( '/' ),
			),
		);
		if ( $creator ) {
			$out['creator'] = $creator;
		}

		unset( $thumb_id );
		return $out;
	}

	/**
	 * Images attached to or embedded in a post (Image Search sitemap).
	 *
	 * @param int $post_id Post ID.
	 * @return array<int, array{id:int,url:string,title:string,caption:string}>
	 */
	public static function post_images( $post_id ) {
		$post_id = absint( $post_id );
		$seen    = array();
		$out     = array();

		$thumb = (int) get_post_thumbnail_id( $post_id );
		if ( $thumb ) {
			$seen[ $thumb ] = true;
			$item           = self::sitemap_image_item( $thumb, $post_id );
			if ( $item ) {
				$out[] = $item;
			}
		}

		$post = get_post( $post_id );
		if ( $post && $post->post_content ) {
			if ( preg_match_all( '/wp-image-(\d+)/', $post->post_content, $m ) ) {
				foreach ( $m[1] as $id ) {
					$id = absint( $id );
					if ( ! $id || isset( $seen[ $id ] ) ) {
						continue;
					}
					$seen[ $id ] = true;
					$item        = self::sitemap_image_item( $id, $post_id );
					if ( $item ) {
						$out[] = $item;
					}
				}
			}
		}

		return $out;
	}

	/**
	 * One sitemap image row.
	 *
	 * @param int $attachment_id Attachment.
	 * @param int $post_id       Parent.
	 * @return array{id:int,url:string,title:string,caption:string}|null
	 */
	private static function sitemap_image_item( $attachment_id, $post_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! $url ) {
			return null;
		}
		$title   = self::alt_for_attachment( $attachment_id );
		$caption = (string) wp_get_attachment_caption( $attachment_id );
		$credit  = (string) get_post_meta( $post_id, '_inos_image_credit', true );
		if ( ! $caption && $credit ) {
			$caption = $credit;
		}
		if ( ! $title ) {
			$title = wp_strip_all_tags( get_the_title( $post_id ) );
		}
		return array(
			'id'      => $attachment_id,
			'url'     => $url,
			'title'   => $title,
			'caption' => $caption,
		);
	}

	/**
	 * Lead image only (Google News / Discover) — no extra crops.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function sitemap_lead_image_xml( $post_id ) {
		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		if ( ! $thumb_id ) {
			return '';
		}
		$url = wp_get_attachment_image_url( $thumb_id, 'inos-discover' );
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( $thumb_id, 'full' );
		}
		if ( ! $url ) {
			return '';
		}
		$title   = self::alt_for_attachment( $thumb_id );
		$caption = (string) wp_get_attachment_caption( $thumb_id );
		$credit  = (string) get_post_meta( $post_id, '_inos_image_credit', true );
		if ( ! $caption && $credit ) {
			$caption = $credit;
		}
		$xml  = '<image:image>';
		$xml .= '<image:loc>' . htmlspecialchars( esc_url_raw( $url ), ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:loc>';
		if ( $title ) {
			$xml .= '<image:title>' . htmlspecialchars( $title, ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:title>';
		}
		if ( $caption ) {
			$xml .= '<image:caption>' . htmlspecialchars( $caption, ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:caption>';
		}
		$xml .= '</image:image>';
		return $xml;
	}

	/**
	 * XML for one or more image:image nodes.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function sitemap_image_xml( $post_id ) {
		$xml = '';
		foreach ( self::post_images( $post_id ) as $img ) {
			$xml .= '<image:image>';
			$xml .= '<image:loc>' . htmlspecialchars( esc_url_raw( $img['url'] ), ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:loc>';
			if ( $img['title'] ) {
				$xml .= '<image:title>' . htmlspecialchars( $img['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:title>';
			}
			if ( $img['caption'] ) {
				$xml .= '<image:caption>' . htmlspecialchars( $img['caption'], ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</image:caption>';
			}
			$xml .= '</image:image>';
		}
		return $xml;
	}

	/**
	 * Create newly registered crops (16:9 / 4:3 / 1:1) for older uploads.
	 *
	 * @param int $attachment_id Attachment.
	 */
	private static function maybe_subsizes( $attachment_id ) {
		if ( ! $attachment_id || ! function_exists( 'wp_update_image_subsizes' ) || ! function_exists( 'wp_get_missing_image_subsizes' ) ) {
			return;
		}
		$missing = wp_get_missing_image_subsizes( $attachment_id );
		if ( empty( $missing ) ) {
			return;
		}
		wp_update_image_subsizes( $attachment_id );
	}
}
