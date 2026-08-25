<?php
/**
 * Sitemap index, Google News sitemap, and Search / Discover URL sets.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * XML sitemaps for Google News, Discover, Top Stories, and Search.
 */
class INOS_Sitemaps {

	const POSTS_PER_FILE = 1000;
	const NEWS_HOURS     = 48;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrites' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'render' ), 0 );
		add_filter( 'robots_txt', array( __CLASS__, 'robots_txt' ), 10, 2 );
		add_action( 'transition_post_status', array( __CLASS__, 'on_status_change' ), 10, 3 );
		add_filter( 'wp_sitemaps_enabled', array( __CLASS__, 'disable_core' ) );

		if ( get_option( 'inos_sitemap_rewrite', '' ) !== '1.4.2' ) {
			update_option( 'inos_flush_rewrites', '1' );
			update_option( 'inos_sitemap_rewrite', '1.4.2' );
		}
	}

	/**
	 * Prefer Infy sitemaps over wp-sitemap.xml so Search Console sees one index.
	 *
	 * @param bool $enabled Core enabled.
	 * @return bool
	 */
	public static function disable_core( $enabled ) {
		return inos_get_option( 'disable_core_sitemaps', 1 ) ? false : $enabled;
	}

	/**
	 * Post types in the Search sitemap (not the News sitemap).
	 *
	 * @return string[]
	 */
	private static function content_post_types() {
		$types = array( 'post', 'inos_live_blog' );
		if ( post_type_exists( 'web-story' ) ) {
			$types[] = 'web-story';
		}
		return $types;
	}

	/**
	 * Rewrite rules.
	 */
	public static function register_rewrites() {
		if ( ! INOS_Activator::rewrites_ready() ) {
			return;
		}
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?inos_sitemap=index', 'top' );
		add_rewrite_rule( '^sitemap-news\.xml$', 'index.php?inos_sitemap=news', 'top' );
		add_rewrite_rule( '^news-sitemap\.xml$', 'index.php?inos_sitemap=news', 'top' );
		add_rewrite_rule( '^inos-sitemap\.xml$', 'index.php?inos_sitemap=index', 'top' );
		add_rewrite_rule( '^inos-sitemap-core\.xml$', 'index.php?inos_sitemap=core', 'top' );
		add_rewrite_rule( '^inos-sitemap-posts(?:-([0-9]+))?\.xml$', 'index.php?inos_sitemap=posts&inos_sitemap_page=$matches[1]', 'top' );
		add_rewrite_rule( '^inos-sitemap-pages\.xml$', 'index.php?inos_sitemap=pages', 'top' );
		add_rewrite_rule( '^inos-sitemap-authors\.xml$', 'index.php?inos_sitemap=authors', 'top' );
		add_rewrite_rule( '^inos-sitemap-sections\.xml$', 'index.php?inos_sitemap=sections', 'top' );
		add_rewrite_rule( '^inos-sitemap-tags\.xml$', 'index.php?inos_sitemap=tags', 'top' );
		add_rewrite_rule( '^inos-sitemap-images\.xml$', 'index.php?inos_sitemap=images', 'top' );
	}

	/**
	 * Query vars.
	 *
	 * @param array<int, string> $vars Vars.
	 * @return array<int, string>
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'inos_sitemap';
		$vars[] = 'inos_sitemap_page';
		return $vars;
	}

	/**
	 * Output sitemap XML.
	 */
	public static function render() {
		$type = get_query_var( 'inos_sitemap' );
		if ( ! $type ) {
			return;
		}

		$page = absint( get_query_var( 'inos_sitemap_page' ) );
		if ( $page < 1 ) {
			$page = 1;
		}

		$status = 200;
		if ( 'news' === $type && ! inos_get_option( 'enable_news_sitemap', 1 ) ) {
			$status = 404;
		}
		if ( 'images' === $type && ! inos_get_option( 'enable_image_sitemap', 1 ) ) {
			$status = 404;
		}
		if ( 'tags' === $type && ( ! inos_get_option( 'sitemap_include_tags', 1 ) || ! inos_get_option( 'index_tag_archives', 1 ) ) ) {
			$status = 404;
		}
		if ( 'authors' === $type && ( ! inos_get_option( 'sitemap_include_authors', 1 ) || ! inos_get_option( 'index_author_archives', 1 ) ) ) {
			$status = 404;
		}
		if ( 'sections' === $type && ! inos_get_option( 'index_category_archives', 1 ) ) {
			$status = 404;
		}

		status_header( $status );
		nocache_headers();
		header( 'Content-Type: application/xml; charset=UTF-8' );
		header( 'X-Content-Type-Options: nosniff' );
		$ttl = ( 'news' === $type || 'index' === $type ) ? 300 : 3600;
		header( 'Cache-Control: public, max-age=' . $ttl );

		if ( 404 === $status ) {
			echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
			exit;
		}

		$cache_key = 'inos_sm_' . self::cache_version() . '_' . $type . '_' . $page;
		$cached    = get_transient( $cache_key );
		if ( is_string( $cached ) && $cached ) {
			echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		ob_start();
		if ( 'news' === $type ) {
			self::render_news();
		} elseif ( 'index' === $type ) {
			self::render_index();
		} elseif ( 'core' === $type ) {
			self::render_core();
		} elseif ( 'posts' === $type ) {
			self::render_posts( $page );
		} elseif ( 'pages' === $type ) {
			self::render_pages();
		} elseif ( 'authors' === $type ) {
			self::render_authors();
		} elseif ( 'sections' === $type ) {
			self::render_sections();
		} elseif ( 'tags' === $type ) {
			self::render_tags();
		} elseif ( 'images' === $type ) {
			self::render_images();
		}
		$xml = ob_get_clean();
		set_transient( $cache_key, $xml, $ttl );
		echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Google News sitemap — articles from the last 48 hours only.
	 */
	private static function render_news() {
		$after = gmdate( 'Y-m-d H:i:s', time() - ( self::NEWS_HOURS * HOUR_IN_SECONDS ) );
		$query = new WP_Query(
			array(
				'post_type'              => array( 'post', 'inos_live_blog' ),
				'post_status'            => 'publish',
				'posts_per_page'         => 1000,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'has_password'           => false,
				'date_query'             => array(
					array(
						'column'    => 'post_date_gmt',
						'after'     => $after,
						'inclusive' => true,
					),
				),
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);

		$pub  = (string) inos_get_option( 'news_publication_name', get_bloginfo( 'name' ) );
		$lang = self::news_language();

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

		foreach ( $query->posts as $post ) {
			if ( ! self::is_indexable( $post ) ) {
				continue;
			}
			$title = wp_strip_all_tags( get_the_title( $post ) );
			if ( '' === $title ) {
				continue;
			}

			$loc  = self::permalink( $post );
			$date = get_post_time( DATE_W3C, true, $post );
			$mod  = get_post_modified_time( DATE_W3C, true, $post );

			echo '<url>';
			echo '<loc>' . self::esc_xml( $loc ) . '</loc>';
			echo '<lastmod>' . self::esc_xml( $mod ) . '</lastmod>';
			echo '<news:news>';
			echo '<news:publication><news:name>' . self::esc_xml( $pub ) . '</news:name><news:language>' . self::esc_xml( $lang ) . '</news:language></news:publication>';
			echo '<news:publication_date>' . self::esc_xml( $date ) . '</news:publication_date>';
			echo '<news:title>' . self::esc_xml( $title ) . '</news:title>';
			$genres = self::news_genres( $post );
			if ( $genres ) {
				echo '<news:genres>' . self::esc_xml( $genres ) . '</news:genres>';
			}
			$keywords = self::news_keywords( $post );
			if ( $keywords ) {
				echo '<news:keywords>' . self::esc_xml( $keywords ) . '</news:keywords>';
			}
			echo '</news:news>';
			echo INOS_Images::sitemap_lead_image_xml( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</url>\n";
		}

		echo '</urlset>';
	}

	/**
	 * Sitemap index at /sitemap.xml.
	 */
	private static function render_index() {
		$latest = self::latest_modified();
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		if ( inos_get_option( 'enable_news_sitemap', 1 ) ) {
			self::index_row( home_url( '/news-sitemap.xml' ), $latest );
		}

		self::index_row( home_url( '/inos-sitemap-core.xml' ), $latest );

		$pages = max( 1, (int) ceil( self::count_posts() / self::POSTS_PER_FILE ) );
		for ( $i = 1; $i <= $pages; $i++ ) {
			$path = ( 1 === $i ) ? '/inos-sitemap-posts.xml' : '/inos-sitemap-posts-' . $i . '.xml';
			self::index_row( home_url( $path ), $latest );
		}

		self::index_row( home_url( '/inos-sitemap-pages.xml' ), $latest );
		if ( inos_get_option( 'sitemap_include_authors', 1 ) && inos_get_option( 'index_author_archives', 1 ) ) {
			self::index_row( home_url( '/inos-sitemap-authors.xml' ), $latest );
		}
		if ( inos_get_option( 'index_category_archives', 1 ) ) {
			self::index_row( home_url( '/inos-sitemap-sections.xml' ), $latest );
		}
		if ( inos_get_option( 'sitemap_include_tags', 1 ) && inos_get_option( 'index_tag_archives', 1 ) ) {
			self::index_row( home_url( '/inos-sitemap-tags.xml' ), $latest );
		}
		if ( inos_get_option( 'enable_image_sitemap', 1 ) ) {
			self::index_row( home_url( '/inos-sitemap-images.xml' ), $latest );
		}

		echo '</sitemapindex>';
	}

	/**
	 * Homepage and other hub URLs for Search / Discover.
	 */
	private static function render_core() {
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		$home_mod = self::latest_modified();
		self::url_row( home_url( '/' ), $home_mod );

		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$p = get_post( $posts_page );
			if ( $p && 'publish' === $p->post_status && self::is_indexable( $p ) ) {
				self::url_row( get_permalink( $p ), get_post_modified_time( DATE_W3C, true, $p ) );
			}
		}

		echo '</urlset>';
	}

	/**
	 * Articles sitemap (paginated) for Google Search.
	 *
	 * @param int $page Page.
	 */
	private static function render_posts( $page ) {
		$query = new WP_Query(
			array(
				'post_type'              => self::content_post_types(),
				'post_status'            => 'publish',
				'posts_per_page'         => self::POSTS_PER_FILE,
				'paged'                  => max( 1, $page ),
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'ignore_sticky_posts'    => true,
				'has_password'           => false,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
			)
		);
		self::urlset( $query->posts, true );
	}

	/**
	 * Pages sitemap.
	 */
	private static function render_pages() {
		$posts = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1000,
				'has_password'   => false,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);
		$front = (int) get_option( 'page_on_front' );
		$blog  = (int) get_option( 'page_for_posts' );
		$posts = array_filter(
			$posts,
			static function ( $post ) use ( $front, $blog ) {
				return (int) $post->ID !== $front && (int) $post->ID !== $blog;
			}
		);
		self::urlset( $posts, false );
	}

	/**
	 * Generic urlset from posts.
	 *
	 * @param WP_Post[] $posts   Posts.
	 * @param bool      $images  Include image nodes.
	 */
	private static function urlset( $posts, $images = true ) {
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
		foreach ( $posts as $post ) {
			if ( ! self::is_indexable( $post ) ) {
				continue;
			}
			echo '<url>';
			echo '<loc>' . self::esc_xml( self::permalink( $post ) ) . '</loc>';
			echo '<lastmod>' . self::esc_xml( get_post_modified_time( DATE_W3C, true, $post ) ) . '</lastmod>';
			if ( $images ) {
				echo INOS_Images::sitemap_lead_image_xml( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo "</url>\n";
		}
		echo '</urlset>';
	}

	/**
	 * Author archives.
	 */
	private static function render_authors() {
		$users = get_users(
			array(
				'capability'          => 'edit_posts',
				'has_published_posts' => array( 'post' ),
				'orderby'             => 'post_count',
				'order'               => 'DESC',
			)
		);
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		foreach ( $users as $user ) {
			$robots = (string) get_user_meta( $user->ID, 'inos_robots', true );
			if ( $robots && false !== strpos( $robots, 'noindex' ) ) {
				continue;
			}
			$mod = self::latest_modified_for_author( (int) $user->ID );
			self::url_row( get_author_posts_url( $user->ID ), $mod );
		}
		echo '</urlset>';
	}

	/**
	 * Category archives.
	 */
	private static function render_sections() {
		self::term_urlset( 'category' );
	}

	/**
	 * Tag archives (Search).
	 */
	private static function render_tags() {
		self::term_urlset( 'post_tag' );
	}

	/**
	 * Term archive urlset.
	 *
	 * @param string $taxonomy Taxonomy.
	 */
	private static function term_urlset( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => 2000,
			)
		);
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$robots = (string) get_term_meta( $term->term_id, '_inos_robots', true );
				if ( $robots && false !== strpos( $robots, 'noindex' ) ) {
					continue;
				}
				self::url_row( $link, self::latest_modified_for_term( $term ) );
			}
		}
		echo '</urlset>';
	}

	/**
	 * Image sitemap for Google Image Search.
	 */
	private static function render_images() {
		$posts = get_posts(
			array(
				'post_type'              => self::content_post_types(),
				'post_status'            => 'publish',
				'posts_per_page'         => 1000,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'has_password'           => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				),
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
			)
		);

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
		foreach ( $posts as $post ) {
			if ( ! self::is_indexable( $post ) ) {
				continue;
			}
			$img_xml = INOS_Images::sitemap_image_xml( $post->ID );
			if ( ! $img_xml ) {
				continue;
			}
			echo '<url>';
			echo '<loc>' . self::esc_xml( self::permalink( $post ) ) . '</loc>';
			echo '<lastmod>' . self::esc_xml( get_post_modified_time( DATE_W3C, true, $post ) ) . '</lastmod>';
			echo $img_xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</url>\n";
		}
		echo '</urlset>';
	}

	/**
	 * robots.txt sitemaps.
	 *
	 * @param string $output Text.
	 * @param bool   $public Public.
	 * @return string
	 */
	public static function robots_txt( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		$output .= "\nUser-agent: Googlebot\nAllow: /\n";
		$output .= "User-agent: Googlebot-News\nAllow: /\n";
		$output .= "User-agent: Googlebot-Image\nAllow: /\nAllow: /wp-content/uploads/\n";
		$output .= 'Sitemap: ' . esc_url( home_url( '/sitemap.xml' ) ) . "\n";
		if ( inos_get_option( 'enable_news_sitemap', 1 ) ) {
			$output .= 'Sitemap: ' . esc_url( home_url( '/news-sitemap.xml' ) ) . "\n";
		}
		return $output;
	}

	/**
	 * Bust cache and ping crawlers when news is published.
	 *
	 * @param string  $new New status.
	 * @param string  $old Old status.
	 * @param WP_Post $post Post.
	 */
	public static function on_status_change( $new, $old, $post ) {
		if ( ! in_array( $post->post_type, array_merge( self::content_post_types(), array( 'page' ) ), true ) ) {
			return;
		}
		if ( 'publish' !== $new && 'publish' !== $old ) {
			return;
		}

		self::bump_cache();

		if ( 'publish' !== $new || 'publish' === $old ) {
			return;
		}
		if ( 'web-story' === $post->post_type ) {
			return;
		}
		if ( ! inos_get_option( 'enable_news_sitemap', 1 ) ) {
			return;
		}

		$urls = array(
			home_url( '/news-sitemap.xml' ),
			home_url( '/sitemap.xml' ),
		);
		foreach ( $urls as $sitemap ) {
			wp_remote_get(
				'https://www.google.com/ping?sitemap=' . rawurlencode( $sitemap ),
				array(
					'timeout'  => 5,
					'blocking' => false,
				)
			);
		}
	}

	/**
	 * Indexable for Search / News.
	 *
	 * @param WP_Post $post Post.
	 * @return bool
	 */
	public static function is_indexable( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		if ( ! empty( $post->post_password ) ) {
			return false;
		}
		$robots = (string) get_post_meta( $post->ID, '_inos_robots', true );
		if ( $robots && false !== strpos( $robots, 'noindex' ) ) {
			return false;
		}
		$canonical = (string) get_post_meta( $post->ID, '_inos_canonical', true );
		if ( $canonical ) {
			$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
			$can_host  = wp_parse_url( $canonical, PHP_URL_HOST );
			if ( $can_host && $home_host && strtolower( (string) $can_host ) !== strtolower( (string) $home_host ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Canonical permalink for a sitemap loc.
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	private static function permalink( $post ) {
		$override = (string) get_post_meta( $post->ID, '_inos_canonical', true );
		if ( $override ) {
			return $override;
		}
		$link = get_permalink( $post );
		return $link ? $link : '';
	}

	/**
	 * ISO 639 language for Google News.
	 *
	 * @return string
	 */
	private static function news_language() {
		$lang = strtolower( (string) inos_get_option( 'publication_language', 'en' ) );
		$lang = str_replace( '_', '-', $lang );
		if ( preg_match( '/^([a-z]{2})(?:-[a-z]{2})?$/', $lang, $m ) ) {
			return $m[1];
		}
		return 'en';
	}

	/**
	 * Google News genres from article type.
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	private static function news_genres( $post ) {
		if ( 'inos_live_blog' === $post->post_type ) {
			return 'Blog';
		}
		$slug = function_exists( 'inos_get_article_type' ) ? inos_get_article_type( $post->ID ) : '';
		$map  = array(
			'opinion' => 'OpEd, Opinion',
			'live'    => 'Blog',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
	}

	/**
	 * Keywords from section + tags (Google News sitemap protocol).
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	private static function news_keywords( $post ) {
		if ( function_exists( 'inos_get_news_keywords' ) ) {
			return inos_get_news_keywords( $post->ID );
		}
		$bits = array();
		$sec  = function_exists( 'inos_get_primary_section' ) ? inos_get_primary_section( $post->ID ) : null;
		if ( $sec ) {
			$bits[] = $sec->name;
		}
		$tags = get_the_tags( $post->ID );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$bits[] = $tag->name;
				if ( count( $bits ) >= 10 ) {
					break;
				}
			}
		}
		$bits = array_filter( array_map( 'wp_strip_all_tags', $bits ) );
		return implode( ', ', array_slice( $bits, 0, 10 ) );
	}

	/**
	 * Published article count.
	 *
	 * @return int
	 */
	private static function count_posts() {
		$n = 0;
		foreach ( self::content_post_types() as $type ) {
			$counts = wp_count_posts( $type );
			$n     += (int) ( isset( $counts->publish ) ? $counts->publish : 0 );
		}
		return $n;
	}

	/**
	 * Latest GMT modified time across content.
	 *
	 * @return string
	 */
	private static function latest_modified() {
		$query = new WP_Query(
			array(
				'post_type'              => array_merge( self::content_post_types(), array( 'page' ) ),
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( empty( $query->posts[0] ) ) {
			return gmdate( DATE_W3C );
		}
		return get_post_modified_time( DATE_W3C, true, $query->posts[0] );
	}

	/**
	 * Latest modified for an author.
	 *
	 * @param int $user_id User.
	 * @return string
	 */
	private static function latest_modified_for_author( $user_id ) {
		$query = new WP_Query(
			array(
				'author'                 => $user_id,
				'post_type'              => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( empty( $query->posts[0] ) ) {
			return gmdate( DATE_W3C );
		}
		return get_post_modified_time( DATE_W3C, true, $query->posts[0] );
	}

	/**
	 * Latest modified for a term.
	 *
	 * @param WP_Term $term Term.
	 * @return string
	 */
	private static function latest_modified_for_term( $term ) {
		$query = new WP_Query(
			array(
				'post_type'              => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'fields'                 => 'ids',
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $term->taxonomy,
						'field'    => 'term_id',
						'terms'    => (int) $term->term_id,
					),
				),
			)
		);
		if ( empty( $query->posts[0] ) ) {
			return gmdate( DATE_W3C );
		}
		return get_post_modified_time( DATE_W3C, true, $query->posts[0] );
	}

	/**
	 * Sitemap index row.
	 *
	 * @param string $loc     URL.
	 * @param string $lastmod W3C date.
	 */
	private static function index_row( $loc, $lastmod ) {
		echo '<sitemap><loc>' . self::esc_xml( $loc ) . '</loc><lastmod>' . self::esc_xml( $lastmod ) . '</lastmod></sitemap>' . "\n";
	}

	/**
	 * urlset row.
	 *
	 * @param string $loc     URL.
	 * @param string $lastmod W3C date.
	 */
	private static function url_row( $loc, $lastmod ) {
		echo '<url><loc>' . self::esc_xml( $loc ) . '</loc><lastmod>' . self::esc_xml( $lastmod ) . '</lastmod></url>' . "\n";
	}

	/**
	 * Cache version.
	 *
	 * @return int
	 */
	private static function cache_version() {
		return absint( get_option( 'inos_sitemap_cache_v', 1 ) );
	}

	/**
	 * Invalidate sitemap transients.
	 */
	private static function bump_cache() {
		update_option( 'inos_sitemap_cache_v', self::cache_version() + 1, false );
	}

	/**
	 * Escape XML text (and URL locs).
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function esc_xml( $text ) {
		return htmlspecialchars( (string) $text, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
	}
}
