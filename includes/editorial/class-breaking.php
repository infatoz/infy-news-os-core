<?php
/**
 * Headline ticker query.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Breaking / latest ticker data.
 */
class INOS_Breaking {

	/**
	 * Hooks.
	 */
	public static function init() {
		// Data is read via helpers; no extra hooks required.
	}

	/**
	 * Stories for the masthead ticker.
	 *
	 * @param int $limit Limit.
	 * @return WP_Post[]
	 */
	public static function get_posts( $limit = 0 ) {
		$limit    = $limit ? max( 1, absint( $limit ) ) : max( 1, min( 20, absint( inos_get_option( 'ticker_count', 10 ) ) ) );
		$source   = self::source();
		$category = absint( inos_get_option( 'ticker_category', 0 ) );

		if ( 'breaking' === $source ) {
			return self::breaking_posts( $limit, $category );
		}

		if ( 'live_latest' === $source ) {
			$live = self::open_live( $limit, $category );
			if ( count( $live ) >= $limit ) {
				return $live;
			}
			$seen   = wp_list_pluck( $live, 'ID' );
			$latest = self::latest_posts( $limit, $category, $seen );
			return array_slice( array_merge( $live, $latest ), 0, $limit );
		}

		return self::latest_posts( $limit, $category );
	}

	/**
	 * Ticker source setting.
	 *
	 * @return string
	 */
	public static function source() {
		$source = (string) inos_get_option( 'ticker_source', 'latest' );
		return in_array( $source, array( 'latest', 'breaking', 'live_latest' ), true ) ? $source : 'latest';
	}

	/**
	 * Label for the ticker chip.
	 *
	 * @param WP_Post[] $posts Posts in the strip.
	 * @return string
	 */
	public static function label( $posts = array() ) {
		$custom = trim( (string) inos_get_option( 'ticker_label', '' ) );
		if ( $custom ) {
			return $custom;
		}

		$source = self::source();
		if ( 'latest' === $source ) {
			$title = trim( (string) inos_get_option( 'latest_title', '' ) );
			return $title ? $title : ( function_exists( 'inos_label' ) ? inos_label( 'latest' ) : __( 'Latest', 'infy-news-os-core' ) );
		}

		$live_only = ! empty( $posts );
		foreach ( $posts as $item ) {
			if ( ! class_exists( 'INOS_Liveblog' ) || ! INOS_Liveblog::is_live( $item->ID ) ) {
				$live_only = false;
				break;
			}
		}
		if ( $live_only || 'live_latest' === $source ) {
			return function_exists( 'inos_label' ) ? inos_label( 'live' ) : __( 'Live', 'infy-news-os-core' );
		}

		return function_exists( 'inos_label' ) ? inos_label( 'breaking' ) : __( 'Breaking', 'infy-news-os-core' );
	}

	/**
	 * Latest published stories.
	 *
	 * @param int   $limit    Limit.
	 * @param int   $category Category ID.
	 * @param int[] $exclude  IDs to skip.
	 * @return WP_Post[]
	 */
	private static function latest_posts( $limit, $category = 0, $exclude = array() ) {
		$args = array(
			'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
		);
		if ( $category ) {
			$args['cat'] = $category;
		}
		$exclude = array_filter( array_map( 'absint', (array) $exclude ) );
		if ( $exclude ) {
			$args['post__not_in'] = $exclude;
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Open live blogs.
	 *
	 * @param int $limit    Limit.
	 * @param int $category Category ID.
	 * @return WP_Post[]
	 */
	private static function open_live( $limit, $category = 0 ) {
		$out = array();
		if ( ! class_exists( 'INOS_Liveblog' ) || ! post_type_exists( 'inos_live_blog' ) ) {
			return $out;
		}

		$args = array(
			'post_type'           => 'inos_live_blog',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'modified',
			'order'               => 'DESC',
		);
		if ( $category ) {
			$args['cat'] = $category;
		}

		$query = new WP_Query( $args );
		foreach ( $query->posts as $post ) {
			if ( ! INOS_Liveblog::is_live( $post->ID ) ) {
				continue;
			}
			$out[] = $post;
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * Live blogs first, then breaking posts still in window.
	 *
	 * @param int $limit    Limit.
	 * @param int $category Category ID.
	 * @return WP_Post[]
	 */
	private static function breaking_posts( $limit, $category = 0 ) {
		$out  = self::open_live( $limit, $category );
		$seen = wp_list_pluck( $out, 'ID' );
		if ( count( $out ) >= $limit ) {
			return $out;
		}

		$args = array(
			'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 20,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => array_map( 'absint', $seen ),
			'meta_key'            => '_inos_breaking', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'          => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'orderby'             => 'date',
			'order'               => 'DESC',
		);
		if ( $category ) {
			$args['cat'] = $category;
		}

		$query = new WP_Query( $args );
		foreach ( $query->posts as $post ) {
			if ( function_exists( 'inos_is_breaking' ) && inos_is_breaking( $post->ID ) ) {
				$out[] = $post;
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}
}
