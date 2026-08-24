<?php
/**
 * Newsletter form, storage, webhook, CSV export.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Email capture.
 */
class INOS_Newsletter {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_create_table' ) );
		add_shortcode( 'inos_newsletter', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_post_inos_subscribe', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_nopriv_inos_subscribe', array( __CLASS__, 'handle' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_export' ) );
	}

	/**
	 * Table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'inos_subscribers';
	}

	/**
	 * Create table.
	 */
	public static function create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(190) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'subscribed',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Ensure table exists.
	 */
	public static function maybe_create_table() {
		global $wpdb;
		$table = self::table();
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $found !== $table ) {
			self::create_table();
		}
	}

	/**
	 * Shortcode.
	 *
	 * @return string
	 */
	public static function shortcode() {
		ob_start();
		self::render_form();
		return (string) ob_get_clean();
	}

	/**
	 * Form markup.
	 */
	public static function render_form() {
		if ( ! inos_get_option( 'enable_newsletter', 1 ) ) {
			return;
		}

		$heading = (string) inos_get_option( 'newsletter_heading', '' );
		$desc    = (string) inos_get_option( 'newsletter_description', '' );
		$button  = (string) inos_get_option( 'newsletter_button', __( 'Subscribe', 'infy-news-os-core' ) );
		$ok      = isset( $_GET['inos_nl'] ) ? sanitize_key( wp_unslash( $_GET['inos_nl'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<section class="inos-newsletter" id="inos-subscribe">
			<?php if ( $heading ) : ?>
				<h2 class="inos-newsletter__title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $desc ) : ?>
				<p class="inos-newsletter__desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
			<?php if ( 'ok' === $ok ) : ?>
				<p class="inos-newsletter__success"><?php echo esc_html( (string) inos_get_option( 'newsletter_success', '' ) ); ?></p>
			<?php elseif ( 'dup' === $ok ) : ?>
				<p class="inos-newsletter__success"><?php echo esc_html( inos_label( 'nl_already' ) ); ?></p>
			<?php elseif ( 'err' === $ok ) : ?>
				<p class="inos-newsletter__error"><?php echo esc_html( inos_label( 'nl_invalid' ) ); ?></p>
			<?php endif; ?>
			<form class="inos-newsletter__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) ? ' target="_top"' : ''; ?>>
				<input type="hidden" name="action" value="inos_subscribe" />
				<input type="hidden" name="inos_nl_redirect" value="<?php echo esc_url( self::current_url() ); ?>" />
				<?php wp_nonce_field( 'inos_subscribe', 'inos_nl_nonce' ); ?>
				<label class="screen-reader-text" for="inos-nl-email"><?php echo esc_html( inos_label( 'nl_email' ) ); ?></label>
				<input id="inos-nl-email" type="email" name="inos_email" required placeholder="<?php echo esc_attr( inos_label( 'nl_placeholder' ) ); ?>" />
				<button type="submit"><?php echo esc_html( $button ); ?></button>
			</form>
		</section>
		<?php
	}

	/**
	 * Current URL without query noise.
	 *
	 * @return string
	 */
	private static function current_url() {
		if ( ! empty( $_SERVER['HTTP_HOST'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$uri = strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return home_url( $uri ? $uri : '/' );
		}
		return home_url( '/' );
	}

	/**
	 * Handle POST.
	 */
	public static function handle() {
		$redirect = isset( $_POST['inos_nl_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['inos_nl_redirect'] ) ) : home_url( '/' );
		if ( ! isset( $_POST['inos_nl_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inos_nl_nonce'] ) ), 'inos_subscribe' ) ) {
			wp_safe_redirect( add_query_arg( 'inos_nl', 'err', $redirect ) );
			exit;
		}

		$email = isset( $_POST['inos_email'] ) ? sanitize_email( wp_unslash( $_POST['inos_email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'inos_nl', 'err', $redirect ) );
			exit;
		}

		$status = 'ok';
		if ( inos_get_option( 'newsletter_store_local', 1 ) ) {
			global $wpdb;
			$table = self::table();
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $exists ) {
				$status = 'dup';
			} else {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$table,
					array(
						'email'      => $email,
						'status'     => 'subscribed',
						'created_at' => current_time( 'mysql' ),
					),
					array( '%s', '%s', '%s' )
				);
			}
		}

		$webhook = (string) inos_get_option( 'newsletter_webhook', '' );
		if ( $webhook ) {
			wp_remote_post(
				$webhook,
				array(
					'timeout'  => 8,
					'blocking' => false,
					'body'     => array( 'email' => $email ),
				)
			);
		}

		wp_safe_redirect( add_query_arg( 'inos_nl', $status, $redirect ) );
		exit;
	}

	/**
	 * Subscribers admin page.
	 */
	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;
		$table = self::table();
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 500" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$export = wp_nonce_url( admin_url( 'admin.php?page=inos-subscribers&inos_export=1' ), 'inos_export_csv' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Newsletter subscribers', 'infy-news-os-core' ); ?></h1>
			<p><a class="button" href="<?php echo esc_url( $export ); ?>"><?php esc_html_e( 'Export CSV', 'infy-news-os-core' ); ?></a></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email', 'infy-news-os-core' ); ?></th>
						<th><?php esc_html_e( 'Status', 'infy-news-os-core' ); ?></th>
						<th><?php esc_html_e( 'Date', 'infy-news-os-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No subscribers yet.', 'infy-news-os-core' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row->email ); ?></td>
								<td><?php echo esc_html( $row->status ); ?></td>
								<td><?php echo esc_html( $row->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * CSV export.
	 */
	public static function maybe_export() {
		if ( empty( $_GET['inos_export'] ) || empty( $_GET['page'] ) || 'inos-subscribers' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'inos_export_csv' );

		global $wpdb;
		$table = self::table();
		$rows  = $wpdb->get_results( "SELECT email, status, created_at FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=inos-subscribers.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'email', 'status', 'created_at' ) );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				fputcsv( $out, $row );
			}
		}
		fclose( $out );
		exit;
	}
}
