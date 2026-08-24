<?php
/**
 * Live blog post types, REST polling, metabox for coverage end.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Live coverage.
 */
class INOS_Liveblog {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 11 );
		add_action( 'init', array( __CLASS__, 'maybe_backfill_sections' ), 20 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_inos_live_blog', array( __CLASS__, 'save_parent' ) );
		add_action( 'save_post_inos_live_update', array( __CLASS__, 'touch_parent' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'rest' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * Register CPTs.
	 */
	public static function register_post_types() {
		register_post_type(
			'inos_live_blog',
			array(
				'labels'              => array(
					'name'          => __( 'Live blogs', 'infy-news-os-core' ),
					'singular_name' => __( 'Live blog', 'infy-news-os-core' ),
					'add_new_item'  => __( 'Add live blog', 'infy-news-os-core' ),
				),
				'public'              => true,
				'has_archive'         => true,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-controls-repeat',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'custom-fields' ),
				'rewrite'             => array( 'slug' => 'live' ),
				'show_in_nav_menus'   => true,
				'taxonomies'          => array( 'category', 'post_tag', 'inos_article_type' ),
			)
		);

		register_post_type(
			'inos_live_update',
			array(
				'labels'            => array(
					'name'          => __( 'Live updates', 'infy-news-os-core' ),
					'singular_name' => __( 'Live update', 'infy-news-os-core' ),
					'add_new_item'  => __( 'Add live update', 'infy-news-os-core' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_in_menu'      => 'edit.php?post_type=inos_live_blog',
				'show_in_rest'      => true,
				'supports'          => array( 'title', 'editor', 'author', 'custom-fields' ),
				'capability_type'   => 'post',
			)
		);
	}

	/**
	 * Attach story taxonomies so live blogs appear in Latest and section lists.
	 */
	public static function register_taxonomies() {
		register_taxonomy_for_object_type( 'category', 'inos_live_blog' );
		register_taxonomy_for_object_type( 'post_tag', 'inos_live_blog' );
		if ( taxonomy_exists( 'inos_article_type' ) ) {
			register_taxonomy_for_object_type( 'inos_article_type', 'inos_live_blog' );
		}
	}

	/**
	 * Assign demo live blogs to sections if they were imported before taxonomies existed.
	 */
	public static function maybe_backfill_sections() {
		if ( get_option( 'inos_live_blog_sections_backfill' ) ) {
			return;
		}

		$map = array(
			'inos-demo-live-chip-summit' => 'technology',
			'inos-demo-live-heat-alert'  => 'science',
		);
		foreach ( $map as $slug => $cat_slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'inos_live_blog' );
			$term = get_term_by( 'slug', $cat_slug, 'category' );
			if ( ! $post || ! $term || is_wp_error( $term ) ) {
				continue;
			}
			$terms = get_the_terms( $post, 'category' );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				wp_set_object_terms( $post->ID, array( (int) $term->term_id ), 'category' );
				update_post_meta( $post->ID, '_inos_primary_section', (int) $term->term_id );
			}
			if ( taxonomy_exists( 'inos_article_type' ) && ! has_term( 'live', 'inos_article_type', $post ) ) {
				wp_set_object_terms( $post->ID, 'live', 'inos_article_type', true );
			}
		}

		update_option( 'inos_live_blog_sections_backfill', 1, false );
	}

	/**
	 * Parent metabox: coverage ended + link to add updates.
	 */
	public static function boxes() {
		add_meta_box(
			'inos-live-coverage',
			__( 'Live coverage', 'infy-news-os-core' ),
			array( __CLASS__, 'render_parent_box' ),
			'inos_live_blog',
			'side'
		);

		add_meta_box(
			'inos-live-parent',
			__( 'Parent live blog', 'infy-news-os-core' ),
			array( __CLASS__, 'render_child_box' ),
			'inos_live_update',
			'side'
		);
	}

	/**
	 * Parent box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_parent_box( $post ) {
		$ended  = get_post_meta( $post->ID, '_inos_coverage_ended', true );
		$closed = get_post_meta( $post->ID, '_inos_coverage_closed', true );
		$url    = admin_url( 'post-new.php?post_type=inos_live_update&inos_parent=' . $post->ID );
		?>
		<p>
			<label><input type="checkbox" name="inos_coverage_closed" value="1" <?php checked( $closed, '1' ); ?> /> <?php esc_html_e( 'Coverage ended', 'infy-news-os-core' ); ?></label>
		</p>
		<?php if ( $ended ) : ?>
			<p class="description"><?php echo esc_html( $ended ); ?></p>
		<?php endif; ?>
		<p><a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Add update', 'infy-news-os-core' ); ?></a></p>
		<?php
		$updates = self::get_updates( $post->ID );
		if ( $updates ) {
			echo '<ul>';
			foreach ( array_slice( $updates, 0, 8 ) as $u ) {
				echo '<li><a href="' . esc_url( get_edit_post_link( $u->ID ) ) . '">' . esc_html( get_the_title( $u ) ) . '</a></li>';
			}
			echo '</ul>';
		}
	}

	/**
	 * Child parent selector.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_child_box( $post ) {
		$parent = $post->post_parent ? (int) $post->post_parent : ( isset( $_GET['inos_parent'] ) ? absint( $_GET['inos_parent'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$blogs  = get_posts( array( 'post_type' => 'inos_live_blog', 'posts_per_page' => 50, 'post_status' => array( 'publish', 'draft', 'future' ) ) );
		echo '<select name="inos_live_parent" class="widefat">';
		echo '<option value="0">' . esc_html__( '— Select —', 'infy-news-os-core' ) . '</option>';
		foreach ( $blogs as $blog ) {
			echo '<option value="' . esc_attr( (string) $blog->ID ) . '" ' . selected( $parent, $blog->ID, false ) . '>' . esc_html( get_the_title( $blog ) ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Save coverage flags.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_parent( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$closed = empty( $_POST['inos_coverage_closed'] ) ? '0' : '1'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, '_inos_coverage_closed', $closed );
		if ( '1' === $closed ) {
			$existing = get_post_meta( $post_id, '_inos_coverage_ended', true );
			if ( ! $existing ) {
				update_post_meta( $post_id, '_inos_coverage_ended', gmdate( 'c' ) );
			}
		} else {
			delete_post_meta( $post_id, '_inos_coverage_ended' );
		}
	}

	/**
	 * Assign parent and bump live blog modified date.
	 *
	 * @param int $post_id Update ID.
	 */
	public static function touch_parent( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$parent = isset( $_POST['inos_live_parent'] ) ? absint( $_POST['inos_live_parent'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $parent ) {
			remove_action( 'save_post_inos_live_update', array( __CLASS__, 'touch_parent' ) );
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_parent' => $parent,
				)
			);
			wp_update_post(
				array(
					'ID'                => $parent,
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', true ),
				)
			);
			add_action( 'save_post_inos_live_update', array( __CLASS__, 'touch_parent' ) );
		}
	}

	/**
	 * REST route for polling.
	 */
	public static function rest() {
		register_rest_route(
			'inos/v1',
			'/live-blog/(?P<id>\d+)/updates',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( __CLASS__, 'rest_updates' ),
				'args'                => array(
					'id'    => array( 'sanitize_callback' => 'absint' ),
					'after' => array( 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
	}

	/**
	 * REST callback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_updates( $request ) {
		$id    = absint( $request['id'] );
		$after = $request->get_param( 'after' );
		$items = array();
		foreach ( self::get_updates( $id ) as $update ) {
			$iso = get_post_time( DATE_W3C, true, $update );
			if ( $after && strtotime( $iso ) <= strtotime( $after ) ) {
				continue;
			}
			$items[] = self::serialize_update( $update, $id );
		}
		return rest_ensure_response(
			array(
				'closed'  => '1' === (string) get_post_meta( $id, '_inos_coverage_closed', true ),
				'updates' => $items,
			)
		);
	}

	/**
	 * Child updates newest first.
	 *
	 * @param int $parent_id Parent ID.
	 * @return WP_Post[]
	 */
	public static function get_updates( $parent_id ) {
		$q = new WP_Query(
			array(
				'post_type'           => 'inos_live_update',
				'post_status'         => 'publish',
				'post_parent'         => absint( $parent_id ),
				'posts_per_page'      => 100,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		return $q->posts;
	}

	/**
	 * Coverage is still happening.
	 *
	 * @param int $post_id Live blog ID.
	 * @return bool
	 */
	public static function is_live( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || 'inos_live_blog' !== get_post_type( $post_id ) ) {
			return false;
		}
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return false;
		}
		return '1' !== (string) get_post_meta( $post_id, '_inos_coverage_closed', true );
	}

	/**
	 * Fragment id for one update.
	 *
	 * @param int $update_id Update ID.
	 * @return string
	 */
	public static function update_anchor( $update_id ) {
		return 'inos-update-' . absint( $update_id );
	}

	/**
	 * Canonical URL for one update (parent permalink + hash).
	 *
	 * @param WP_Post|int $update    Update.
	 * @param int         $parent_id Parent live blog ID.
	 * @return string
	 */
	public static function update_url( $update, $parent_id = 0 ) {
		$update = get_post( $update );
		if ( ! $update ) {
			return '';
		}
		$parent_id = $parent_id ? absint( $parent_id ) : (int) $update->post_parent;
		$base      = $parent_id ? get_permalink( $parent_id ) : get_permalink();
		if ( ! $base ) {
			return '';
		}
		return $base . '#' . self::update_anchor( $update->ID );
	}

	/**
	 * REST / JS payload for one update.
	 *
	 * @param WP_Post $update    Update.
	 * @param int     $parent_id Parent ID.
	 * @return array<string, mixed>
	 */
	public static function serialize_update( $update, $parent_id ) {
		$title = wp_strip_all_tags( get_the_title( $update ) );
		$plain = wp_strip_all_tags( $update->post_content );
		$tease = $plain ? wp_trim_words( $plain, 28 ) : '';
		$url   = self::update_url( $update, $parent_id );
		if ( function_exists( 'inos_apply_share_utm' ) ) {
			$url = inos_apply_share_utm( $url, 'native', 'live-update-' . $update->ID, 'article' );
		}

		return array(
			'id'         => (int) $update->ID,
			'title'      => $title,
			'content'    => apply_filters( 'the_content', $update->post_content ),
			'date'       => get_post_time( DATE_W3C, true, $update ),
			'display'    => get_the_date( '', $update ) . ' ' . get_the_time( '', $update ),
			'url'        => $url,
			'share_text' => $tease ? $title . ' — ' . $tease : $title,
		);
	}

	/**
	 * Front assets on live blogs.
	 */
	public static function assets() {
		if ( ! is_singular( 'inos_live_blog' ) ) {
			return;
		}
		wp_enqueue_style( 'inos-public', INOS_CORE_URL . 'public/css/inos-public.css', array(), INOS_CORE_VERSION );
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		wp_enqueue_script(
			'inos-public',
			INOS_CORE_URL . 'public/js/inos-public.js',
			array(),
			INOS_CORE_VERSION,
			true
		);
		$post_id = get_the_ID();
		$updates = self::get_updates( $post_id );
		$latest  = ! empty( $updates[0] ) ? get_post_time( DATE_W3C, true, $updates[0] ) : get_post_time( DATE_W3C, true, $post_id );
		$closed  = '1' === (string) get_post_meta( $post_id, '_inos_coverage_closed', true );
		wp_localize_script(
			'inos-public',
			'inosLive',
			array(
				'id'       => $post_id,
				'after'    => $latest,
				'rest'     => esc_url_raw( rest_url( 'inos/v1/live-blog/' . $post_id . '/updates' ) ),
				'poll'     => $closed ? 0 : 10000,
				'ended'    => inos_label( 'coverage_ended' ),
				'share'    => inos_label( 'share_this_update' ),
				'fresh'    => inos_label( 'new_update' ),
				'nUpdates' => inos_label( 'n_updates' ),
				'icon'     => function_exists( 'inos_theme_share_icon' ) ? inos_theme_share_icon( 'native' ) : '',
			)
		);
	}

	/**
	 * Title placeholder.
	 *
	 * @param string  $title Title.
	 * @param WP_Post $post  Post.
	 * @return string
	 */
	public static function title_placeholder( $title, $post ) {
		if ( 'inos_live_update' === $post->post_type ) {
			return __( 'Update headline', 'infy-news-os-core' );
		}
		return $title;
	}
}
