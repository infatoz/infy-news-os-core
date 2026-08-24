<?php
/**
 * Custom editorial statuses.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * In Review / Copy Edit statuses.
 */
class INOS_Statuses {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'admin_footer-post.php', array( __CLASS__, 'dropdown_js' ) );
		add_action( 'admin_footer-post-new.php', array( __CLASS__, 'dropdown_js' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'states' ), 10, 2 );
	}

	/**
	 * Register statuses.
	 */
	public static function register() {
		if ( ! inos_get_option( 'enable_custom_statuses', 1 ) ) {
			return;
		}

		register_post_status(
			'inos_review',
			array(
				'label'                     => _x( 'In Review', 'post status', 'infy-news-os-core' ),
				'public'                    => false,
				'protected'                 => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'In Review <span class="count">(%s)</span>', 'In Review <span class="count">(%s)</span>', 'infy-news-os-core' ),
			)
		);

		register_post_status(
			'inos_copyedit',
			array(
				'label'                     => _x( 'Copy Edit', 'post status', 'infy-news-os-core' ),
				'public'                    => false,
				'protected'                 => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Copy Edit <span class="count">(%s)</span>', 'Copy Edit <span class="count">(%s)</span>', 'infy-news-os-core' ),
			)
		);
	}

	/**
	 * Add statuses to classic dropdown.
	 */
	public static function dropdown_js() {
		if ( ! inos_get_option( 'enable_custom_statuses', 1 ) ) {
			return;
		}
		global $post;
		if ( ! $post || ! in_array( $post->post_type, array( 'post', 'inos_live_blog' ), true ) ) {
			return;
		}
		$current = $post->post_status;
		?>
		<script>
		jQuery(function($){
			var statuses = {
				inos_review: "<?php echo esc_js( __( 'In Review', 'infy-news-os-core' ) ); ?>",
				inos_copyedit: "<?php echo esc_js( __( 'Copy Edit', 'infy-news-os-core' ) ); ?>"
			};
			var $sel = $('#post_status');
			if (!$sel.length) { return; }
			$.each(statuses, function(val, label){
				if (!$sel.find('option[value="'+val+'"]').length) {
					$sel.append($('<option/>').attr('value', val).text(label));
				}
			});
			var current = <?php echo wp_json_encode( $current ); ?>;
			if (statuses[current]) {
				$sel.val(current);
				$('#post-status-display').text(statuses[current]);
			}
		});
		</script>
		<?php
	}

	/**
	 * List table labels.
	 *
	 * @param array<string, string> $states States.
	 * @param WP_Post               $post   Post.
	 * @return array<string, string>
	 */
	public static function states( $states, $post ) {
		if ( 'inos_review' === $post->post_status ) {
			$states['inos_review'] = __( 'In Review', 'infy-news-os-core' );
		}
		if ( 'inos_copyedit' === $post->post_status ) {
			$states['inos_copyedit'] = __( 'Copy Edit', 'infy-news-os-core' );
		}
		if ( '1' === (string) get_post_meta( $post->ID, '_inos_breaking', true ) && function_exists( 'inos_is_breaking' ) && inos_is_breaking( $post->ID ) ) {
			$states['inos_breaking'] = __( 'Breaking', 'infy-news-os-core' );
		}
		return $states;
	}
}
