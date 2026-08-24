<?php
/**
 * Seeds E-E-A-T policy pages used in the footer.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * About, Contact, Editorial Policy, Corrections.
 */
class INOS_Pages {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_seed' ), 30 );
	}

	/**
	 * Create missing policy pages once.
	 */
	public static function maybe_seed() {
		if ( get_option( 'inos_pages_seeded' ) ) {
			self::sync_contact_url();
			return;
		}

		self::seed();
	}

	/**
	 * Insert pages.
	 */
	public static function seed() {
		$pub = (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) );
		$email = (string) inos_get_option( 'contact_email', get_option( 'admin_email' ) );

		$pages = array(
			'about'             => array(
				'title'    => __( 'About', 'infy-news-os-core' ),
				'template' => 'templates/template-about.php',
				'content'  => sprintf(
					"<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph -->",
					esc_html(
						sprintf(
							/* translators: %s: publication name */
							__( '%s is an independent newsroom. We publish original reporting for readers — not for search engines first, though we follow Google Search, News, Discover, and Top Stories publisher guidelines.', 'infy-news-os-core' ),
							$pub
						)
					)
				),
			),
			'contact'           => array(
				'title'    => __( 'Contact', 'infy-news-os-core' ),
				'template' => 'templates/template-contact.php',
				'content'  => sprintf(
					'<!-- wp:paragraph --><p>%s <a href="mailto:%s">%s</a></p><!-- /wp:paragraph -->',
					esc_html__( 'News tips, corrections, and press inquiries:', 'infy-news-os-core' ),
					esc_attr( $email ),
					esc_html( $email )
				),
			),
			'editorial-policy'  => array(
				'title'    => __( 'Editorial Policy', 'infy-news-os-core' ),
				'template' => 'templates/template-editorial-policy.php',
				'content'  => '<!-- wp:paragraph --><p>' . esc_html__( 'We separate news from opinion. Sponsored content is labeled. Authors are named, with a public bio and expertise. Corrections are timestamped on the article and listed on the Corrections page.', 'infy-news-os-core' ) . '</p><!-- /wp:paragraph -->',
			),
			'corrections'       => array(
				'title'    => __( 'Corrections', 'infy-news-os-core' ),
				'template' => 'templates/template-corrections.php',
				'content'  => '<!-- wp:paragraph --><p>' . esc_html__( 'If we get a fact wrong, we correct it visibly on the story. Email the newsroom with the article URL, the error, and a source.', 'infy-news-os-core' ) . '</p><!-- /wp:paragraph -->',
			),
		);

		foreach ( $pages as $slug => $data ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
					'post_content' => $data['content'],
				),
				true
			);
			if ( ! is_wp_error( $id ) && $id ) {
				update_post_meta( $id, '_wp_page_template', $data['template'] );
			}
		}

		update_option( 'inos_pages_seeded', 1 );
		self::sync_contact_url();
	}

	/**
	 * Keep publisher contact URL in sync.
	 */
	private static function sync_contact_url() {
		if ( inos_get_option( 'contact_page_url', '' ) ) {
			return;
		}
		$page = get_page_by_path( 'contact' );
		if ( ! $page ) {
			return;
		}
		$all = INOS_Settings::all();
		$all['contact_page_url'] = get_permalink( $page );
		update_option( INOS_CORE_OPTION, $all );
	}
}
