<?php
/**
 * Public helper API for the theme and plugin modules.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'inos_get_option' ) ) {
	return;
}

/**
 * Get a plugin setting.
 *
 * @param string $key     Option key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function inos_get_option( $key, $default = null ) {
	return INOS_Settings::get( $key, $default );
}

/**
 * Active visual look id (editorial, broadsheet, magazine, digital, app).
 *
 * @return string
 */
function inos_theme_preset() {
	if ( class_exists( 'INOS_Presets' ) ) {
		return INOS_Presets::current();
	}
	return 'editorial';
}

/**
 * Whether the hybrid news-app look is active.
 *
 * @return bool
 */
function inos_is_app_preset() {
	return class_exists( 'INOS_Presets' ) && INOS_Presets::is_app();
}

/**
 * Archive listing pagination type (numbered, load_more, or infinite).
 * AMP listings always use numbered pages.
 *
 * @return string
 */
function inos_archive_pagination_type() {
	if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
		return 'numbered';
	}
	$type = function_exists( 'inos_get_option' ) ? (string) inos_get_option( 'archive_pagination', 'numbered' ) : 'numbered';
	if ( class_exists( 'INOS_Settings' ) ) {
		return INOS_Settings::sanitize_archive_pagination( $type );
	}
	return in_array( $type, array( 'numbered', 'load_more', 'infinite' ), true ) ? $type : 'numbered';
}

/**
 * Whether the article related list should use a Load more button.
 * AMP always renders the full related list with no scripted paging.
 *
 * @return bool
 */
function inos_related_load_more_enabled() {
	if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
		return false;
	}
	return function_exists( 'inos_get_option' ) ? (bool) (int) inos_get_option( 'related_load_more', 1 ) : true;
}

/**
 * First-paint related row count (capped by the maximum related setting).
 *
 * @return int
 */
function inos_related_load_more_initial() {
	$initial = function_exists( 'inos_get_option' ) ? absint( inos_get_option( 'related_load_more_initial', 3 ) ) : 3;
	$max     = function_exists( 'inos_get_option' ) ? absint( inos_get_option( 'related_count', 6 ) ) : 6;
	if ( $initial < 1 ) {
		$initial = 3;
	}
	if ( $initial > 8 ) {
		$initial = 8;
	}
	if ( $max > 0 && $initial > $max ) {
		$initial = $max;
	}
	return $initial;
}

/**
 * Front-end chrome string (overridable in Settings → Language / Labels).
 *
 * @param string             $key  Catalog key.
 * @param array<int, string> $args vsprintf replacements.
 * @return string
 */
function inos_label( $key, $args = array() ) {
	if ( class_exists( 'INOS_Labels' ) ) {
		return INOS_Labels::get( $key, $args );
	}
	return $args ? vsprintf( $key, array_values( (array) $args ) ) : $key;
}

/**
 * Plural chrome string.
 *
 * @param string $one    Singular catalog key.
 * @param string $many   Plural catalog key.
 * @param int    $number Count.
 * @return string
 */
function inos_label_n( $one, $many, $number ) {
	if ( class_exists( 'INOS_Labels' ) ) {
		return INOS_Labels::get_n( $one, $many, $number );
	}
	return (string) $number;
}

/**
 * Whether a post is currently breaking.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function inos_is_breaking( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return false;
	}
	if ( '1' !== (string) get_post_meta( $post_id, '_inos_breaking', true ) ) {
		return false;
	}

	$until = absint( get_post_meta( $post_id, '_inos_breaking_until', true ) );
	if ( $until ) {
		return time() < $until;
	}

	$hours = max( 1, absint( inos_get_option( 'breaking_duration_hours', 24 ) ) );
	$post  = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}
	$stamp = strtotime( $post->post_modified_gmt . ' GMT' );
	return ( time() - $stamp ) < ( $hours * HOUR_IN_SECONDS );
}

/**
 * Article kicker.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function inos_get_kicker( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return $post_id ? (string) get_post_meta( $post_id, '_inos_kicker', true ) : '';
}

/**
 * Article dek / subheadline.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function inos_get_dek( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$dek = (string) get_post_meta( $post_id, '_inos_dek', true );
	if ( $dek ) {
		return $dek;
	}
	return (string) get_the_excerpt( $post_id );
}

/**
 * Dateline.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function inos_get_dateline( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	return $post_id ? (string) get_post_meta( $post_id, '_inos_dateline', true ) : '';
}

/**
 * Estimated reading time in minutes (≈200 wpm).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function inos_reading_minutes( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return 1;
	}
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Google News keywords: editor field, else section + tags.
 *
 * @param int $post_id Post ID.
 * @return string Comma-separated, max 10.
 */
