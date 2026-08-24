<?php
/**
 * Archive listing pagination (load more / infinite).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end archive paging that avoids /section/page/2/ 404s
 * when the category base is empty.
 */
class INOS_Archives {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_paged' ) );
		add_filter( 'redirect_canonical', array( __CLASS__, 'skip_canonical' ), 10, 2 );
		add_action( 'wp_ajax_inos_archive_more', array( __CLASS__, 'ajax_more' ) );
		add_action( 'wp_ajax_nopriv_inos_archive_more', array( __CLASS__, 'ajax_more' ) );
	}

	/**
	 * Public query var for listing pages.
	 *
	 * @param string[] $vars Vars.
	 * @return string[]
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'inos_paged';
		return $vars;
	}

	/**
	 * Map inos_paged onto the main query.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function apply_paged( $query ) {
		if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_main_query() ) {
			return;
		}
		$paged = absint( $query->get( 'inos_paged' ) );
		if ( $paged < 1 && isset( $_GET['inos_paged'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$paged = absint( wp_unslash( $_GET['inos_paged'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( $paged < 2 ) {
			return;
		}
		if ( ! $query->is_archive() && ! $query->is_home() && ! $query->is_search() ) {
			return;
		}
		$query->set( 'paged', $paged );
		$query->is_paged = true;
	}

	/**
	 * Do not send ?inos_paged=2 to /page/2/.
	 *
	 * @param string|false $redirect  Redirect URL.
	 * @param string       $requested Requested URL.
	 * @return string|false
	 */
	public static function skip_canonical( $redirect, $requested ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( get_query_var( 'inos_paged' ) || ( isset( $_GET['inos_paged'] ) && absint( $_GET['inos_paged'] ) > 1 ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		return $redirect;
	}

	/**
	 * JSON HTML for the next archive page.
	 */
	public static function ajax_more() {
		check_ajax_referer( 'inos_archive_more', 'nonce' );

		$paged = max( 2, absint( wp_unslash( $_POST['paged'] ?? 0 ) ) );
		$kind  = sanitize_key( wp_unslash( $_POST['kind'] ?? '' ) );
		$id    = absint( wp_unslash( $_POST['id'] ?? 0 ) );
		$hide  = ! empty( $_POST['hideLive'] );
		$ads   = empty( $_POST['noAds'] );

		$args = array(
			'post_status'    => 'publish',
			'paged'          => $paged,
			'posts_per_page' => (int) get_option( 'posts_per_page', 10 ),
			'no_found_rows'  => false,
		);

		if ( function_exists( 'inos_story_post_types' ) && 'cpt' !== $kind ) {
			$args['post_type'] = inos_story_post_types();
		}

		switch ( $kind ) {
			case 'category':
				$args['cat'] = $id;
				break;
			case 'tag':
				$args['tag_id'] = $id;
				break;
			case 'tax':
				$tax = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
				if ( $tax && $id ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => $tax,
							'field'    => 'term_id',
							'terms'    => $id,
						),
					);
				}
				break;
			case 'author':
				$args['author'] = $id;
				break;
			case 'search':
				$args['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) );
				$args['post_type'] = 'any';
				break;
			case 'date':
				$args['year']     = absint( wp_unslash( $_POST['year'] ?? 0 ) );
				$args['monthnum'] = absint( wp_unslash( $_POST['month'] ?? 0 ) );
				$args['day']      = absint( wp_unslash( $_POST['day'] ?? 0 ) );
				break;
			case 'cpt':
				$args['post_type'] = sanitize_key( wp_unslash( $_POST['postType'] ?? 'inos_live_blog' ) );
				break;
			case 'home':
			default:
				break;
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			wp_send_json_success(
				array(
					'html' => '',
					'next' => 0,
					'max'  => (int) $query->max_num_pages,
				)
			);
		}

		global $wp_query;
		$previous = $wp_query;
		$wp_query = $query;

		ob_start();
		if ( function_exists( 'inos_theme_archive_cards' ) ) {
			inos_theme_archive_cards(
				array(
					'ads'       => $ads,
					'hide_live' => $hide,
				)
			);
		}
		$html = ob_get_clean();

		$wp_query = $previous;
		wp_reset_postdata();

		$next = ( $paged < (int) $query->max_num_pages ) ? $paged + 1 : 0;

		wp_send_json_success(
			array(
				'html' => $html,
				'next' => $next,
				'max'  => (int) $query->max_num_pages,
			)
		);
	}
}
