<?php
/**
 * Homepage module builder (Newspaper / Jannah-style blocks).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ordered homepage modules stored in option `inos_home_modules`.
 */
class INOS_Home_Builder {

	const OPTION = 'inos_home_modules';

	/**
	 * IDs already printed this request (unique articles).
	 *
	 * @var int[]
	 */
	private static $used_ids = array();

	/**
	 * Module type catalog.
	 *
	 * @return array<string, array{label:string, fields:string[]}>
	 */
	public static function types() {
		return array(
			'intro'        => array(
				'label'  => __( 'Intro kicker', 'infy-news-os-core' ),
				'fields' => array( 'title', 'subtitle' ),
			),
			'hero'         => array(
				'label'  => __( 'Hero / featured', 'infy-news-os-core' ),
				'fields' => array( 'layout', 'count', 'show_excerpt', 'unique' ),
			),
			'web_stories'  => array(
				'label'  => __( 'Web Stories', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count', 'layout' ),
			),
			'category'     => array(
				'label'  => __( 'Category row', 'infy-news-os-core' ),
				'fields' => array( 'title', 'category', 'count', 'layout', 'orderby', 'unique', 'show_more', 'more_text', 'show_excerpt', 'show_meta', 'show_thumb', 'dark' ),
			),
			'posts'        => array(
				'label'  => __( 'Posts block', 'infy-news-os-core' ),
				'fields' => array( 'title', 'category', 'tag', 'count', 'layout', 'orderby', 'unique', 'show_more', 'more_text', 'show_excerpt', 'show_meta', 'show_thumb', 'dark' ),
			),
			'slider'       => array(
				'label'  => __( 'Featured slider', 'infy-news-os-core' ),
				'fields' => array( 'title', 'category', 'count', 'orderby', 'unique', 'show_excerpt' ),
			),
			'tabs'         => array(
				'label'  => __( 'Category tabs', 'infy-news-os-core' ),
				'fields' => array( 'title', 'tabs', 'count', 'layout', 'unique', 'show_thumb', 'show_meta' ),
			),
			'latest'       => array(
				'label'  => __( 'Latest list', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count', 'unique' ),
			),
			'trending'     => array(
				'label'  => __( 'Trending list', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'split'        => array(
				'label'  => __( 'Latest + trending columns', 'infy-news-os-core' ),
				'fields' => array( 'layout', 'count' ),
			),
			'live'         => array(
				'label'  => __( 'Live coverage', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count', 'layout', 'show_more' ),
			),
			'authors'      => array(
				'label'  => __( 'Authors', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'topics'       => array(
				'label'  => __( 'Topics / sections', 'infy-news-os-core' ),
				'fields' => array( 'title', 'count' ),
			),
			'newsletter'   => array(
				'label'  => __( 'Newsletter', 'infy-news-os-core' ),
				'fields' => array( 'dark' ),
			),
			'ad'           => array(
				'label'  => __( 'Advertisement', 'infy-news-os-core' ),
				'fields' => array( 'ad_slot' ),
			),
			'html'         => array(
				'label'  => __( 'Custom HTML', 'infy-news-os-core' ),
				'fields' => array( 'title', 'html', 'dark' ),
			),
		);
	}

	/**
	 * Layout choices by context.
	 *
	 * @param string $context hero|block|stories|split.
	 * @return array<string, string>
	 */
	public static function layouts( $context = 'block' ) {
		if ( 'hero' === $context ) {
			return array(
				'lead-grid' => __( 'Lead + secondary grid', 'infy-news-os-core' ),
				'lead-left' => __( 'Secondary first, lead right', 'infy-news-os-core' ),
				'stacked'   => __( 'Stacked (lead above)', 'infy-news-os-core' ),
				'mosaic'    => __( 'Lead full-width, four across', 'infy-news-os-core' ),
				'lead-only' => __( 'Lead story only', 'infy-news-os-core' ),
				'slider'    => __( 'Full-width slider', 'infy-news-os-core' ),
			);
		}
		if ( 'stories' === $context ) {
			return array(
				'circles'  => __( 'Circles', 'infy-news-os-core' ),
				'carousel' => __( 'Carousel', 'infy-news-os-core' ),
				'grid'     => __( 'Grid', 'infy-news-os-core' ),
			);
		}
		if ( 'split' === $context ) {
			return array(
				'latest-trending' => __( 'Latest | Trending', 'infy-news-os-core' ),
				'trending-latest' => __( 'Trending | Latest', 'infy-news-os-core' ),
				'stacked'         => __( 'Stacked', 'infy-news-os-core' ),
			);
		}
		return array(
			'cards'     => __( 'Image cards', 'infy-news-os-core' ),
			'compact'   => __( 'Compact headlines', 'infy-news-os-core' ),
			'magazine'  => __( 'Magazine (lead + list)', 'infy-news-os-core' ),
			'overlay'   => __( 'Overlay cards', 'infy-news-os-core' ),
			'grid-2'    => __( '2 columns', 'infy-news-os-core' ),
			'grid-3'    => __( '3 columns', 'infy-news-os-core' ),
			'grid-5'    => __( '5 columns', 'infy-news-os-core' ),
			'list'      => __( 'Text list', 'infy-news-os-core' ),
		);
	}

	/**
	 * Empty module with defaults.
	 *
	 * @param string               $type Type.
	 * @param array<string, mixed> $over Overrides.
	 * @return array<string, mixed>
	 */
	public static function blank( $type, $over = array() ) {
		$base = array(
			'id'           => 'mod_' . wp_generate_password( 8, false, false ),
			'type'         => $type,
			'enabled'      => 1,
			'title'        => '',
			'subtitle'     => '',
			'category'     => 0,
			'tag'          => 0,
			'count'        => 4,
			'layout'       => '',
			'orderby'      => 'date',
			'unique'       => 1,
			'show_more'    => 1,
			'more_text'    => '',
			'show_excerpt' => 0,
			'show_meta'    => 1,
			'show_thumb'   => 1,
			'dark'         => 0,
			'ad_slot'      => 'between_cards',
			'html'         => '',
			'tabs'         => '',
		);
		if ( 'hero' === $type ) {
			$base['layout'] = 'lead-grid';
			$base['count']  = 4;
		} elseif ( 'web_stories' === $type ) {
			$base['layout'] = 'circles';
			$base['count']  = 10;
		} elseif ( 'split' === $type ) {
			$base['layout'] = 'latest-trending';
			$base['count']  = 8;
		} elseif ( 'slider' === $type ) {
			$base['count'] = 6;
		} elseif ( 'latest' === $type ) {
			$base['count'] = 8;
		} elseif ( 'trending' === $type ) {
			$base['count'] = 6;
		} elseif ( 'authors' === $type || 'topics' === $type ) {
			$base['count'] = 6;
		} elseif ( 'live' === $type ) {
			$base['count']  = 4;
			$base['layout'] = 'cards';
		} elseif ( in_array( $type, array( 'category', 'posts', 'tabs' ), true ) ) {
			$base['layout'] = 'cards';
		}
		return wp_parse_args( $over, $base );
	}

	/**
	 * Default stack matching the current homepage.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function defaults() {
		$mods   = array();
		$mods[] = self::blank(
			'intro',
			array(
				'title'    => (string) inos_get_option( 'home_kicker', '' ),
				'subtitle' => (string) inos_get_option( 'home_intro', '' ),
			)
		);
		$mods[] = self::blank(
			'hero',
			array(
				'enabled'      => inos_get_option( 'show_hero', 1 ) ? 1 : 0,
				'layout'       => (string) inos_get_option( 'hero_layout', 'lead-grid' ),
				'count'        => absint( inos_get_option( 'secondary_count', 4 ) ),
				'show_excerpt' => inos_get_option( 'show_lead_dek', 1 ) ? 1 : 0,
			)
		);
		$mods[] = self::blank(
			'web_stories',
			array(
				'enabled' => inos_get_option( 'show_home_web_stories', 1 ) ? 1 : 0,
				'title'   => (string) inos_get_option( 'web_stories_title', '' ),
				'count'   => absint( inos_get_option( 'web_stories_count', 10 ) ),
				'layout'  => (string) inos_get_option( 'web_stories_view', 'circles' ),
			)
		);
		$mods[] = self::blank(
			'ad',
			array(
				'enabled' => inos_get_option( 'show_home_ads', 1 ) ? 1 : 0,
				'ad_slot' => 'between_cards',
			)
		);

		$style = (string) inos_get_option( 'section_style', 'cards' );
		$count = absint( inos_get_option( 'section_count', 4 ) );
		$more  = inos_get_option( 'show_section_more', 1 ) ? 1 : 0;
		$ids   = function_exists( 'inos_get_homepage_section_ids' ) ? inos_get_homepage_section_ids() : array();
		foreach ( $ids as $term_id ) {
			$term = get_term( $term_id, 'category' );
			$mods[] = self::blank(
				'category',
				array(
					'title'     => ( $term && ! is_wp_error( $term ) ) ? $term->name : '',
					'category'  => (int) $term_id,
					'count'     => $count,
					'layout'    => $style,
					'show_more' => $more,
				)
			);
		}

		$mods[] = self::blank(
			'split',
			array(
				'enabled' => ( inos_get_option( 'show_latest', 1 ) || inos_get_option( 'show_trending', 1 ) ) ? 1 : 0,
				'layout'  => (string) inos_get_option( 'split_layout', 'latest-trending' ),
				'count'   => absint( inos_get_option( 'latest_count', 8 ) ),
			)
		);
		$mods[] = self::blank(
			'newsletter',
			array(
				'enabled' => inos_get_option( 'show_home_newsletter', 1 ) ? 1 : 0,
			)
		);

		return $mods;
	}

	/**
	 * Stored or default modules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		$stored = get_option( self::OPTION, null );
		if ( ! is_array( $stored ) || empty( $stored ) ) {
			return self::defaults();
		}
		return array_values( array_map( array( __CLASS__, 'sanitize_module' ), $stored ) );
	}

	/**
	 * Enabled modules for the front page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function enabled() {
		self::$used_ids = array();
		$out            = array();
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
			if ( ! is_array( $mod ) ) {
				continue;
			}
			$clean[] = self::sanitize_module( $mod );
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
		$type  = isset( $mod['type'] ) ? sanitize_key( $mod['type'] ) : 'posts';
		if ( ! in_array( $type, $types, true ) ) {
			$type = 'posts';
		}
		$out = self::blank( $type );
		if ( ! empty( $mod['id'] ) ) {
			$out['id'] = sanitize_key( $mod['id'] );
		}
		$out['enabled']      = empty( $mod['enabled'] ) ? 0 : 1;
		$out['title']        = isset( $mod['title'] ) ? sanitize_text_field( $mod['title'] ) : '';
		$out['subtitle']     = isset( $mod['subtitle'] ) ? sanitize_textarea_field( $mod['subtitle'] ) : '';
		$out['category']     = isset( $mod['category'] ) ? absint( $mod['category'] ) : 0;
		$out['tag']          = isset( $mod['tag'] ) ? absint( $mod['tag'] ) : 0;
		$out['count']        = isset( $mod['count'] ) ? max( 1, min( 24, absint( $mod['count'] ) ) ) : $out['count'];
		$out['layout']       = isset( $mod['layout'] ) ? sanitize_text_field( $mod['layout'] ) : $out['layout'];
		$order               = isset( $mod['orderby'] ) ? sanitize_key( $mod['orderby'] ) : 'date';
		$out['orderby']      = in_array( $order, array( 'date', 'modified', 'comment_count', 'views', 'rand' ), true ) ? $order : 'date';
		$out['unique']       = empty( $mod['unique'] ) ? 0 : 1;
		$out['show_more']    = empty( $mod['show_more'] ) ? 0 : 1;
		$out['more_text']    = isset( $mod['more_text'] ) ? sanitize_text_field( $mod['more_text'] ) : '';
		$out['show_excerpt'] = empty( $mod['show_excerpt'] ) ? 0 : 1;
		$out['show_meta']    = empty( $mod['show_meta'] ) ? 0 : 1;
		$out['show_thumb']   = empty( $mod['show_thumb'] ) ? 0 : 1;
		$out['dark']         = empty( $mod['dark'] ) ? 0 : 1;
		$slot                = isset( $mod['ad_slot'] ) ? sanitize_key( $mod['ad_slot'] ) : 'between_cards';
		$out['ad_slot']      = in_array( $slot, array( 'header', 'below_ticker', 'between_cards', 'sidebar', 'footer' ), true ) ? $slot : 'between_cards';
		$out['html']         = isset( $mod['html'] ) ? wp_kses_post( $mod['html'] ) : '';
		$out['tabs']         = isset( $mod['tabs'] ) ? sanitize_text_field( $mod['tabs'] ) : '';
		return $out;
	}

	/**
	 * Query posts for a module and record used IDs.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return WP_Post[]
	 */
	public static function query( $mod ) {
		$count  = max( 1, absint( $mod['count'] ) );
		$unique = ! empty( $mod['unique'] ) && inos_get_option( 'home_unique_posts', 1 );
		$args   = array(
			'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		if ( ! empty( $mod['category'] ) ) {
			$args['cat'] = absint( $mod['category'] );
		}
		if ( ! empty( $mod['tag'] ) ) {
			$args['tag_id'] = absint( $mod['tag'] );
		}
		if ( $unique && self::$used_ids ) {
			$args['post__not_in'] = self::$used_ids;
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
		$posts = $query->posts;
		foreach ( $posts as $post ) {
			if ( $unique ) {
				self::$used_ids[] = (int) $post->ID;
			}
		}
		if ( $unique ) {
			self::$used_ids = array_values( array_unique( self::$used_ids ) );
		}
		return $posts;
	}

	/**
	 * Mark IDs used (hero lead/secondary).
	 *
	 * @param int[] $ids IDs.
	 */
	public static function mark_used( $ids ) {
		foreach ( (array) $ids as $id ) {
			$id = absint( $id );
			if ( $id ) {
				self::$used_ids[] = $id;
			}
		}
		self::$used_ids = array_values( array_unique( self::$used_ids ) );
	}

	/**
	 * Tab category IDs.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return int[]
	 */
	public static function tab_ids( $mod ) {
		$raw = isset( $mod['tabs'] ) ? $mod['tabs'] : '';
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
		return array_values( array_unique( $ids ) );
	}

	/**
	 * More-link URL for a module.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return string
	 */
	public static function more_url( $mod ) {
		if ( ! empty( $mod['category'] ) ) {
			$link = get_term_link( (int) $mod['category'], 'category' );
			return is_wp_error( $link ) ? '' : $link;
		}
		if ( ! empty( $mod['tag'] ) ) {
			$link = get_term_link( (int) $mod['tag'], 'post_tag' );
			return is_wp_error( $link ) ? '' : $link;
		}
		if ( 'live' === $mod['type'] ) {
			$link = get_post_type_archive_link( 'inos_live_blog' );
			return $link ? $link : '';
		}
		$page = get_option( 'page_for_posts' );
		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	/**
	 * Display title.
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
		$types = self::types();
		return isset( $types[ $mod['type'] ] ) ? $types[ $mod['type'] ]['label'] : '';
	}

	/**
	 * Featured authors.
	 *
	 * @param int $count Count.
	 * @return array<int, array<string, mixed>>
	 */
	public static function authors( $count = 6 ) {
		$users = get_users(
			array(
				'capability'          => 'edit_posts',
				'has_published_posts' => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : array( 'post' ),
				'orderby'             => 'post_count',
				'order'               => 'DESC',
				'number'              => max( 1, absint( $count ) ),
			)
		);
		$out = array();
		foreach ( $users as $user ) {
			if ( function_exists( 'inos_get_author_profile' ) ) {
				$out[] = inos_get_author_profile( $user->ID );
			}
		}
		return array_filter( $out );
	}

	/**
	 * Topic terms (parent categories first).
	 *
	 * @param int $count Count.
	 * @return WP_Term[]
	 */
	public static function topics( $count = 8 ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => max( 1, absint( $count ) ),
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);
		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Live blogs.
	 *
	 * @param int $count Count.
	 * @return WP_Post[]
	 */
	public static function live_posts( $count = 4 ) {
		$count = max( 1, absint( $count ) );
		$base  = array(
			'post_type'           => 'inos_live_blog',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'modified',
			'order'               => 'DESC',
		);
		$open  = new WP_Query(
			array_merge(
				$base,
				array(
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						array(
							'key'     => '_inos_coverage_closed',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_inos_coverage_closed',
							'value'   => '1',
							'compare' => '!=',
						),
					),
				)
			)
		);
		if ( $open->posts ) {
			return $open->posts;
		}
		$query = new WP_Query( $base );
		return $query->posts;
	}

	/**
	 * Category / tag options for admin.
	 *
	 * @return array{categories: array<int,string>, tags: array<int,string>}
	 */
	public static function tax_choices() {
		$cats = array( 0 => __( '— All / latest —', 'infy-news-os-core' ) );
		$tags = array( 0 => __( '— None —', 'infy-news-os-core' ) );
		$ct   = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
		if ( ! is_wp_error( $ct ) ) {
			foreach ( $ct as $term ) {
				$cats[ (int) $term->term_id ] = $term->name;
			}
		}
		$tt = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true, 'number' => 80 ) );
		if ( ! is_wp_error( $tt ) ) {
			foreach ( $tt as $term ) {
				$tags[ (int) $term->term_id ] = $term->name;
			}
		}
		return array(
			'categories' => $cats,
			'tags'       => $tags,
		);
	}
}
