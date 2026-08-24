<?php
/**
 * Article type taxonomy.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers inos_article_type.
 */
class INOS_Taxonomies {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register taxonomies.
	 */
	public static function register() {
		register_taxonomy(
			'inos_article_type',
			array( 'post', 'inos_live_blog' ),
			array(
				'labels'            => array(
					'name'          => __( 'Article types', 'infy-news-os-core' ),
					'singular_name' => __( 'Article type', 'infy-news-os-core' ),
					'menu_name'     => __( 'Article types', 'infy-news-os-core' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
				'rewrite'           => array( 'slug' => 'type' ),
			)
		);

		self::maybe_seed_terms();
	}

	/**
	 * Default article types.
	 */
	private static function maybe_seed_terms() {
		if ( get_option( 'inos_article_types_seeded' ) ) {
			return;
		}

		$terms = array(
			'news'      => __( 'News', 'infy-news-os-core' ),
			'analysis'  => __( 'Analysis', 'infy-news-os-core' ),
			'opinion'   => __( 'Opinion', 'infy-news-os-core' ),
			'interview' => __( 'Interview', 'infy-news-os-core' ),
			'explainer' => __( 'Explainer', 'infy-news-os-core' ),
			'review'    => __( 'Review', 'infy-news-os-core' ),
			'live'      => __( 'Live', 'infy-news-os-core' ),
		);

		foreach ( $terms as $slug => $name ) {
			if ( ! term_exists( $slug, 'inos_article_type' ) ) {
				wp_insert_term( $name, 'inos_article_type', array( 'slug' => $slug ) );
			}
		}

		update_option( 'inos_article_types_seeded', 1 );
	}

	/**
	 * Schema.org @type from article type slug.
	 *
	 * @param string $slug Term slug.
	 * @return string
	 */
	public static function schema_type_from_slug( $slug ) {
		$map = array(
			'news'      => 'ReportageNewsArticle',
			'analysis'  => 'AnalysisNewsArticle',
			'opinion'   => 'OpinionNewsArticle',
			'interview' => 'NewsArticle',
			'explainer' => 'BackgroundNewsArticle',
			'review'    => 'ReviewNewsArticle',
			'live'      => 'LiveBlogPosting',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : (string) inos_get_option( 'default_article_type', 'NewsArticle' );
	}
}
