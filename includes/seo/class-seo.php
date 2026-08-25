<?php
/**
 * Titles, robots, canonical, Open Graph — articles and archives.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end SEO head tags plus category/tag/type archive fields.
 */
class INOS_SEO {

	/**
	 * Taxonomies that get per-term SEO fields.
	 *
	 * @return string[]
	 */
	public static function term_taxonomies() {
		return array( 'category', 'post_tag', 'inos_article_type' );
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		foreach ( self::term_taxonomies() as $tax ) {
			add_action( $tax . '_add_form_fields', array( __CLASS__, 'term_add_fields' ) );
			add_action( $tax . '_edit_form_fields', array( __CLASS__, 'term_edit_fields' ) );
		}
		add_action( 'created_term', array( __CLASS__, 'save_term' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'save_term' ), 10, 3 );

		if ( is_admin() ) {
			return;
		}
		add_action( 'wp', array( __CLASS__, 'boot' ) );
	}

	/**
	 * Late hooks after query is known.
	 */
	public static function boot() {
		if ( ! inos_get_option( 'enable_seo', 1 ) ) {
			return;
		}

		add_filter( 'pre_get_document_title', array( __CLASS__, 'document_title' ), 20 );
		add_filter( 'get_the_archive_title_prefix', array( __CLASS__, 'archive_title_prefix' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_filter( 'get_canonical_url', array( __CLASS__, 'filter_canonical' ), 10, 2 );
		remove_action( 'wp_head', 'rel_canonical' );
	}

	/**
	 * Drop “Category:” / “Tag:” prefixes on archive H1s.
	 *
	 * @return string
	 */
	public static function archive_title_prefix() {
		return '';
	}

	/**
	 * Override document title.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	public static function document_title( $title ) {
		$custom = self::seo_title_raw();
		if ( $custom ) {
			return self::paged_title( $custom );
		}

		$sep  = self::sep();
		$site = self::site_name();

		if ( is_front_page() ) {
			$home = (string) inos_get_option( 'homepage_title', '' );
			return $home ? $home : $site;
		}

		if ( is_home() ) {
			$page_id = (int) get_option( 'page_for_posts' );
			$label   = $page_id ? wp_strip_all_tags( get_the_title( $page_id ) ) : ( function_exists( 'inos_label' ) ? inos_label( 'latest_stories' ) : __( 'Latest stories', 'infy-news-os-core' ) );
			return self::paged_title( $label . $sep . $site );
		}

		if ( is_author() ) {
			$profile = function_exists( 'inos_get_author_profile' ) ? inos_get_author_profile( get_queried_object_id() ) : array();
			$name    = ! empty( $profile['name'] ) ? $profile['name'] : get_the_author_meta( 'display_name', get_queried_object_id() );
			$job     = ! empty( $profile['job_title'] ) ? $profile['job_title'] : '';
			$built   = $job ? $name . $sep . $job . $sep . $site : $name . $sep . $site;
			return self::paged_title( $built );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				return self::paged_title( $term->name . $sep . $site );
			}
		}

		if ( is_search() ) {
			$q = get_search_query();
			$label = function_exists( 'inos_label' ) ? inos_label( 'search_heading', array( $q ) ) : sprintf( __( 'Search: %s', 'infy-news-os-core' ), $q );
			return $label . $sep . $site;
		}

		if ( is_post_type_archive() ) {
			$name = post_type_archive_title( '', false );
			return self::paged_title( $name . $sep . $site );
		}

		if ( is_year() || is_month() || is_day() ) {
			$name = get_the_archive_title();
			return self::paged_title( wp_strip_all_tags( $name ) . $sep . $site );
		}

		if ( is_404() ) {
			$label = function_exists( 'inos_label' ) ? inos_label( 'page_not_found' ) : __( 'Page not found', 'infy-news-os-core' );
			return $label . $sep . $site;
		}

		return $title;
	}

	/**
	 * Per-object SEO title override.
	 *
	 * @return string
	 */
	private static function seo_title_raw() {
		if ( is_singular( array( 'post', 'inos_live_blog', 'page' ) ) ) {
			$t = (string) get_post_meta( get_the_ID(), '_inos_seo_title', true );
			if ( $t ) {
				return $t;
			}
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$t = (string) get_term_meta( $term->term_id, '_inos_seo_title', true );
				if ( $t ) {
					return $t;
				}
			}
		}

		if ( is_author() ) {
			$t = (string) get_user_meta( get_queried_object_id(), 'inos_seo_title', true );
			if ( $t ) {
				return $t;
			}
		}

		if ( is_home() && ! is_front_page() ) {
			$page_id = (int) get_option( 'page_for_posts' );
			if ( $page_id ) {
				$t = (string) get_post_meta( $page_id, '_inos_seo_title', true );
				if ( $t ) {
					return $t;
				}
			}
		}

		return '';
	}

	/**
	 * Meta description.
	 *
	 * @return string
	 */
	public static function description() {
		if ( is_front_page() ) {
			$home = trim( (string) inos_get_option( 'homepage_description', '' ) );
			if ( $home ) {
				return $home;
			}
			$intro = (string) inos_get_option( 'home_intro', '' );
			if ( $intro ) {
				return wp_strip_all_tags( $intro );
			}
			$tagline = get_bloginfo( 'description', 'display' );
			if ( $tagline ) {
				return wp_strip_all_tags( $tagline );
			}
			if ( is_singular( 'page' ) ) {
				$page_desc = self::singular_description( get_the_ID() );
				if ( $page_desc && strlen( $page_desc ) >= 50 ) {
					return $page_desc;
				}
			}
			$title = wp_strip_all_tags( wp_get_document_title() );
			$site  = self::site_name();
			if ( $title && 0 !== strcasecmp( $title, $site ) ) {
				return $title;
			}
			return function_exists( 'inos_label' )
				? inos_label( 'home_meta_desc', array( $site ) )
				: sprintf(
					/* translators: %s: publication name */
					__( 'The latest news from %s.', 'infy-news-os-core' ),
					$site
				);
		}

		if ( is_singular( array( 'post', 'inos_live_blog', 'page' ) ) ) {
			$singular = self::singular_description( get_the_ID() );
			if ( $singular ) {
				return $singular;
			}
		}

		if ( is_home() ) {
			$page_id = (int) get_option( 'page_for_posts' );
			if ( $page_id ) {
				$seo = (string) get_post_meta( $page_id, '_inos_seo_description', true );
				if ( $seo ) {
					return $seo;
				}
				$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $page_id ) );
				if ( $excerpt ) {
					return $excerpt;
				}
			}
			return function_exists( 'inos_label' )
				? inos_label( 'blog_archive_desc', array( self::site_name() ) )
				: get_bloginfo( 'description' );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$custom = (string) get_term_meta( $term->term_id, '_inos_seo_description', true );
				if ( $custom ) {
					return $custom;
				}
				if ( ! empty( $term->description ) ) {
					return wp_strip_all_tags( $term->description );
				}
				$key = is_tag() ? 'stories_tagged' : ( is_tax( 'inos_article_type' ) ? 'type_archive_desc' : 'latest_in' );
				return function_exists( 'inos_label' )
					? inos_label( $key, array( $term->name, self::site_name() ) )
					: $term->name;
			}
		}

		if ( is_author() ) {
			$custom = (string) get_user_meta( get_queried_object_id(), 'inos_seo_description', true );
			if ( $custom ) {
				return $custom;
			}
			$bio = get_the_author_meta( 'description', get_queried_object_id() );
			if ( $bio ) {
				return wp_strip_all_tags( $bio );
			}
			$name = get_the_author_meta( 'display_name', get_queried_object_id() );
			$job  = (string) get_user_meta( get_queried_object_id(), 'inos_job_title', true );
			if ( $job && function_exists( 'inos_label' ) ) {
				return inos_label( 'author_archive_desc_job', array( $name, $job, self::site_name() ) );
			}
			return function_exists( 'inos_label' )
				? inos_label( 'author_archive_desc', array( $name, self::site_name() ) )
				: $name;
		}

		if ( is_search() ) {
			return function_exists( 'inos_label' )
				? inos_label( 'search_archive_desc', array( get_search_query() ) )
				: get_search_query();
		}

		if ( is_year() || is_month() || is_day() ) {
			return function_exists( 'inos_label' )
				? inos_label( 'date_archive_desc', array( wp_strip_all_tags( get_the_archive_title() ) ) )
				: wp_strip_all_tags( get_the_archive_title() );
		}

		if ( is_post_type_archive() ) {
			$obj = get_queried_object();
			if ( $obj && ! empty( $obj->description ) ) {
				return wp_strip_all_tags( $obj->description );
			}
			$name = post_type_archive_title( '', false );
			return function_exists( 'inos_label' )
				? inos_label( 'type_archive_desc', array( $name, self::site_name() ) )
				: $name;
		}

		if ( is_404() ) {
			return function_exists( 'inos_label' ) ? inos_label( 'page_not_found_text' ) : '';
		}

		return get_bloginfo( 'description' );
	}

	/**
	 * Excerpt / SEO field / first paragraph for a single post or page.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function singular_description( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return '';
		}

		$seo = (string) get_post_meta( $post_id, '_inos_seo_description', true );
		if ( $seo ) {
			return $seo;
		}
		$dek = function_exists( 'inos_get_dek' ) ? inos_get_dek( $post_id ) : '';
		if ( $dek ) {
			return wp_strip_all_tags( $dek );
		}
		$excerpt = wp_strip_all_tags( (string) get_the_excerpt( $post_id ) );
		if ( $excerpt ) {
			return $excerpt;
		}
		$content = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		$content = trim( preg_replace( '/\s+/u', ' ', $content ) );
		if ( $content ) {
			return $content;
		}
		return wp_strip_all_tags( get_the_title( $post_id ) );
	}

	/**
	 * Plain meta description, about 160 characters.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private static function meta_text( $text ) {
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
		if ( '' === $text ) {
			return '';
		}
		$len = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
		if ( $len <= 160 ) {
			return $text;
		}
		$cut = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 157 ) : substr( $text, 0, 157 );
		return rtrim( $cut ) . '...';
	}

	/**
	 * Canonical URL.
	 *
	 * @return string
	 */
	public static function canonical() {
		if ( is_404() ) {
			return '';
		}

		if ( is_singular() ) {
			$override = (string) get_post_meta( get_the_ID(), '_inos_canonical', true );
			if ( $override ) {
				return $override;
			}
			$core = wp_get_canonical_url( get_the_ID() );
			return $core ? $core : get_permalink();
		}

		$base = '';

		if ( is_front_page() ) {
			$base = home_url( '/' );
		} elseif ( is_home() ) {
			$page_id = (int) get_option( 'page_for_posts' );
			$base    = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$link = get_term_link( $term );
				$base = is_wp_error( $link ) ? '' : $link;
			}
		} elseif ( is_author() ) {
			$base = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_search() ) {
			$base = get_search_link();
		} elseif ( is_post_type_archive() ) {
			$link = get_post_type_archive_link( get_query_var( 'post_type' ) );
			$base = $link ? $link : '';
		} elseif ( is_day() ) {
			$base = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		} elseif ( is_month() ) {
			$base = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		} elseif ( is_year() ) {
			$base = get_year_link( get_query_var( 'year' ) );
		}

		if ( ! $base ) {
			$link = (string) wp_get_canonical_url();
			return $link ? $link : home_url( '/' );
		}

		return self::paged_url( $base );
	}

	/**
	 * Filter core canonical.
	 *
	 * @param string  $canonical Canonical.
	 * @param WP_Post $post      Post.
	 * @return string
	 */
	public static function filter_canonical( $canonical, $post ) {
		$override = (string) get_post_meta( $post->ID, '_inos_canonical', true );
		return $override ? $override : $canonical;
	}

	/**
	 * Robots directives.
	 *
	 * @param array<string, mixed> $robots Robots.
	 * @return array<string, mixed>
	 */
	public static function robots( $robots ) {
		if ( inos_get_option( 'max_image_preview_large', 1 ) ) {
			$robots['max-image-preview'] = 'large';
		}

		if ( is_singular( array( 'post', 'inos_live_blog' ) ) ) {
			$override = (string) get_post_meta( get_the_ID(), '_inos_robots', true );
			if ( $override ) {
				self::apply_robots_string( $robots, $override );
			}

			if ( inos_get_option( 'enable_googlebot_news', 1 ) && ! empty( $robots['noindex'] ) ) {
				$robots['googlebot-news'] = 'noindex';
			}

			return $robots;
		}

		$noindex = false;

		if ( is_404() ) {
			$noindex = true;
		} elseif ( is_search() && inos_get_option( 'noindex_search', 1 ) ) {
			$noindex = true;
		} elseif ( ( is_year() || is_month() || is_day() ) && inos_get_option( 'noindex_date_archives', 1 ) ) {
			$noindex = true;
		} elseif ( is_category() && ! inos_get_option( 'index_category_archives', 1 ) ) {
			$noindex = true;
		} elseif ( is_tag() && ! inos_get_option( 'index_tag_archives', 1 ) ) {
			$noindex = true;
		} elseif ( is_author() && ! inos_get_option( 'index_author_archives', 1 ) ) {
			$noindex = true;
		} elseif ( is_tax( 'inos_article_type' ) && ! inos_get_option( 'index_type_archives', 1 ) ) {
			$noindex = true;
		}

		if ( ! $noindex && ( is_category() || is_tag() || is_tax() ) ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$override = (string) get_term_meta( $term->term_id, '_inos_robots', true );
				if ( $override && false !== strpos( $override, 'noindex' ) ) {
					$noindex = true;
				}
			}
		}

		if ( ! $noindex && is_author() ) {
			$override = (string) get_user_meta( get_queried_object_id(), 'inos_robots', true );
			if ( $override && false !== strpos( $override, 'noindex' ) ) {
				$noindex = true;
			}
		}

		if ( ! $noindex && inos_get_option( 'noindex_empty_archives', 1 ) && self::is_empty_listing() ) {
			$noindex = true;
		}

		if ( $noindex ) {
			$robots['noindex'] = true;
			unset( $robots['index'] );
		}

		return $robots;
	}

	/**
	 * Extra head tags.
	 */
	public static function head() {
		$desc = self::meta_text( self::description() );
		if ( ! $desc ) {
			$desc = self::meta_text( self::site_name() );
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
		}

		$canonical = self::canonical();
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		}

		self::rel_prev_next();

		if ( inos_get_option( 'og_enabled', 1 ) ) {
			self::open_graph();
		}

		if ( is_singular( array( 'post', 'inos_live_blog' ) ) && inos_get_option( 'enable_googlebot_news', 1 ) ) {
			$override = (string) get_post_meta( get_the_ID(), '_inos_robots', true );
			$news     = ( $override && false !== strpos( $override, 'noindex' ) ) ? 'noindex' : 'index,follow';
			echo '<meta name="googlebot-news" content="' . esc_attr( $news ) . '" />' . "\n";
		}

		if ( inos_get_option( 'enable_news_keywords_meta', 1 ) && function_exists( 'inos_get_page_keywords' ) ) {
			$keywords = inos_get_page_keywords();
			if ( $keywords ) {
				echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />' . "\n";
				if ( is_singular( array( 'post', 'inos_live_blog' ) ) ) {
					echo '<meta name="news_keywords" content="' . esc_attr( $keywords ) . '" />' . "\n";
				}
			}
		}
	}

	/**
	 * Open Graph / Twitter.
	 */
	private static function open_graph() {
		$type = 'website';
		if ( is_singular( 'post' ) || is_singular( 'inos_live_blog' ) ) {
			$type = 'article';
		} elseif ( is_author() ) {
			$type = 'profile';
		}

		$title   = wp_get_document_title();
		$desc    = self::description();
		$url     = self::canonical();
		$image   = '';
		$og_meta = null;

		if ( is_singular() ) {
			$og_meta = INOS_Images::og_image_meta( get_the_ID() );
			$image   = $og_meta ? $og_meta['url'] : '';
		} elseif ( is_author() ) {
			$image = get_avatar_url( get_queried_object_id(), array( 'size' => 512 ) );
		} else {
			$og_meta = self::archive_og_image();
			$image   = $og_meta ? $og_meta['url'] : '';
		}
		if ( ! $image ) {
			$image = inos_publisher_logo_url();
		}

		echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_locale() ) ) . '" />' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
		if ( $desc ) {
			echo '<meta property="og:description" content="' . esc_attr( self::meta_text( $desc ) ) . '" />' . "\n";
		}
		if ( $url ) {
			echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		}
		echo '<meta property="og:site_name" content="' . esc_attr( self::site_name() ) . '" />' . "\n";
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
			echo '<meta property="og:image:secure_url" content="' . esc_url( $image ) . '" />' . "\n";
			if ( $og_meta && ! empty( $og_meta['type'] ) ) {
				echo '<meta property="og:image:type" content="' . esc_attr( $og_meta['type'] ) . '" />' . "\n";
			}
			if ( $og_meta && ! empty( $og_meta['width'] ) ) {
				echo '<meta property="og:image:width" content="' . esc_attr( (string) $og_meta['width'] ) . '" />' . "\n";
			}
			if ( $og_meta && ! empty( $og_meta['height'] ) ) {
				echo '<meta property="og:image:height" content="' . esc_attr( (string) $og_meta['height'] ) . '" />' . "\n";
			}
			if ( $og_meta && ! empty( $og_meta['alt'] ) ) {
				echo '<meta property="og:image:alt" content="' . esc_attr( $og_meta['alt'] ) . '" />' . "\n";
			}
		}

		$modified = self::listing_modified();
		if ( $modified && 'article' !== $type ) {
			echo '<meta property="og:updated_time" content="' . esc_attr( $modified ) . '" />' . "\n";
		}

		if ( 'article' === $type ) {
			$post_id = get_the_ID();
			echo '<meta property="article:published_time" content="' . esc_attr( get_post_time( DATE_W3C, true, $post_id ) ) . '" />' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( get_post_modified_time( DATE_W3C, true, $post_id ) ) . '" />' . "\n";
			$section = inos_get_primary_section( $post_id );
			if ( $section ) {
				echo '<meta property="article:section" content="' . esc_attr( $section->name ) . '" />' . "\n";
			}
			$author_id = (int) get_post_field( 'post_author', $post_id );
			echo '<meta property="article:author" content="' . esc_url( get_author_posts_url( $author_id ) ) . '" />' . "\n";
			$twitter = (string) get_user_meta( $author_id, 'inos_twitter', true );
			if ( $twitter ) {
				echo '<meta name="twitter:creator" content="@' . esc_attr( ltrim( $twitter, '@' ) ) . '" />' . "\n";
			}
			$tags = get_the_tags( $post_id );
			if ( $tags ) {
				foreach ( $tags as $tag ) {
					echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '" />' . "\n";
				}
			}
		}

		if ( 'profile' === $type ) {
			$profile = function_exists( 'inos_get_author_profile' ) ? inos_get_author_profile( get_queried_object_id() ) : array();
			if ( ! empty( $profile['given_name'] ) ) {
				echo '<meta property="profile:first_name" content="' . esc_attr( $profile['given_name'] ) . '" />' . "\n";
			}
			if ( ! empty( $profile['family_name'] ) ) {
				echo '<meta property="profile:last_name" content="' . esc_attr( $profile['family_name'] ) . '" />' . "\n";
			}
			if ( ! empty( $profile['name'] ) ) {
				echo '<meta property="profile:username" content="' . esc_attr( $profile['name'] ) . '" />' . "\n";
			}
		}

		$card = (string) inos_get_option( 'twitter_card', 'summary_large_image' );
		if ( ! is_singular() && ! $og_meta ) {
			$card = 'summary';
		}
		echo '<meta name="twitter:card" content="' . esc_attr( $card ) . '" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		if ( $desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( self::meta_text( $desc ) ) . '" />' . "\n";
		}
		if ( $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
			if ( $og_meta && ! empty( $og_meta['alt'] ) ) {
				echo '<meta name="twitter:image:alt" content="' . esc_attr( $og_meta['alt'] ) . '" />' . "\n";
			}
		}
	}

	/**
	 * rel prev / next for paginated listings.
	 */
	private static function rel_prev_next() {
		if ( is_singular() || is_404() ) {
			return;
		}
		global $wp_query;
		$max   = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		if ( $max < 2 ) {
			return;
		}
		if ( $paged > 1 ) {
			echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '" />' . "\n";
		}
		if ( $paged < $max ) {
			echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '" />' . "\n";
		}
	}

	/**
	 * Add-term SEO fields.
	 *
	 * @param string $taxonomy Taxonomy.
	 */
	public static function term_add_fields( $taxonomy ) {
		unset( $taxonomy );
		?>
		<div class="form-field">
			<label for="inos_seo_title"><?php esc_html_e( 'SEO title', 'infy-news-os-core' ); ?></label>
			<input type="text" name="inos_seo_title" id="inos_seo_title" value="" />
			<p><?php esc_html_e( 'Overrides the archive document title. Leave empty to use the term name plus publication name.', 'infy-news-os-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="inos_seo_description"><?php esc_html_e( 'SEO / meta description', 'infy-news-os-core' ); ?></label>
			<textarea name="inos_seo_description" id="inos_seo_description" rows="3"></textarea>
			<p><?php esc_html_e( 'Used in search snippets and Open Graph. Leave empty to use the term description.', 'infy-news-os-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="inos_robots"><?php esc_html_e( 'Robots', 'infy-news-os-core' ); ?></label>
			<select name="inos_robots" id="inos_robots">
				<option value=""><?php esc_html_e( 'Default (index, follow)', 'infy-news-os-core' ); ?></option>
				<option value="noindex,follow"><?php esc_html_e( 'noindex, follow', 'infy-news-os-core' ); ?></option>
			</select>
		</div>
		<?php
	}

	/**
	 * Edit-term SEO fields.
	 *
	 * @param WP_Term $term Term.
	 */
	public static function term_edit_fields( $term ) {
		$title = (string) get_term_meta( $term->term_id, '_inos_seo_title', true );
		$desc  = (string) get_term_meta( $term->term_id, '_inos_seo_description', true );
		$robots = (string) get_term_meta( $term->term_id, '_inos_robots', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="inos_seo_title"><?php esc_html_e( 'SEO title', 'infy-news-os-core' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" name="inos_seo_title" id="inos_seo_title" value="<?php echo esc_attr( $title ); ?>" />
				<p class="description"><?php esc_html_e( 'Overrides the archive document title. Leave empty to use the term name plus publication name.', 'infy-news-os-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="inos_seo_description"><?php esc_html_e( 'SEO / meta description', 'infy-news-os-core' ); ?></label></th>
			<td>
				<textarea class="large-text" rows="3" name="inos_seo_description" id="inos_seo_description"><?php echo esc_textarea( $desc ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Used in search snippets and Open Graph. Leave empty to use the term description.', 'infy-news-os-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="inos_robots"><?php esc_html_e( 'Robots', 'infy-news-os-core' ); ?></label></th>
			<td>
				<select name="inos_robots" id="inos_robots">
					<option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default (index, follow)', 'infy-news-os-core' ); ?></option>
					<option value="noindex,follow" <?php selected( $robots, 'noindex,follow' ); ?>><?php esc_html_e( 'noindex, follow', 'infy-news-os-core' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Persist term SEO.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 */
	public static function save_term( $term_id, $tt_id, $taxonomy ) {
		unset( $tt_id );
		if ( ! in_array( $taxonomy, self::term_taxonomies(), true ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		$title = isset( $_POST['inos_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['inos_seo_title'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$desc  = isset( $_POST['inos_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['inos_seo_description'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$robots = isset( $_POST['inos_robots'] ) ? sanitize_text_field( wp_unslash( $_POST['inos_robots'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! in_array( $robots, array( '', 'noindex,follow' ), true ) ) {
			$robots = '';
		}

		update_term_meta( $term_id, '_inos_seo_title', $title );
		update_term_meta( $term_id, '_inos_seo_description', $desc );
		update_term_meta( $term_id, '_inos_robots', $robots );
	}

	/**
	 * Title separator wrapped in spaces.
	 *
	 * @return string
	 */
	private static function sep() {
		return ' ' . trim( (string) inos_get_option( 'title_separator', '|' ) ) . ' ';
	}

	/**
	 * Publication name.
	 *
	 * @return string
	 */
	private static function site_name() {
		return (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) );
	}

	/**
	 * Append “Page N” when paged.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	private static function paged_title( $title ) {
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		if ( $paged < 2 ) {
			return $title;
		}
		$label = function_exists( 'inos_label' ) ? inos_label( 'page_n', array( (string) $paged ) ) : sprintf( __( 'Page %s', 'infy-news-os-core' ), (string) $paged );
		return $title . self::sep() . $label;
	}

	/**
	 * Add pagination to a listing URL.
	 *
	 * @param string $base Base URL.
	 * @param int    $page Page number (0 = current).
	 * @return string
	 */
	private static function paged_url( $base, $page = 0 ) {
		$page = $page ? absint( $page ) : max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		if ( $page < 2 ) {
			return $base;
		}
		global $wp_rewrite;
		if ( $wp_rewrite instanceof WP_Rewrite && $wp_rewrite->using_permalinks() && false === strpos( $base, '?' ) ) {
			return user_trailingslashit( trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/' . $page, 'paged' ) );
		}
		return add_query_arg( 'paged', $page, $base );
	}

	/**
	 * Apply a robots override string.
	 *
	 * @param array<string, mixed> $robots Robots.
	 * @param string               $override Override.
	 */
	private static function apply_robots_string( &$robots, $override ) {
		if ( false !== strpos( $override, 'noindex' ) ) {
			$robots['noindex'] = true;
			unset( $robots['index'] );
		}
		if ( false !== strpos( $override, 'nofollow' ) ) {
			$robots['nofollow'] = true;
			unset( $robots['follow'] );
		}
	}

	/**
	 * Empty listing (no posts in the main query).
	 *
	 * @return bool
	 */
	private static function is_empty_listing() {
		if ( is_singular() || is_front_page() || is_404() ) {
			return false;
		}
		global $wp_query;
		return empty( $wp_query->posts );
	}

	/**
	 * First story image on the current listing, for OG.
	 *
	 * @return array<string, mixed>|null
	 */
	private static function archive_og_image() {
		global $wp_query;
		if ( empty( $wp_query->posts ) || ! class_exists( 'INOS_Images' ) ) {
			return null;
		}
		foreach ( $wp_query->posts as $post ) {
			$meta = INOS_Images::og_image_meta( $post->ID );
			if ( $meta ) {
				return $meta;
			}
		}
		return null;
	}

	/**
	 * Latest modified time among listing posts.
	 *
	 * @return string
	 */
	private static function listing_modified() {
		global $wp_query;
		if ( empty( $wp_query->posts ) ) {
			return '';
		}
		$latest = 0;
		foreach ( $wp_query->posts as $post ) {
			$ts = get_post_modified_time( 'U', true, $post );
			if ( $ts > $latest ) {
				$latest = $ts;
			}
		}
		return $latest ? gmdate( DATE_W3C, $latest ) : '';
	}
}