function inos_get_news_keywords( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$custom = trim( (string) get_post_meta( $post_id, '_inos_news_keywords', true ) );
	if ( $custom ) {
		$bits = array_filter( array_map( 'trim', explode( ',', $custom ) ) );
		$bits = array_values( array_unique( array_map( 'wp_strip_all_tags', $bits ) ) );
		return implode( ', ', array_slice( $bits, 0, 10 ) );
	}

	$bits = array();
	$sec  = inos_get_primary_section( $post_id );
	if ( $sec ) {
		$bits[] = $sec->name;
	}
	$tags = get_the_tags( $post_id );
	if ( $tags ) {
		foreach ( $tags as $tag ) {
			$bits[] = $tag->name;
			if ( count( $bits ) >= 10 ) {
				break;
			}
		}
	}

	$bits = array_values( array_unique( array_filter( array_map( 'wp_strip_all_tags', $bits ) ) ) );
	return implode( ', ', array_slice( $bits, 0, 10 ) );
}

/**
 * Visible + JSON-LD breadcrumb trail.
 *
 * @return array<int, array{name:string, url:string}>
 */
function inos_get_breadcrumb_items() {
	$items   = array();
	$items[] = array(
		'name' => (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) ),
		'url'  => home_url( '/' ),
	);

	if ( is_singular( array( 'post', 'inos_live_blog' ) ) ) {
		$section = inos_get_primary_section( get_the_ID() );
		if ( $section ) {
			$link = get_term_link( $section );
			if ( ! is_wp_error( $link ) ) {
				$items[] = array(
					'name' => $section->name,
					'url'  => $link,
				);
			}
		}
		$items[] = array(
			'name' => wp_strip_all_tags( get_the_title() ),
			'url'  => get_permalink(),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			if ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) {
				$ancestors = array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) );
				foreach ( $ancestors as $ancestor_id ) {
					$ancestor = get_term( $ancestor_id, $term->taxonomy );
					if ( $ancestor && ! is_wp_error( $ancestor ) ) {
						$alink = get_term_link( $ancestor );
						$items[] = array(
							'name' => $ancestor->name,
							'url'  => is_wp_error( $alink ) ? '' : $alink,
						);
					}
				}
			}
			$link = get_term_link( $term );
			$items[] = array(
				'name' => $term->name,
				'url'  => is_wp_error( $link ) ? '' : $link,
			);
		}
	} elseif ( is_author() ) {
		$items[] = array(
			'name' => inos_label( 'authors' ),
			'url'  => '',
		);
		$items[] = array(
			'name' => get_the_author_meta( 'display_name', get_queried_object_id() ),
			'url'  => get_author_posts_url( get_queried_object_id() ),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'name' => inos_label( 'search_heading', array( get_search_query() ) ),
			'url'  => '',
		);
	} elseif ( is_home() && ! is_front_page() ) {
		$page_id = (int) get_option( 'page_for_posts' );
		$items[] = array(
			'name' => $page_id ? wp_strip_all_tags( get_the_title( $page_id ) ) : inos_label( 'latest_stories' ),
			'url'  => $page_id ? get_permalink( $page_id ) : home_url( '/' ),
		);
	} elseif ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( get_query_var( 'post_type' ) );
		$items[] = array(
			'name' => post_type_archive_title( '', false ),
			'url'  => $link ? $link : '',
		);
	} elseif ( is_year() || is_month() || is_day() ) {
		$items[] = array(
			'name' => wp_strip_all_tags( get_the_archive_title() ),
			'url'  => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'name' => inos_label( 'page_not_found' ),
			'url'  => '',
		);
	} elseif ( is_singular() ) {
		$items[] = array(
			'name' => wp_strip_all_tags( get_the_title() ),
			'url'  => get_permalink(),
		);
	}

	return $items;
}

/**
 * Previous / next story in the same section.
 *
 * @param int $post_id Post ID.
 * @return array{prev:?WP_Post, next:?WP_Post}
 */
function inos_adjacent_stories( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$same    = (bool) inos_get_primary_section( $post_id );
	return array(
		'prev' => get_adjacent_post( $same, '', true, 'category' ),
		'next' => get_adjacent_post( $same, '', false, 'category' ),
	);
}

/**
 * Seeded policy page permalink.
 *
 * @param string $slug about|contact|editorial-policy|corrections.
 * @return string
 */
function inos_policy_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ) );
	return $page ? get_permalink( $page ) : '';
}

/**
 * Primary section term.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function inos_get_primary_section( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	$primary = absint( get_post_meta( $post_id, '_inos_primary_section', true ) );
	if ( $primary ) {
		$term = get_term( $primary, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}

	$cats = get_the_category( $post_id );
	return ( ! empty( $cats[0] ) ) ? $cats[0] : null;
}

/**
 * Print article byline HTML.
 *
 * @param int $post_id Post ID.
 */
