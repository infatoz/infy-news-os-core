<?php
/**
 * JSON-LD graph for Search / News / Top Stories.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Emits schema.org JSON-LD.
 */
class INOS_Schema {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output' ), 30 );
	}

	/**
	 * Print JSON-LD.
	 */
	public static function output() {
		if ( is_admin() || ! inos_get_option( 'enable_schema', 1 ) ) {
			return;
		}

		$graph = self::graph();
		if ( empty( $graph ) ) {
			return;
		}

		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";
	}

	/**
	 * Graph nodes.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function graph() {
		$nodes   = array();
		$nodes[] = self::organization();
		$nodes[] = self::website();

		if ( is_front_page() ) {
			$nodes[] = self::collection_page( home_url( '/' ), (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) ) );
		} elseif ( is_singular( 'inos_live_blog' ) ) {
			$nodes[] = self::live_blog( get_post() );
			$nodes[] = self::breadcrumbs();
		} elseif ( is_singular( 'post' ) ) {
			$nodes[] = self::news_article( get_post() );
			$nodes[] = self::breadcrumbs();
		} elseif ( is_singular( 'page' ) ) {
			$nodes[] = self::web_page( get_post() );
			$nodes[] = self::breadcrumbs();
		} elseif ( is_author() ) {
			$nodes[] = INOS_Author::profile_page_schema( get_queried_object_id() );
			$nodes[] = INOS_Author::person_schema( get_queried_object_id() );
			$nodes[] = self::breadcrumbs();
		} elseif ( is_search() ) {
			$nodes[] = self::search_results_page();
		} elseif ( is_home() && ! is_front_page() ) {
			$page_id = (int) get_option( 'page_for_posts' );
			$url     = $page_id ? get_permalink( $page_id ) : home_url( '/' );
			$name    = $page_id ? wp_strip_all_tags( get_the_title( $page_id ) ) : (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) );
			$nodes[] = self::collection_page( $url, $name );
			$nodes[] = self::breadcrumbs();
		} elseif ( is_post_type_archive() ) {
			$link = get_post_type_archive_link( get_query_var( 'post_type' ) );
			$name = post_type_archive_title( '', false );
			if ( $link ) {
				$nodes[] = self::collection_page( $link, $name );
			}
			$nodes[] = self::breadcrumbs();
		} elseif ( is_year() || is_month() || is_day() ) {
			$link = self::current_date_url();
			if ( $link ) {
				$nodes[] = self::collection_page( $link, wp_strip_all_tags( get_the_archive_title() ) );
			}
			$nodes[] = self::breadcrumbs();
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$link = get_term_link( $term );
				if ( ! is_wp_error( $link ) ) {
					$nodes[] = self::collection_page( $link, $term->name );
				}
				$nodes[] = self::breadcrumbs();
			}
		}

		return array_values( array_filter( $nodes ) );
	}

	/**
	 * Organization.
	 *
	 * @return array<string, mixed>
	 */
	public static function organization() {
		$org = array(
			'@type' => 'NewsMediaOrganization',
			'@id'   => home_url( '/#organization' ),
			'name'  => (string) inos_get_option( 'org_name', get_bloginfo( 'name' ) ),
			'url'   => home_url( '/' ),
		);

		$legal = (string) inos_get_option( 'legal_name', '' );
		if ( $legal ) {
			$org['legalName'] = $legal;
		}

		$logo = inos_publisher_logo_url();
		if ( $logo ) {
			$org_name  = (string) inos_get_option( 'org_name', get_bloginfo( 'name' ) );
			$logo_node = array(
				'@type'           => 'ImageObject',
				'@id'             => home_url( '/#logo' ),
				'url'             => $logo,
				'contentUrl'      => $logo,
				'creditText'      => $org_name,
				'creator'         => array(
					'@type' => 'Organization',
					'name'  => $org_name,
					'url'   => home_url( '/' ),
				),
				'copyrightNotice' => sprintf(
					/* translators: 1: year, 2: publisher name */
					__( '© %1$s %2$s. All rights reserved.', 'infy-news-os-core' ),
					gmdate( 'Y' ),
					$org_name
				),
			);
			$logo_id = absint( inos_get_option( 'logo_id', 0 ) );
			if ( ! $logo_id ) {
				$logo_id = absint( get_theme_mod( 'custom_logo' ) );
			}
			if ( $logo_id ) {
				$meta = wp_get_attachment_image_src( $logo_id, 'full' );
				if ( $meta ) {
					$logo_node['width']  = (int) $meta[1];
					$logo_node['height'] = (int) $meta[2];
				}
			}
			$org['logo'] = $logo_node;
		}

		$same = inos_publisher_sameas();
		if ( $same ) {
			$org['sameAs'] = $same;
		}

		$email = (string) inos_get_option( 'contact_email', '' );
		if ( $email ) {
			$org['email'] = $email;
		}

		$founding = (string) inos_get_option( 'founding_date', '' );
		if ( $founding ) {
			$org['foundingDate'] = $founding;
		}

		$contact = (string) inos_get_option( 'contact_page_url', '' );
		if ( ! $contact && function_exists( 'inos_policy_page_url' ) ) {
			$contact = inos_policy_page_url( 'contact' );
		}
		if ( $contact ) {
			$org['contactPoint'] = array(
				'@type'       => 'ContactPoint',
				'contactType' => 'editorial',
				'url'         => $contact,
				'email'       => $email,
			);
		}

		if ( function_exists( 'inos_policy_page_url' ) ) {
			$ethics = inos_policy_page_url( 'editorial-policy' );
			$corr   = inos_policy_page_url( 'corrections' );
			if ( $ethics ) {
				$org['publishingPrinciples'] = $ethics;
				$org['ethicsPolicy']         = $ethics;
			}
			if ( $corr ) {
				$org['correctionsPolicy'] = $corr;
			}
		}

		return $org;
	}

	/**
	 * WebSite + SearchAction.
	 *
	 * @return array<string, mixed>
	 */
	public static function website() {
		return array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) ),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/**
	 * NewsArticle node.
	 *
	 * @param WP_Post $post Post.
	 * @return array<string, mixed>
	 */
	public static function news_article( $post ) {
		$slug = inos_get_article_type( $post->ID );
		$type = INOS_Taxonomies::schema_type_from_slug( $slug );
		if ( 'LiveBlogPosting' === $type ) {
			$type = (string) inos_get_option( 'default_article_type', 'NewsArticle' );
		}

		$section = inos_get_primary_section( $post->ID );
		$image   = INOS_Images::image_object( $post->ID );
		$word    = str_word_count( wp_strip_all_tags( (string) $post->post_content ) );
		$mins    = max( 1, (int) ceil( $word / 200 ) );

		$article = array(
			'@type'               => $type,
			'@id'                 => get_permalink( $post ) . '#article',
			'headline'            => wp_strip_all_tags( get_the_title( $post ) ),
			'datePublished'       => get_post_time( DATE_W3C, true, $post ),
			'dateModified'        => get_post_modified_time( DATE_W3C, true, $post ),
			'mainEntityOfPage'    => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post ),
			),
			'author'              => INOS_Author::person_schema( (int) $post->post_author ),
			'publisher'           => array( '@id' => home_url( '/#organization' ) ),
			'isAccessibleForFree' => 'True',
			'wordCount'           => $word,
			'timeRequired'        => 'PT' . $mins . 'M',
			'inLanguage'          => (string) inos_get_option( 'publication_language', 'en' ),
			'copyrightYear'       => (int) get_post_time( 'Y', true, $post ),
			'copyrightHolder'     => array( '@id' => home_url( '/#organization' ) ),
		);

		$dek = inos_get_dek( $post->ID );
		if ( $dek ) {
			$plain = wp_strip_all_tags( $dek );
			$article['description']         = $plain;
			$article['alternativeHeadline'] = $plain;
		}

		if ( $image ) {
			$article['image'] = $image;
			if ( is_array( $image ) && isset( $image[0]['url'] ) ) {
				$article['thumbnailUrl'] = $image[0]['url'];
			} elseif ( is_array( $image ) && ! empty( $image['url'] ) ) {
				$article['thumbnailUrl'] = $image['url'];
			}
		}
		if ( $section ) {
			$article['articleSection'] = $section->name;
			$article['about']          = array(
				'@type' => 'Thing',
				'name'  => $section->name,
			);
		}

		$keywords = function_exists( 'inos_get_news_keywords' ) ? inos_get_news_keywords( $post->ID ) : '';
		if ( $keywords ) {
			$article['keywords'] = $keywords;
		}

		$dateline = function_exists( 'inos_get_dateline' ) ? inos_get_dateline( $post->ID ) : '';
		if ( $dateline ) {
			$article['dateline'] = $dateline;
		}

		$comments = (int) get_comments_number( $post );
		if ( $comments ) {
			$article['commentCount'] = $comments;
		}

		if ( inos_get_option( 'enable_speakable', 1 ) ) {
			$article['speakable'] = array(
				'@type'       => 'SpeakableSpecification',
				'cssSelector' => array( '.inos-article__title', '.inos-article__dek' ),
			);
		}

		$sponsored = get_post_meta( $post->ID, '_inos_sponsored', true );
		if ( '1' === (string) $sponsored ) {
			$label = (string) get_post_meta( $post->ID, '_inos_sponsored_label', true );
			$article['creativeWorkStatus'] = $label ? $label : 'Sponsored';
		}

		return $article;
	}

	/**
	 * LiveBlogPosting.
	 *
	 * @param WP_Post $post Live blog post.
	 * @return array<string, mixed>
	 */
	public static function live_blog( $post ) {
		$updates = INOS_Liveblog::get_updates( $post->ID );
		$items   = array();
		foreach ( $updates as $update ) {
			$items[] = array(
				'@type'         => 'BlogPosting',
				'headline'      => wp_strip_all_tags( get_the_title( $update ) ),
				'datePublished' => get_post_time( DATE_W3C, true, $update ),
				'url'           => INOS_Liveblog::update_url( $update, $post->ID ),
				'articleBody'   => wp_strip_all_tags( $update->post_content ),
			);
		}

		$node = array(
			'@type'             => 'LiveBlogPosting',
			'@id'               => get_permalink( $post ) . '#liveblog',
			'headline'          => wp_strip_all_tags( get_the_title( $post ) ),
			'datePublished'     => get_post_time( DATE_W3C, true, $post ),
			'dateModified'      => get_post_modified_time( DATE_W3C, true, $post ),
			'coverageStartTime' => get_post_time( DATE_W3C, true, $post ),
			'publisher'         => array( '@id' => home_url( '/#organization' ) ),
			'author'            => INOS_Author::person_schema( (int) $post->post_author ),
			'mainEntityOfPage'  => get_permalink( $post ),
			'liveBlogUpdate'    => $items,
		);

		$ended = get_post_meta( $post->ID, '_inos_coverage_ended', true );
		if ( $ended ) {
			$node['coverageEndTime'] = $ended;
		}

		$image = INOS_Images::image_object( $post->ID );
		if ( $image ) {
			$node['image'] = $image;
		}

		$dek = inos_get_dek( $post->ID );
		if ( $dek ) {
			$node['description'] = wp_strip_all_tags( $dek );
		}

		if ( inos_get_option( 'enable_speakable', 1 ) ) {
			$node['speakable'] = array(
				'@type'       => 'SpeakableSpecification',
				'cssSelector' => array( '.inos-article__title', '.inos-article__dek' ),
			);
		}

		return $node;
	}

	/**
	 * WebPage for static pages.
	 *
	 * @param WP_Post $post Page.
	 * @return array<string, mixed>
	 */
	private static function web_page( $post ) {
		return array(
			'@type'       => 'WebPage',
			'@id'         => get_permalink( $post ) . '#webpage',
			'url'         => get_permalink( $post ),
			'name'        => wp_strip_all_tags( get_the_title( $post ) ),
			'isPartOf'    => array( '@id' => home_url( '/#website' ) ),
			'publisher'   => array( '@id' => home_url( '/#organization' ) ),
		);
	}

	/**
	 * CollectionPage.
	 *
	 * @param string $url  URL.
	 * @param string $name Name.
	 * @return array<string, mixed>
	 */
	private static function collection_page( $url, $name ) {
		$node = array(
			'@type'     => 'CollectionPage',
			'@id'       => trailingslashit( $url ) . '#webpage',
			'url'       => $url,
			'name'      => $name,
			'isPartOf'  => array( '@id' => home_url( '/#website' ) ),
			'publisher' => array( '@id' => home_url( '/#organization' ) ),
		);
		$desc = class_exists( 'INOS_SEO' ) ? INOS_SEO::description() : '';
		if ( $desc ) {
			$node['description'] = wp_strip_all_tags( $desc );
		}
		if ( ! is_front_page() ) {
			$list = self::item_list( $url );
			if ( $list ) {
				$node['mainEntity'] = $list;
			}
		}
		return $node;
	}

	/**
	 * SearchResultsPage.
	 *
	 * @return array<string, mixed>
	 */
	private static function search_results_page() {
		$url  = get_search_link();
		$node = array(
			'@type'     => 'SearchResultsPage',
			'@id'       => trailingslashit( $url ) . '#webpage',
			'url'       => $url,
			'name'      => wp_get_document_title(),
			'isPartOf'  => array( '@id' => home_url( '/#website' ) ),
			'publisher' => array( '@id' => home_url( '/#organization' ) ),
		);
		$desc = class_exists( 'INOS_SEO' ) ? INOS_SEO::description() : '';
		if ( $desc ) {
			$node['description'] = wp_strip_all_tags( $desc );
		}
		$list = self::item_list( $url );
		if ( $list ) {
			$node['mainEntity'] = $list;
		}
		return $node;
	}

	/**
	 * ItemList of stories on the current listing page.
	 *
	 * @param string $url Listing URL.
	 * @return array<string, mixed>|null
	 */
	private static function item_list( $url ) {
		global $wp_query;
		if ( empty( $wp_query->posts ) ) {
			return null;
		}
		$elements = array();
		$pos      = 1;
		foreach ( $wp_query->posts as $post ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'url'      => get_permalink( $post ),
				'name'     => wp_strip_all_tags( get_the_title( $post ) ),
			);
			++$pos;
		}
		return array(
			'@type'           => 'ItemList',
			'@id'             => trailingslashit( $url ) . '#itemlist',
			'numberOfItems'   => count( $elements ),
			'itemListElement' => $elements,
		);
	}

	/**
	 * Current date-archive permalink.
	 *
	 * @return string
	 */
	private static function current_date_url() {
		if ( is_day() ) {
			return get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		}
		if ( is_month() ) {
			return get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		}
		if ( is_year() ) {
			return get_year_link( get_query_var( 'year' ) );
		}
		return '';
	}

	/**
	 * BreadcrumbList.
	 *
	 * @return array<string, mixed>|null
	 */
	private static function breadcrumbs() {
		$trail = function_exists( 'inos_get_breadcrumb_items' ) ? inos_get_breadcrumb_items() : array();
		if ( count( $trail ) < 2 ) {
			return null;
		}

		$items    = array();
		$last_url = home_url( '/' );
		foreach ( $trail as $crumb ) {
			$name = isset( $crumb['name'] ) ? trim( wp_strip_all_tags( (string) $crumb['name'] ) ) : '';
			$url  = isset( $crumb['url'] ) ? esc_url_raw( (string) $crumb['url'] ) : '';
			if ( '' === $name || '' === $url ) {
				continue;
			}
			$last_url = $url;
			$items[]  = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => $name,
				'item'     => $url,
			);
		}

		if ( count( $items ) < 2 ) {
			return null;
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'@id'             => trailingslashit( $last_url ) . '#breadcrumb',
			'itemListElement' => $items,
		);
	}
}
