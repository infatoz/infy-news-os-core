<?php
/**
 * Article details sidebar builder.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ordered article sidebar modules stored in option `inos_article_sidebar`.
 */
class INOS_Article_Sidebar {

	const OPTION = 'inos_article_sidebar';

	const WIDGET_ID = 'inos-article-sidebar';

	/**
	 * Block types that fit a news article rail.
	 *
	 * @return array<string, array{label:string, fields:string[]}>
	 */
	public static function types() {
		return array(
			'ad'         => array(
				'label'  => __( 'Advertisement', 'infy-news-os-core' ),
				'fields' => array( 'ad_slot' ),
			),
			'trending'   => array(
				'label'  => __( 'Trending', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'latest'     => array(
				'label'  => __( 'Latest stories', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'related'    => array(
				'label'  => __( 'More in this section', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count', 'show_thumb', 'show_meta' ),
			),
			'also_read'  => array(
				'label'  => __( 'Also read (editor picks)', 'infy-news-os-core' ),
				'fields' => array( 'title', 'show_thumb', 'show_meta' ),
			),
			'posts'      => array(
				'label'  => __( 'Posts / category', 'infy-news-os-core' ),
				'fields' => array( 'title', 'category', 'tag', 'count', 'layout', 'orderby', 'show_more', 'more_text', 'show_meta', 'show_thumb' ),
			),
			'live'       => array(
				'label'  => __( 'Live coverage', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count', 'show_more' ),
			),
			'topics'     => array(
				'label'  => __( 'Topics / sections', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'authors'    => array(
				'label'  => __( 'Authors', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'newsletter' => array(
				'label'  => __( 'Newsletter', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'widgets'    => array(
				'label'  => __( 'WordPress widgets', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'html'       => array(
				'label'  => __( 'Custom HTML', 'infy-news-os-core' ),
				'fields' => array( 'title', 'html' ),
			),
		);
	}

	/**
	 * Compact layouts for the article rail.
	 *
	 * @return array<string, string>
	 */
	public static function layouts() {
		return array(
			'compact' => __( 'Compact headlines', 'infy-news-os-core' ),
			'list'    => __( 'Text list', 'infy-news-os-core' ),
		);
	}

	/**
	 * Empty module.
	 *
	 * @param string               $type Type.
	 * @param array<string, mixed> $over Overrides.
	 * @return array<string, mixed>
	 */
	public static function blank( $type, $over = array() ) {
		$base = class_exists( 'INOS_Home_Builder' )
			? INOS_Home_Builder::blank( in_array( $type, array( 'hero', 'web_stories', 'split', 'slider', 'tabs', 'intro' ), true ) ? 'posts' : $type, array() )
			: array(
				'id'           => 'mod_' . wp_generate_password( 8, false, false ),
				'type'         => $type,
				'enabled'      => 1,
				'title'        => '',
				'subtitle'     => '',
				'category'     => 0,
				'tag'          => 0,
				'count'        => 4,
				'layout'       => 'compact',
				'orderby'      => 'date',
				'unique'       => 0,
				'show_more'    => 0,
				'more_text'    => '',
				'show_excerpt' => 0,
				'show_meta'    => 1,
				'show_thumb'   => 1,
				'dark'         => 0,
				'ad_slot'      => 'sidebar',
				'html'         => '',
				'tabs'         => '',
			);
		$base['type']    = $type;
		$base['layout']  = 'compact';
		$base['unique']  = 0;
		$base['ad_slot'] = 'sidebar';
		if ( 'trending' === $type ) {
			$base['count'] = 6;
		} elseif ( 'latest' === $type || 'related' === $type ) {
			$base['count'] = 5;
		} elseif ( 'live' === $type || 'authors' === $type || 'topics' === $type ) {
			$base['count'] = 5;
		} elseif ( 'posts' === $type ) {
			$base['count']     = 5;
			$base['show_more'] = 1;
		}
		return wp_parse_args( $over, $base );
	}

	/**
	 * Default article rail (matches the previous hardcoded sidebar).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function defaults() {
		return array(
			self::blank(
				'ad',
				array(
					'ad_slot' => 'sidebar',
				)
			),
			self::blank(
				'trending',
				array(
					'title' => (string) inos_get_option( 'trending_title', __( 'Trending', 'infy-news-os-core' ) ),
					'count' => absint( inos_get_option( 'trending_count', 6 ) ),
				)
			),
			self::blank( 'widgets' ),
		);
	}

	/**
	 * Stored or default modules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		$stored = get_option( self::OPTION, null );
		if ( null === $stored || false === $stored ) {
			return self::defaults();
		}
		if ( ! is_array( $stored ) ) {
			return self::defaults();
		}
		return array_values( array_map( array( __CLASS__, 'sanitize_module' ), $stored ) );
	}

	/**
	 * Enabled modules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function enabled() {
		$out = array();
		foreach ( self::all() as $mod ) {
			if ( ! empty( $mod['enabled'] ) ) {
				$out[] = $mod;
			}
		}
		return $out;
	}

	/**
	 * Persist modules.
	 *
	 * @param mixed $raw JSON string or array.
	 */
	public static function save( $raw ) {
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $raw ) ) {
			return;
		}
		$clean = array();
		foreach ( $raw as $mod ) {
			if ( is_array( $mod ) ) {
				$clean[] = self::sanitize_module( $mod );
			}
		}
		update_option( self::OPTION, $clean, false );
	}

	/**
	 * Sanitize one module.
	 *
	 * @param array<string, mixed> $mod Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_module( $mod ) {
		$types = array_keys( self::types() );
		$type  = isset( $mod['type'] ) ? sanitize_key( $mod['type'] ) : 'trending';
		if ( ! in_array( $type, $types, true ) ) {
			$type = 'trending';
		}
		$out = self::blank( $type );
		if ( ! empty( $mod['id'] ) ) {
			$out['id'] = sanitize_key( $mod['id'] );
		}
		$out['enabled']      = empty( $mod['enabled'] ) ? 0 : 1;
		$out['title']        = isset( $mod['title'] ) ? sanitize_text_field( $mod['title'] ) : '';
		$out['category']     = isset( $mod['category'] ) ? absint( $mod['category'] ) : 0;
		$out['tag']          = isset( $mod['tag'] ) ? absint( $mod['tag'] ) : 0;
		$out['count']        = isset( $mod['count'] ) ? max( 1, min( 16, absint( $mod['count'] ) ) ) : $out['count'];
		$layout              = isset( $mod['layout'] ) ? sanitize_key( $mod['layout'] ) : 'compact';
		$out['layout']       = isset( self::layouts()[ $layout ] ) ? $layout : 'compact';
		$order               = isset( $mod['orderby'] ) ? sanitize_key( $mod['orderby'] ) : 'date';
		$out['orderby']      = in_array( $order, array( 'date', 'modified', 'comment_count', 'views', 'rand' ), true ) ? $order : 'date';
		$out['show_more']    = empty( $mod['show_more'] ) ? 0 : 1;
		$out['more_text']    = isset( $mod['more_text'] ) ? sanitize_text_field( $mod['more_text'] ) : '';
		$out['show_meta']    = empty( $mod['show_meta'] ) ? 0 : 1;
		$out['show_thumb']   = empty( $mod['show_thumb'] ) ? 0 : 1;
		$slot                = isset( $mod['ad_slot'] ) ? sanitize_key( $mod['ad_slot'] ) : 'sidebar';
		$out['ad_slot']      = in_array( $slot, array( 'header', 'below_ticker', 'between_cards', 'sidebar', 'footer' ), true ) ? $slot : 'sidebar';
		$out['html']         = isset( $mod['html'] ) ? wp_kses_post( $mod['html'] ) : '';
		return $out;
	}

	/**
	 * Widget area to print (dedicated article area, else the shared sidebar).
	 *
	 * @return string
	 */
	public static function widget_id() {
		if ( is_active_sidebar( self::WIDGET_ID ) ) {
			return self::WIDGET_ID;
		}
		if ( is_active_sidebar( 'sidebar-1' ) ) {
			return 'sidebar-1';
		}
		return self::WIDGET_ID;
	}

	/**
	 * Heading for a module.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return string
	 */
	public static function title( $mod ) {
		if ( ! empty( $mod['title'] ) ) {
			return (string) $mod['title'];
		}
		if ( ! empty( $mod['category'] ) ) {
			$term = get_term( (int) $mod['category'], 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term->name;
			}
		}
		return '';
	}

	/**
	 * Posts query for list blocks, excluding the current article.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return WP_Post[]
	 */
	public static function query( $mod ) {
		$count   = max( 1, absint( $mod['count'] ) );
		$exclude = array();
		if ( is_singular() ) {
			$exclude[] = get_queried_object_id();
		}
		$args = array(
			'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => array_filter( array_map( 'absint', $exclude ) ),
		);
		if ( ! empty( $mod['category'] ) ) {
			$args['cat'] = absint( $mod['category'] );
		}
		if ( ! empty( $mod['tag'] ) ) {
			$args['tag_id'] = absint( $mod['tag'] );
		}
		$orderby = isset( $mod['orderby'] ) ? $mod['orderby'] : 'date';
		if ( 'views' === $orderby ) {
			$args['meta_key'] = '_inos_views'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
		} elseif ( 'rand' === $orderby ) {
			$args['orderby'] = 'rand';
		} elseif ( 'comment_count' === $orderby ) {
			$args['orderby'] = 'comment_count';
		} elseif ( 'modified' === $orderby ) {
			$args['orderby'] = 'modified';
		} else {
			$args['orderby'] = 'date';
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}
}