function inos_the_byline( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$author_id = (int) get_post_field( 'post_author', $post_id );
	$profile   = inos_get_author_profile( $author_id );
	$name      = $profile ? $profile['name'] : get_the_author_meta( 'display_name', $author_id );
	$job       = $profile ? $profile['job_title'] : get_user_meta( $author_id, 'inos_job_title', true );
	$location  = $profile ? $profile['location'] : '';
	$avatar    = get_avatar( $author_id, 56, 'mystery', $name, array( 'class' => 'inos-byline__avatar', 'force_display' => true ) );
	$published = get_post_time( DATE_W3C, true, $post_id );
	$modified  = get_post_modified_time( DATE_W3C, true, $post_id );
	$show_upd  = $modified && strtotime( $modified ) - strtotime( $published ) > MINUTE_IN_SECONDS;
	$utc       = 'utc' === inos_get_option( 'datetime_display', 'site' );
	$pub_label = $utc
		? get_post_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), true, $post_id ) . ' UTC'
		: get_the_date( '', $post_id ) . ' ' . get_the_time( '', $post_id );
	$mod_label = $utc
		? get_post_modified_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), true, $post_id ) . ' UTC'
		: get_the_modified_date( '', $post_id ) . ' ' . get_the_modified_time( '', $post_id );

	$meta_bits = array_filter( array( $job, $location ) );

	echo '<div class="inos-byline">';
	echo '<a class="inos-byline__media" rel="author" href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . $avatar . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="inos-byline__text">';
	echo '<a class="inos-byline__name" rel="author" href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( $name ) . '</a>';
	if ( $meta_bits ) {
		echo '<span class="inos-byline__job">' . esc_html( implode( ' · ', $meta_bits ) ) . '</span>';
	}
	echo '<p class="inos-byline__dates">';
	echo '<time datetime="' . esc_attr( $published ) . '">' . esc_html( $pub_label ) . '</time>';
	if ( $show_upd ) {
		echo ' <span class="inos-byline__updated">' . esc_html( inos_label( 'updated' ) ) . ' <time datetime="' . esc_attr( $modified ) . '">' . esc_html( $mod_label ) . '</time></span>';
	}
	$meta_extra = array();
	if ( inos_get_option( 'show_reading_time', 1 ) ) {
		$mins         = inos_reading_minutes( $post_id );
		$meta_extra[] = '<span class="inos-byline__read">' . esc_html( inos_label( 'min_read', array( (string) $mins ) ) ) . '</span>';
	}
	if ( inos_get_option( 'show_view_count', 0 ) ) {
		$views = absint( get_post_meta( $post_id, '_inos_views', true ) );
		if ( $views ) {
			$meta_extra[] = '<span class="inos-byline__views">' . esc_html( inos_label_n( 'view_one', 'view_many', $views ) ) . '</span>';
		}
	}
	if ( $meta_extra ) {
		echo ' <span class="inos-byline__stats">' . implode( ' · ', $meta_extra ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</p></div></div>';
}

/**
 * Author profile array for archives, boxes, and schema consumers.
 *
 * @param int $user_id User ID.
 * @return array<string, mixed>
 */
function inos_get_author_profile( $user_id = 0 ) {
	$user_id = $user_id ? absint( $user_id ) : get_the_author_meta( 'ID' );
	if ( ! $user_id || ! class_exists( 'INOS_Author' ) ) {
		return array();
	}
	return INOS_Author::profile( $user_id );
}

/**
 * Print author social links.
 *
 * @param array<string, mixed> $profile Profile.
 */
function inos_the_author_social( $profile ) {
	if ( empty( $profile['social'] ) || ! is_array( $profile['social'] ) ) {
		return;
	}
	echo '<ul class="inos-author-social">';
	foreach ( $profile['social'] as $item ) {
		$network = sanitize_key( $item['network'] );
		$is_mail = ( 0 === strpos( $item['url'], 'mailto:' ) );
		echo '<li>';
		echo '<a class="inos-author-social__btn inos-author-social__btn--' . esc_attr( $network ) . '" href="' . esc_url( $item['url'] ) . '"';
		if ( ! $is_mail ) {
			echo ' target="_blank" rel="noopener noreferrer me"';
		}
		echo ' aria-label="' . esc_attr( $item['label'] ) . '">';
		if ( function_exists( 'inos_theme_share_icon' ) ) {
			$icon = inos_theme_share_icon( $network );
			if ( $icon ) {
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		echo '<span class="screen-reader-text">' . esc_html( $item['label'] ) . '</span>';
		echo '</a></li>';
	}
	echo '</ul>';
}

/**
 * Render a configured ad slot.
 *
 * @param string $id Slot id.
 */
function inos_ad_slot( $id ) {
	INOS_Ads::render( $id );
}

/**
 * Related posts query.
 *
 * @param int   $post_id Post ID.
 * @param int   $count   Number of posts.
 * @param int[] $exclude Extra IDs to skip.
 * @return WP_Post[]
 */
function inos_related_posts( $post_id = 0, $count = 0, $exclude = array() ) {
	return INOS_Trending::related( $post_id, $count, $exclude );
}

/**
 * Editor-picked Also read posts.
 *
 * @param int $post_id Post ID.
 * @return WP_Post[]
 */
function inos_also_read_posts( $post_id = 0 ) {
	return class_exists( 'INOS_Trending' ) ? INOS_Trending::also_read( $post_id ) : array();
}

/**
 * Trending posts.
 *
 * @param int $count Number of posts.
 * @return WP_Post[]
 */
function inos_trending_posts( $count = 0 ) {
	return INOS_Trending::trending( $count );
}

/**
 * Print newsletter form.
 */
function inos_newsletter_form() {
	INOS_Newsletter::render_form();
}

/**
 * Breaking posts still in window.
 *
 * @param int $limit Max posts.
 * @return WP_Post[]
 */
function inos_get_breaking_posts( $limit = 8 ) {
	return INOS_Breaking::get_posts( $limit );
}

/**
 * Story types that mix into Latest, hero, sections, and the ticker.
 *
 * @return string[]
 */
function inos_story_post_types() {
	$types = array( 'post' );
	if ( post_type_exists( 'inos_live_blog' ) ) {
		$types[] = 'inos_live_blog';
	}
	return $types;
}

/**
 * Mix live blogs into category, tag, type, and date archives.
 *
 * @param WP_Query $query Query.
 */
function inos_filter_story_archives( $query ) {
	if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_singular() || $query->is_post_type_archive() || $query->is_page() ) {
		return;
	}
	if ( ! $query->is_category() && ! $query->is_tag() && ! $query->is_tax( 'inos_article_type' ) && ! $query->is_date() && ! $query->is_author() && ! $query->is_home() ) {
		return;
	}

	$current = $query->get( 'post_type' );
	if ( ! empty( $current ) && 'post' !== $current && array( 'post' ) !== $current ) {
		return;
	}
	$query->set( 'post_type', inos_story_post_types() );
}
add_action( 'pre_get_posts', 'inos_filter_story_archives' );

/**
 * Whether a story may appear in sitemaps and Google News RSS.
 *
 * @param int|WP_Post $post Post.
 * @return bool
 */
function inos_is_indexable( $post = 0 ) {
	$post = get_post( $post );
	return class_exists( 'INOS_Sitemaps' ) && INOS_Sitemaps::is_indexable( $post );
}

/**
 * RSS/Google News body, including live-blog updates.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function inos_feed_story_html( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$html    = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
	if ( 'inos_live_blog' !== get_post_type( $post_id ) || ! class_exists( 'INOS_Liveblog' ) ) {
		return $html;
	}

	$updates = INOS_Liveblog::get_updates( $post_id );
	if ( ! $updates ) {
		return $html;
	}

	$parts = array();
	foreach ( array_slice( $updates, 0, 12 ) as $update ) {
		$parts[] = '<h3>' . esc_html( get_the_title( $update ) ) . '</h3>' . apply_filters( 'the_content', $update->post_content );
	}
	return $html . implode( '', $parts );
}

/**
 * Keep open live blogs in Latest even when older stories fill the date window.
 *
 * @param WP_Post[] $posts Existing list.
 * @param int       $count Target count.
 * @return WP_Post[]
 */
function inos_with_open_live_blogs( $posts, $count ) {
	$count = max( 1, absint( $count ) );
	$posts = is_array( $posts ) ? $posts : array();
	if ( ! class_exists( 'INOS_Liveblog' ) || ! post_type_exists( 'inos_live_blog' ) ) {
		return array_slice( $posts, 0, $count );
	}

	$live_q = new WP_Query(
		array(
			'post_type'           => 'inos_live_blog',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'modified',
			'order'               => 'DESC',
		)
	);

	$head = array();
	$seen = array();
	foreach ( $live_q->posts as $post ) {
		if ( ! INOS_Liveblog::is_live( $post->ID ) ) {
			continue;
		}
		$head[] = $post;
		$seen[] = (int) $post->ID;
	}

	$out = $head;
	foreach ( $posts as $post ) {
		if ( in_array( (int) $post->ID, $seen, true ) ) {
			continue;
		}
		$out[]  = $post;
		$seen[] = (int) $post->ID;
	}

	return array_slice( $out, 0, $count );
}

/**
 * Top-level sections for mega menu.
 *
 * @return WP_Term[]
 */
function inos_get_sections_for_menu() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 12,
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Latest posts for a section.
 *
 * @param int $term_id Category ID.
 * @param int $count   Count.
 * @return WP_Post[]
 */
function inos_get_section_posts( $term_id, $count = 4 ) {
	$query = new WP_Query(
		array(
			'post_type'           => inos_story_post_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => absint( $count ),
			'cat'                 => absint( $term_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	return $query->posts;
}

/**
 * Featured pin for a section mega menu.
 *
 * @param int $term_id Category ID.
 * @return WP_Post|null
 */
function inos_get_section_featured_post( $term_id ) {
	$term_id = absint( $term_id );
	$q       = new WP_Query(
		array(
			'post_type'           => inos_story_post_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'cat'                 => $term_id,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'meta_key'            => '_inos_trending_pin', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'          => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( ! empty( $q->posts[0] ) ) {
		return $q->posts[0];
	}

	$lead = inos_get_lead_post();
	if ( $lead && has_term( $term_id, 'category', $lead ) ) {
		return $lead;
	}

	$latest = inos_get_section_posts( $term_id, 1 );
	return ! empty( $latest[0] ) ? $latest[0] : null;
}

/**
 * Lead story for homepage.
 *
 * @return WP_Post|null
 */
function inos_get_lead_post() {
	$pinned = absint( inos_get_option( 'lead_post_id', 0 ) );
	if ( $pinned ) {
		$post = get_post( $pinned );
		if ( $post && 'publish' === $post->post_status && in_array( $post->post_type, inos_story_post_types(), true ) ) {
			return $post;
		}
	}

	$q = new WP_Query(
		array(
			'post_type'           => inos_story_post_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
		)
	);
	return ! empty( $q->posts[0] ) ? $q->posts[0] : null;
}

/**
 * Secondary homepage posts excluding lead.
 *
 * @param int      $exclude_id Lead ID.
 * @param int|null $count      Count.
 * @return WP_Post[]
 */
function inos_get_secondary_posts( $exclude_id = 0, $count = null ) {
	if ( null === $count ) {
		$count = absint( inos_get_option( 'secondary_count', 4 ) );
	}
	$q = new WP_Query(
		array(
			'post_type'           => inos_story_post_types(),
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $count ),
			'post__not_in'        => $exclude_id ? array( absint( $exclude_id ) ) : array(),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	return $q->posts;
}

/**
 * Homepage section category IDs.
 *
 * @return int[]
 */
function inos_get_homepage_section_ids() {
	$slots = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$id = absint( inos_get_option( 'section_' . $i, 0 ) );
		if ( $id ) {
			$slots[] = $id;
		}
	}
	if ( $slots ) {
		return array_values( array_unique( $slots ) );
	}

	$raw = (string) inos_get_option( 'section_rows', '' );
	if ( '' === trim( $raw ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 4,
				'exclude'    => array( (int) get_option( 'default_category' ) ),
			)
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}
		return wp_list_pluck( $terms, 'term_id' );
	}

	$ids = array();
	foreach ( preg_split( '/[\s,]+/', $raw ) as $part ) {
		$part = trim( $part );
		if ( is_numeric( $part ) ) {
			$ids[] = absint( $part );
			continue;
		}
		$term = get_term_by( 'slug', sanitize_title( $part ), 'category' );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Whether plugin helpers should skip lazy-load on LCP image.
 *
 * @return bool
 */
function inos_skip_lcp_lazy() {
	return (bool) inos_get_option( 'skip_lcp_lazy', 1 );
}

/**
 * Print the article featured image (eager LCP, caption, credit).
 *
 * @param int $post_id Post ID.
 */
function inos_the_featured_figure( $post_id = 0 ) {
	if ( class_exists( 'INOS_Images' ) ) {
		echo INOS_Images::featured_figure_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Theme thumbnail attributes (lazy or LCP).
 *
 * @param int    $post_id Post ID.
 * @param string $size    Size.
 * @param bool   $lcp     LCP candidate.
 * @return array<string, string>
 */
function inos_image_attrs( $post_id, $size = 'inos-card', $lcp = false ) {
	if ( class_exists( 'INOS_Images' ) ) {
		return INOS_Images::theme_attrs( $post_id, $size, $lcp );
	}
	$attr = array(
		'class'    => 'inos-card__img',
		'alt'      => wp_strip_all_tags( get_the_title( $post_id ) ),
		'decoding' => 'async',
	);
	if ( $lcp && function_exists( 'inos_skip_lcp_lazy' ) && inos_skip_lcp_lazy() ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
	} else {
		$attr['loading'] = 'lazy';
	}
	return $attr;
}

/**
 * Article type slug.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function inos_get_article_type( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return 'news';
	}
	$terms = get_the_terms( $post_id, 'inos_article_type' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return 'news';
	}
	return $terms[0]->slug;
}

/**
 * SameAs URLs from publisher settings.
 *
 * @return string[]
 */
function inos_publisher_sameas() {
	$raw = (string) inos_get_option( 'sameas', '' );
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( $url ) {
			$out[] = $url;
		}
	}
	return $out;
}

/**
 * Publisher social profiles from sameAs URLs.
 *
 * @return array<int, array{network:string,label:string,url:string}>
 */
function inos_publisher_social() {
	$urls = inos_publisher_sameas();
	$out  = array();
	$seen = array();
	$map  = array(
		'x.com'         => array( 'twitter', __( 'X', 'infy-news-os-core' ) ),
		'twitter.com'   => array( 'twitter', __( 'X', 'infy-news-os-core' ) ),
		'linkedin.com'  => array( 'linkedin', __( 'LinkedIn', 'infy-news-os-core' ) ),
		'facebook.com'  => array( 'facebook', __( 'Facebook', 'infy-news-os-core' ) ),
		'instagram.com' => array( 'instagram', __( 'Instagram', 'infy-news-os-core' ) ),
		'youtube.com'   => array( 'youtube', __( 'YouTube', 'infy-news-os-core' ) ),
		'youtu.be'      => array( 'youtube', __( 'YouTube', 'infy-news-os-core' ) ),
		'wikipedia.org' => array( 'wikipedia', __( 'Wikipedia', 'infy-news-os-core' ) ),
		'github.com'    => array( 'github', __( 'GitHub', 'infy-news-os-core' ) ),
		't.me'          => array( 'telegram', __( 'Telegram', 'infy-news-os-core' ) ),
		'telegram.me'   => array( 'telegram', __( 'Telegram', 'infy-news-os-core' ) ),
		'wa.me'         => array( 'whatsapp', __( 'WhatsApp', 'infy-news-os-core' ) ),
		'whatsapp.com'  => array( 'whatsapp', __( 'WhatsApp', 'infy-news-os-core' ) ),
	);

	foreach ( $urls as $url ) {
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		$host = strtolower( preg_replace( '/^www\./', '', $host ) );
		if ( isset( $map[ $host ] ) ) {
			$network = $map[ $host ][0];
			$label   = $map[ $host ][1];
		} else {
			$network = 'website';
			$label   = __( 'Website', 'infy-news-os-core' );
		}
		$key = $network . ':' . $url;
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = 1;
		$out[]        = array(
			'network' => $network,
			'label'   => $label,
			'url'     => $url,
		);
	}

	return $out;
}

/**
 * Print publisher social icons.
 */
function inos_the_publisher_social() {
	$items = inos_publisher_social();
	if ( ! $items ) {
		return;
	}
	echo '<ul class="inos-drawer__social">';
	foreach ( $items as $item ) {
		$network = sanitize_key( $item['network'] );
		echo '<li>';
		echo '<a class="inos-drawer__social-btn inos-drawer__social-btn--' . esc_attr( $network ) . '" href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener noreferrer me" aria-label="' . esc_attr( $item['label'] ) . '">';
		if ( function_exists( 'inos_theme_share_icon' ) ) {
			$icon = inos_theme_share_icon( $network );
			if ( $icon ) {
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		echo '<span class="screen-reader-text">' . esc_html( $item['label'] ) . '</span>';
		echo '</a></li>';
	}
	echo '</ul>';
}

/**
 * Append UTM parameters to a shareable URL.
 *
 * @param string $url     Base URL.
 * @param string $network Network slug.
 * @param string $content utm_content.
 * @param string $context article|archive|related.
 * @return string
 */
function inos_apply_share_utm( $url, $network = 'share', $content = '', $context = 'article' ) {
	if ( ! $url || ! inos_get_option( 'enable_share_utm', 1 ) ) {
		return $url;
	}

	$network  = sanitize_key( $network );
	$campaign = (string) inos_get_option( 'utm_campaign', 'article-share' );
	$medium   = (string) inos_get_option( 'utm_medium', 'social' );

	if ( 'email' === $network ) {
		$medium = 'email';
	} elseif ( 'also-read' === $network ) {
		$medium   = 'article';
		$campaign = 'also-read';
	} elseif ( 'related' === $network || 'related' === $context ) {
		$medium = 'article';
		if ( '' === $campaign || 'article-share' === $campaign ) {
			$campaign = 'related-stories';
		}
	} elseif ( 'archive' === $context && ( '' === $campaign || 'article-share' === $campaign ) ) {
		$campaign = 'archive-share';
	}

	$args = array(
		'utm_source'   => $network ? $network : 'share',
		'utm_medium'   => $medium ? $medium : 'social',
		'utm_campaign' => $campaign ? $campaign : 'article-share',
	);
	if ( '' !== $content ) {
		$args['utm_content'] = $content;
	}

	return add_query_arg( $args, $url );
}

/**
 * Article permalink with share UTM parameters.
 *
 * @param int    $post_id Post ID.
 * @param string $network Network slug (twitter, facebook, related…).
 * @return string
 */
function inos_get_share_permalink( $post_id, $network = 'share' ) {
	$post_id = absint( $post_id );
	$url     = $post_id ? get_permalink( $post_id ) : '';
	if ( ! $url ) {
		return '';
	}
	$context = ( 'related' === $network || 'also-read' === $network ) ? 'related' : 'article';
	return inos_apply_share_utm( $url, $network, (string) $post_id, $context );
}

/**
 * Related-story permalink attributed to the current article.
 *
 * @param int $related_id Related post ID.
 * @param int $source_id  Current article ID.
 * @return string
 */
function inos_get_related_permalink( $related_id, $source_id = 0 ) {
	$url = inos_get_share_permalink( $related_id, 'related' );
	if ( $url && $source_id && inos_get_option( 'enable_share_utm', 1 ) ) {
		$url = add_query_arg( 'utm_content', 'from-' . absint( $source_id ), $url );
	}
	return $url;
}

/**
 * Also-read permalink attributed to the current article.
 *
 * @param int $also_id   Destination post ID.
 * @param int $source_id Current article ID.
 * @return string
 */
function inos_get_also_read_permalink( $also_id, $source_id = 0 ) {
	$url = inos_get_share_permalink( $also_id, 'also-read' );
	if ( $url && $source_id && inos_get_option( 'enable_share_utm', 1 ) ) {
		$url = add_query_arg( 'utm_content', 'from-' . absint( $source_id ), $url );
	}
	return $url;
}

/**
 * Share destinations for any URL (article or archive).
 *
 * @param string $url         Canonical URL.
 * @param string $title       Share title.
 * @param string $utm_content utm_content value.
 * @param string $context     article|archive.
 * @return array<string, array<string, string>>
 */
function inos_get_share_networks_for_url( $url, $title, $utm_content = '', $context = 'article' ) {
	$url   = esc_url_raw( $url );
	$title = wp_strip_all_tags( $title );
	$out   = array();
	if ( ! $url ) {
		return $out;
	}

	$networks = array(
		'native'   => inos_label( 'share' ),
		'twitter'  => inos_label( 'share_x' ),
		'facebook' => inos_label( 'share_facebook' ),
		'linkedin' => inos_label( 'share_linkedin' ),
		'whatsapp' => inos_label( 'share_whatsapp' ),
		'telegram' => inos_label( 'share_telegram' ),
		'email'    => inos_label( 'share_email' ),
		'copy'     => inos_label( 'copy_link' ),
	);

	foreach ( $networks as $network => $label ) {
		$share_url = inos_apply_share_utm( $url, $network, $utm_content, $context );
		$encoded   = rawurlencode( $share_url );
		$text      = rawurlencode( $title );

		switch ( $network ) {
			case 'twitter':
				$intent = 'https://twitter.com/intent/tweet?url=' . $encoded . '&text=' . $text;
				break;
			case 'facebook':
				$intent = 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded;
				break;
			case 'linkedin':
				$intent = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encoded;
				break;
			case 'whatsapp':
				$intent = 'https://api.whatsapp.com/send?text=' . rawurlencode( $title . ' ' . $share_url );
				break;
			case 'telegram':
				$intent = 'https://t.me/share/url?url=' . $encoded . '&text=' . $text;
				break;
			case 'email':
				$intent = 'mailto:?subject=' . $text . '&body=' . $encoded;
				break;
			default:
				$intent = $share_url;
				break;
		}

		$out[ $network ] = array(
			'label'     => $label,
			'share_url' => $share_url,
			'intent'    => $intent,
		);
	}

	return $out;
}

/**
 * Share destinations for an article.
 *
 * @param int $post_id Post ID.
 * @return array<string, array<string, string>>
 */
function inos_get_share_networks( $post_id ) {
	$post_id = absint( $post_id );
	return inos_get_share_networks_for_url(
		get_permalink( $post_id ),
		get_the_title( $post_id ),
		(string) $post_id,
		'article'
	);
}

/**
 * Canonical URL + title for the current archive (or latest index).
 *
 * @return array{url:string,title:string,text:string,utm_content:string,type:string}
 */
function inos_get_archive_share_context() {
	$pub = (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) );
	$out = array(
		'url'         => home_url( '/' ),
		'title'       => $pub,
		'text'        => $pub,
		'utm_content' => 'archive',
		'type'        => 'archive',
	);

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$out['url']         = $link;
				$out['title']       = $term->name;
				$out['text']        = sprintf(
					/* translators: 1: section name, 2: publication */
					__( '%1$s coverage from %2$s', 'infy-news-os-core' ),
					$term->name,
					$pub
				);
				$out['utm_content'] = $term->taxonomy . '-' . $term->term_id;
				$out['type']        = is_category() ? 'section' : 'topic';
			}
		}
		return $out;
	}

	if ( is_author() ) {
		$author_id          = get_queried_object_id();
		$name               = get_the_author_meta( 'display_name', $author_id );
		$out['url']         = get_author_posts_url( $author_id );
		$out['title']       = $name;
		$out['text']        = sprintf(
			/* translators: 1: author name, 2: publication */
			__( 'Stories by %1$s at %2$s', 'infy-news-os-core' ),
			$name,
			$pub
		);
		$out['utm_content'] = 'author-' . $author_id;
		$out['type']        = 'author';
		return $out;
	}

	if ( is_search() ) {
		$query              = get_search_query();
		$out['url']         = get_search_link( $query );
		$out['title']       = sprintf( __( 'Search: %s', 'infy-news-os-core' ), $query );
		$out['text']        = $out['title'];
		$out['utm_content'] = 'search';
		$out['type']        = 'search';
		return $out;
	}

	if ( is_post_type_archive() ) {
		$ptype = get_query_var( 'post_type' );
		$ptype = is_array( $ptype ) ? reset( $ptype ) : $ptype;
		$link  = get_post_type_archive_link( $ptype );
		$label = post_type_archive_title( '', false );
		if ( $link ) {
			$out['url']   = $link;
			$out['title'] = $label ? $label : (string) $ptype;
			$out['text']  = $out['title'];
		}
		$out['utm_content'] = 'cpt-' . sanitize_key( (string) $ptype );
		$out['type']        = 'archive';
		return $out;
	}

	if ( is_day() ) {
		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );
		$day   = (int) get_query_var( 'day' );
		$out['url']         = get_day_link( $year, $month, $day );
		$out['title']       = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, $month, $day, $year ) );
		$out['text']        = $out['title'];
		$out['utm_content'] = 'date-day';
		$out['type']        = 'date';
		return $out;
	}

	if ( is_month() ) {
		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );
		$out['url']         = get_month_link( $year, $month );
		$out['title']       = date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) );
		$out['text']        = $out['title'];
		$out['utm_content'] = 'date-month';
		$out['type']        = 'date';
		return $out;
	}

	if ( is_year() ) {
		$year = (int) get_query_var( 'year' );
		$out['url']         = get_year_link( $year );
		$out['title']       = (string) $year;
		$out['text']        = $out['title'];
		$out['utm_content'] = 'date-year';
		$out['type']        = 'date';
		return $out;
	}

	if ( is_home() ) {
		$page_id            = (int) get_option( 'page_for_posts' );
		$out['url']         = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		$out['title']       = __( 'Latest stories', 'infy-news-os-core' );
		$out['text']        = sprintf(
			/* translators: %s: publication */
			__( 'Latest stories from %s', 'infy-news-os-core' ),
			$pub
		);
		$out['utm_content'] = 'latest';
		$out['type']        = 'latest';
	}

	return $out;
}

