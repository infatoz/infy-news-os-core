<?php
/**
 * Related articles and view-based trending.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Recirculation helpers.
 */
class INOS_Trending {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'maybe_count' ) );
		add_filter( 'the_content', array( __CLASS__, 'inject_also_read' ), 18 );
		add_action( 'wp_ajax_inos_related_more', array( __CLASS__, 'ajax_related_more' ) );
		add_action( 'wp_ajax_nopriv_inos_related_more', array( __CLASS__, 'ajax_related_more' ) );
	}

	/**
	 * Related posts.
	 *
	 * @param int   $post_id Post ID.
	 * @param int   $count   Count.
	 * @param int[] $exclude Extra IDs to skip (already shown related cards).
	 * @return WP_Post[]
	 */
	public static function related( $post_id = 0, $count = 0, $exclude = array() ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		$count   = $count ? absint( $count ) : absint( inos_get_option( 'related_count', 6 ) );
		if ( $count > 24 ) {
			$count = 24;
		}
		if ( ! $post_id || $count < 1 ) {
			return array();
		}

		$exclude = array_values( array_unique( array_filter( array_map( 'absint', (array) $exclude ) ) ) );
		$not_in  = array_merge( array( $post_id ), self::also_read_ids( $post_id ), $exclude );

		$section = inos_get_primary_section( $post_id );
		$tags    = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );

		$args = array(
			'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'post__not_in'        => $not_in,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);

		$tax_query = array( 'relation' => 'OR' );
		if ( $section ) {
			$tax_query[] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array( (int) $section->term_id ),
			);
		}
		if ( $tags ) {
			$tax_query[] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', $tags ),
			);
		}
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		$q = new WP_Query( $args );
		if ( count( $q->posts ) >= $count ) {
			return $q->posts;
		}

		$fallback = new WP_Query(
			array(
				'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'post__not_in'        => array_merge( $not_in, wp_list_pluck( $q->posts, 'ID' ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'cat'                 => $section ? (int) $section->term_id : 0,
			)
		);

		return array_slice( array_merge( $q->posts, $fallback->posts ), 0, $count );
	}

	/**
	 * JSON HTML for the next related stories batch on an article.
	 */
	public static function ajax_related_more() {
		check_ajax_referer( 'inos_related_more', 'nonce' );

		if ( ! function_exists( 'inos_related_load_more_enabled' ) || ! inos_related_load_more_enabled() ) {
			wp_send_json_error( array( 'message' => 'disabled' ), 400 );
		}

		$post_id = absint( wp_unslash( $_POST['post'] ?? 0 ) );
		$post    = $post_id ? get_post( $post_id ) : null;
		$types   = function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : array( 'post' );
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status || ! in_array( $post->post_type, (array) $types, true ) ) {
			wp_send_json_error( array( 'message' => 'invalid' ), 400 );
		}

		$raw     = preg_split( '/[,\s]+/', (string) wp_unslash( $_POST['exclude'] ?? '' ) );
		$exclude = array_values(
			array_unique(
				array_filter(
					array_map( 'absint', is_array( $raw ) ? $raw : array() )
				)
			)
		);
		if ( count( $exclude ) > 40 ) {
			$exclude = array_slice( $exclude, 0, 40 );
		}

		$max   = min( 24, max( 1, absint( inos_get_option( 'related_count', 6 ) ) ) );
		$batch = min( 8, max( 1, absint( inos_get_option( 'related_more_count', 3 ) ) ) );
		$need  = min( $batch, max( 0, $max - count( $exclude ) ) );
		if ( $need < 1 ) {
			wp_send_json_success(
				array(
					'html'     => '',
					'has_more' => false,
					'ids'      => array(),
				)
			);
		}

		$probe  = self::related( $post_id, $need + 1, $exclude );
		$has_more = count( $probe ) > $need && ( count( $exclude ) + $need ) < $max;
		$posts    = array_slice( $probe, 0, $need );

		ob_start();
		if ( function_exists( 'inos_theme_related_cards' ) ) {
			inos_theme_related_cards( $posts, $post_id );
		} else {
			foreach ( $posts as $item ) {
				echo '<p><a href="' . esc_url( get_permalink( $item ) ) . '">' . esc_html( get_the_title( $item ) ) . '</a></p>';
			}
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'     => $html,
				'has_more' => $has_more,
				'ids'      => wp_list_pluck( $posts, 'ID' ),
			)
		);
	}

	/**
	 * Increment views on singular public visits.
	 */
	public static function maybe_count() {
		if ( is_admin() || ! is_singular( array( 'post', 'inos_live_blog' ) ) ) {
			return;
		}
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return;
		}

		$post_id = get_the_ID();
		$key     = 'inos_view_' . $post_id . '_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		if ( get_transient( $key ) ) {
			return;
		}
		set_transient( $key, 1, HOUR_IN_SECONDS );

		$views = absint( get_post_meta( $post_id, '_inos_views', true ) );
		update_post_meta( $post_id, '_inos_views', $views + 1 );
	}

	/**
	 * Editor-picked Also read IDs.
	 *
	 * @param int $post_id Post ID.
	 * @return int[]
	 */
	public static function also_read_ids( $post_id = 0 ) {
		$post_id = $post_id ? absint( $post_id ) : get_the_ID();
		if ( ! $post_id ) {
			return array();
		}
		$raw = get_post_meta( $post_id, '_inos_also_read_ids', true );
		if ( is_array( $raw ) ) {
			$ids = array_map( 'absint', $raw );
		} else {
			$ids = array_map( 'absint', preg_split( '/[\s,]+/', (string) $raw ) );
		}
		$ids = array_values( array_unique( array_filter( $ids ) ) );
		$max = max( 1, absint( inos_get_option( 'also_read_count', 4 ) ) );
		$ids = array_slice( $ids, 0, $max );
		return array_values( array_diff( $ids, array( $post_id ) ) );
	}

	/**
	 * Editor-picked Also read posts.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Post[]
	 */
	public static function also_read( $post_id = 0 ) {
		$ids = self::also_read_ids( $post_id );
		if ( ! $ids ) {
			return array();
		}
		$q = new WP_Query(
			array(
				'post_type'           => array( 'post', 'inos_live_blog' ),
				'post_status'         => 'publish',
				'post__in'            => $ids,
				'orderby'             => 'post__in',
				'posts_per_page'      => count( $ids ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		return $q->posts;
	}

	/**
	 * Trending posts.
	 *
	 * @param int $count Count.
	 * @return WP_Post[]
	 */
	public static function trending( $count = 0 ) {
		$count  = $count ? absint( $count ) : absint( inos_get_option( 'trending_count', 6 ) );
		$source = (string) inos_get_option( 'trending_source', 'views' );

		if ( 'editorial' === $source ) {
			$q = new WP_Query(
				array(
					'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => $count,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_inos_trending_pin', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'          => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			if ( $q->posts ) {
				return $q->posts;
			}
		}

		$q = new WP_Query(
			array(
				'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'meta_key'            => '_inos_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'             => 'meta_value_num',
				'order'               => 'DESC',
				'date_query'          => array(
					array(
						'after' => '7 days ago',
					),
				),
			)
		);

		if ( $q->posts ) {
			return $q->posts;
		}

		$latest = new WP_Query(
			array(
				'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		return $latest->posts;
	}

	/**
	 * Compact also-read links after a few paragraphs (Newspaper-style recirc).
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public static function inject_also_read( $content ) {
		if ( is_admin() || ! is_singular( array( 'post', 'inos_live_blog' ) ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return $content;
		}
		if ( ! inos_get_option( 'mid_article_also_read', 1 ) ) {
			return $content;
		}

		$posts = array_slice( self::also_read( get_the_ID() ), 0, 2 );
		if ( ! $posts ) {
			$posts = array_slice( self::related( get_the_ID(), 2 ), 0, 2 );
		}
		if ( ! $posts ) {
			return $content;
		}

		$heading = function_exists( 'inos_label' ) ? inos_label( 'also_read_inline' ) : __( 'Also read', 'infy-news-os-core' );
		$html    = '<aside class="inos-inline-read" aria-label="' . esc_attr( $heading ) . '">';
		$html   .= '<p class="inos-inline-read__label">' . esc_html( $heading ) . '</p><ul class="inos-inline-read__list">';
		foreach ( $posts as $item ) {
			$html .= '<li><a href="' . esc_url( get_permalink( $item ) ) . '">' . esc_html( get_the_title( $item ) ) . '</a></li>';
		}
		$html .= '</ul></aside>';

		$n     = max( 2, absint( inos_get_option( 'in_article_paragraph', 2 ) ) + 1 );
		$parts = preg_split( '/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		if ( ! $parts ) {
			return $content;
		}

		$p   = 0;
		$out = '';
		foreach ( $parts as $part ) {
			$out .= $part;
			if ( preg_match( '/<\/p>/i', $part ) ) {
				++$p;
				if ( $p === $n ) {
					$out .= $html;
				}
			}
		}

		return $out;
	}
}