/**
 * Print Google Preferred Sources button.
 */
function inos_the_preferred_source_button() {
	if ( class_exists( 'INOS_Tracking' ) ) {
		INOS_Tracking::render_preferred_source_button();
	}
}

/**
 * Publisher logo URL.
 *
 * @return string
 */
function inos_publisher_logo_url() {
	$logo_id = absint( inos_get_option( 'logo_id', 0 ) );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	$custom = get_theme_mod( 'custom_logo' );
	if ( $custom ) {
		$url = wp_get_attachment_image_url( $custom, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Whether this request is an AMP document (official AMP plugin).
 *
 * @return bool
 */
function inos_is_amp() {
	return class_exists( 'INOS_AMP' ) && INOS_AMP::is_request();
}

/**
 * Whether the homepage Web Stories rail should print.
 *
 * @return bool
 */
function inos_show_home_web_stories() {
	return class_exists( 'INOS_Web_Stories' ) && INOS_Web_Stories::show_on_home();
}

/**
 * Enabled homepage builder modules.
 *
 * @return array<int, array<string, mixed>>
 */
function inos_home_modules() {
	return class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::enabled() : array();
}

/**
 * Enabled article sidebar builder modules.
 *
 * @return array<int, array<string, mixed>>
 */
function inos_article_sidebar_modules() {
	return class_exists( 'INOS_Article_Sidebar' ) ? INOS_Article_Sidebar::enabled() : array();
}

/**
 * Enabled mobile drawer builder modules.
 *
 * @return array<int, array<string, mixed>>
 */
function inos_drawer_modules() {
	return class_exists( 'INOS_Drawer' ) ? INOS_Drawer::enabled() : array();
}

/**
 * Allowed masthead identity modes.
 *
 * @return string[]
 */
function inos_masthead_identity_choices() {
	return array(
		'logo'               => __( 'Logo only', 'infy-news-os-core' ),
		'title'              => __( 'Title only', 'infy-news-os-core' ),
		'title_tagline'      => __( 'Title and description', 'infy-news-os-core' ),
		'logo_title'         => __( 'Logo and title', 'infy-news-os-core' ),
		'logo_tagline'       => __( 'Logo and description', 'infy-news-os-core' ),
		'logo_title_tagline' => __( 'Logo, title, and description', 'infy-news-os-core' ),
		'tagline'            => __( 'Description only', 'infy-news-os-core' ),
	);
}

/**
 * Current masthead identity mode (logo / title / tagline combination).
 *
 * @return string
 */
function inos_masthead_identity() {
	$mode = function_exists( 'inos_get_option' ) ? (string) inos_get_option( 'masthead_identity', 'logo' ) : 'logo';
	if ( class_exists( 'INOS_Settings' ) ) {
		return INOS_Settings::sanitize_masthead_identity( $mode );
	}
	return isset( inos_masthead_identity_choices()[ $mode ] ) ? $mode : 'logo';
}

/**
 * Whether a custom logo file is available.
 *
 * @return bool
 */
function inos_masthead_has_logo() {
	if ( has_custom_logo() ) {
		return true;
	}
	$id = function_exists( 'inos_get_option' ) ? absint( inos_get_option( 'logo_id', 0 ) ) : 0;
	return $id > 0;
}

/**
 * Effective masthead identity when a logo file is missing.
 *
 * @return string
 */
function inos_masthead_identity_effective() {
	$mode = inos_masthead_identity();
	if ( inos_masthead_has_logo() ) {
		return $mode;
	}
	if ( 'logo' === $mode || 'logo_title' === $mode ) {
		return 'title';
	}
	if ( 'logo_tagline' === $mode || 'logo_title_tagline' === $mode ) {
		return 'title_tagline';
	}
	return $mode;
}
