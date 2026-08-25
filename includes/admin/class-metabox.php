<?php
/**
 * Article metabox.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Infy News OS article fields.
 */
class INOS_Metabox {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_post', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'save_post_inos_live_blog', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'wp_ajax_inos_search_posts', array( __CLASS__, 'ajax_search' ) );
	}

	/**
	 * Register metabox.
	 */
	public static function boxes() {
		add_meta_box(
			'inos-article',
			__( 'Infy News OS', 'infy-news-os-core' ),
			array( __CLASS__, 'render' ),
			array( 'post', 'inos_live_blog' ),
			'normal',
			'high'
		);
	}

	/**
	 * Render fields.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'inos_save_article', 'inos_article_nonce' );

		$kicker     = get_post_meta( $post->ID, '_inos_kicker', true );
		$dek        = get_post_meta( $post->ID, '_inos_dek', true );
		$dateline   = get_post_meta( $post->ID, '_inos_dateline', true );
		$breaking   = get_post_meta( $post->ID, '_inos_breaking', true );
		$exclusive  = get_post_meta( $post->ID, '_inos_exclusive', true );
		$sponsored  = get_post_meta( $post->ID, '_inos_sponsored', true );
		$spon_label = get_post_meta( $post->ID, '_inos_sponsored_label', true );
		$primary    = absint( get_post_meta( $post->ID, '_inos_primary_section', true ) );
		$source     = get_post_meta( $post->ID, '_inos_source', true );
		$correction = get_post_meta( $post->ID, '_inos_correction', true );
		$robots     = get_post_meta( $post->ID, '_inos_robots', true );
		$canonical  = get_post_meta( $post->ID, '_inos_canonical', true );
		$seo_title  = get_post_meta( $post->ID, '_inos_seo_title', true );
		$seo_desc   = get_post_meta( $post->ID, '_inos_seo_description', true );
		$home_pin   = get_post_meta( $post->ID, '_inos_homepage_pin', true );
		$trend_pin  = get_post_meta( $post->ID, '_inos_trending_pin', true );
		$credit     = get_post_meta( $post->ID, '_inos_image_credit', true );
		$keywords   = get_post_meta( $post->ID, '_inos_news_keywords', true );
		$also_ids   = class_exists( 'INOS_Trending' ) ? INOS_Trending::also_read_ids( $post->ID ) : array();
		$also_max   = max( 1, absint( inos_get_option( 'also_read_count', 4 ) ) );

		$cats = get_categories( array( 'hide_empty' => false ) );
		?>
		<div class="inos-metabox">
			<p>
				<label for="inos_kicker"><strong><?php esc_html_e( 'Kicker', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_kicker" name="inos_kicker" value="<?php echo esc_attr( $kicker ); ?>" />
			</p>
			<p>
				<label for="inos_dek"><strong><?php esc_html_e( 'Dek / subheadline', 'infy-news-os-core' ); ?></strong></label>
				<textarea class="widefat" rows="2" id="inos_dek" name="inos_dek"><?php echo esc_textarea( $dek ); ?></textarea>
			</p>
			<p>
				<label for="inos_dateline"><strong><?php esc_html_e( 'Dateline', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_dateline" name="inos_dateline" value="<?php echo esc_attr( $dateline ); ?>" placeholder="<?php esc_attr_e( 'NEW DELHI', 'infy-news-os-core' ); ?>" />
			</p>
			<p>
				<label><input type="checkbox" name="inos_breaking" value="1" <?php checked( $breaking, '1' ); ?> /> <?php esc_html_e( 'Breaking news', 'infy-news-os-core' ); ?></label>
				<label style="margin-left:1em;"><input type="checkbox" name="inos_exclusive" value="1" <?php checked( $exclusive, '1' ); ?> /> <?php esc_html_e( 'Exclusive', 'infy-news-os-core' ); ?></label>
				<label style="margin-left:1em;"><input type="checkbox" name="inos_sponsored" value="1" <?php checked( $sponsored, '1' ); ?> /> <?php esc_html_e( 'Sponsored', 'infy-news-os-core' ); ?></label>
			</p>
			<p>
				<label for="inos_sponsored_label"><strong><?php esc_html_e( 'Sponsored disclosure label', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_sponsored_label" name="inos_sponsored_label" value="<?php echo esc_attr( $spon_label ); ?>" placeholder="<?php esc_attr_e( 'Paid content', 'infy-news-os-core' ); ?>" />
			</p>
			<p>
				<label for="inos_primary_section"><strong><?php esc_html_e( 'Primary section', 'infy-news-os-core' ); ?></strong></label>
				<select id="inos_primary_section" name="inos_primary_section" class="widefat">
					<option value="0"><?php esc_html_e( '— First category —', 'infy-news-os-core' ); ?></option>
					<?php foreach ( $cats as $cat ) : ?>
						<option value="<?php echo esc_attr( (string) $cat->term_id ); ?>" <?php selected( $primary, $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="inos_news_keywords"><strong><?php esc_html_e( 'Google News keywords', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_news_keywords" name="inos_news_keywords" value="<?php echo esc_attr( $keywords ); ?>" placeholder="<?php esc_attr_e( 'AI, regulation, India', 'infy-news-os-core' ); ?>" />
				<span class="description"><?php esc_html_e( 'Comma-separated, up to 10. Used in the news sitemap, keywords / news_keywords meta, and schema. Leave empty to use the section, tags, and headline.', 'infy-news-os-core' ); ?></span>
			</p>
			<p>
				<label for="inos_source"><strong><?php esc_html_e( 'Source / wire', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_source" name="inos_source" value="<?php echo esc_attr( $source ); ?>" />
			</p>
			<p>
				<label for="inos_image_credit"><strong><?php esc_html_e( 'Featured image credit', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_image_credit" name="inos_image_credit" value="<?php echo esc_attr( $credit ); ?>" />
				<span class="description"><?php esc_html_e( 'Shown as a caption and in Image Search / schema (photographer, agency, or license note).', 'infy-news-os-core' ); ?></span>
			</p>
			<p>
				<label for="inos_correction"><strong><?php echo esc_html( inos_get_option( 'correction_label', __( 'Correction', 'infy-news-os-core' ) ) ); ?></strong></label>
				<textarea class="widefat" rows="3" id="inos_correction" name="inos_correction"><?php echo esc_textarea( $correction ); ?></textarea>
			</p>
			<p>
				<label><input type="checkbox" name="inos_homepage_pin" value="1" <?php checked( $home_pin, '1' ); ?> /> <?php esc_html_e( 'Pin on homepage lead', 'infy-news-os-core' ); ?></label>
				<label style="margin-left:1em;"><input type="checkbox" name="inos_trending_pin" value="1" <?php checked( $trend_pin, '1' ); ?> /> <?php esc_html_e( 'Pin in trending / mega menu', 'infy-news-os-core' ); ?></label>
			</p>
			<div class="inos-also-read" data-inos-also-read data-max="<?php echo esc_attr( (string) $also_max ); ?>" data-exclude="<?php echo esc_attr( (string) $post->ID ); ?>">
				<p>
					<label for="inos_also_read_search"><strong><?php esc_html_e( 'Also read', 'infy-news-os-core' ); ?></strong></label>
				</p>
				<p class="description"><?php esc_html_e( 'Pin extra stories to show beside Related at the bottom of this article. Search by headline.', 'infy-news-os-core' ); ?></p>
				<input type="hidden" name="inos_also_read_ids" id="inos_also_read_ids" value="<?php echo esc_attr( implode( ',', $also_ids ) ); ?>" />
				<ul class="inos-also-read__picked">
					<?php foreach ( $also_ids as $also_id ) : ?>
						<?php
						$also_post = get_post( $also_id );
						if ( ! $also_post ) {
							continue;
						}
						?>
						<li data-id="<?php echo esc_attr( (string) $also_id ); ?>">
							<span><?php echo esc_html( get_the_title( $also_post ) ); ?></span>
							<button type="button" class="button-link inos-also-read__remove"><?php esc_html_e( 'Remove', 'infy-news-os-core' ); ?></button>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="search" class="widefat inos-also-read__search" id="inos_also_read_search" placeholder="<?php esc_attr_e( 'Search published stories…', 'infy-news-os-core' ); ?>" autocomplete="off" />
				<ul class="inos-also-read__results" hidden></ul>
			</div>
			<p>
				<label for="inos_editorial_status"><strong><?php esc_html_e( 'Editorial status', 'infy-news-os-core' ); ?></strong></label>
				<select id="inos_editorial_status" name="inos_editorial_status" class="widefat">
					<option value=""><?php esc_html_e( '— Keep current —', 'infy-news-os-core' ); ?></option>
					<option value="draft" <?php selected( $post->post_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'infy-news-os-core' ); ?></option>
					<option value="inos_review" <?php selected( $post->post_status, 'inos_review' ); ?>><?php esc_html_e( 'In Review', 'infy-news-os-core' ); ?></option>
					<option value="inos_copyedit" <?php selected( $post->post_status, 'inos_copyedit' ); ?>><?php esc_html_e( 'Copy Edit', 'infy-news-os-core' ); ?></option>
					<option value="pending" <?php selected( $post->post_status, 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'infy-news-os-core' ); ?></option>
					<option value="publish" <?php selected( $post->post_status, 'publish' ); ?>><?php esc_html_e( 'Published', 'infy-news-os-core' ); ?></option>
				</select>
			</p>
			<hr />
			<p>
				<label for="inos_seo_title"><strong><?php esc_html_e( 'SEO title', 'infy-news-os-core' ); ?></strong></label>
				<input type="text" class="widefat" id="inos_seo_title" name="inos_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" />
			</p>
			<p>
				<label for="inos_seo_description"><strong><?php esc_html_e( 'SEO / meta description', 'infy-news-os-core' ); ?></strong></label>
				<textarea class="widefat" rows="2" id="inos_seo_description" name="inos_seo_description"><?php echo esc_textarea( $seo_desc ); ?></textarea>
			</p>
			<p>
				<label for="inos_canonical"><strong><?php esc_html_e( 'Canonical URL override', 'infy-news-os-core' ); ?></strong></label>
				<input type="url" class="widefat" id="inos_canonical" name="inos_canonical" value="<?php echo esc_attr( $canonical ); ?>" />
			</p>
			<p>
				<label for="inos_robots"><strong><?php esc_html_e( 'Robots override', 'infy-news-os-core' ); ?></strong></label>
				<select id="inos_robots" name="inos_robots" class="widefat">
					<option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default (index, follow)', 'infy-news-os-core' ); ?></option>
					<option value="noindex,follow" <?php selected( $robots, 'noindex,follow' ); ?>><?php esc_html_e( 'noindex, follow', 'infy-news-os-core' ); ?></option>
					<option value="noindex,nofollow" <?php selected( $robots, 'noindex,nofollow' ); ?>><?php esc_html_e( 'noindex, nofollow', 'infy-news-os-core' ); ?></option>
				</select>
			</p>
			<?php if ( class_exists( 'INOS_Push' ) && INOS_Push::is_enabled() ) : ?>
				<?php
				$push_sent    = get_post_meta( $post->ID, INOS_Push::META_SENT, true );
				$push_default = ( 'publish' !== $post->post_status && ! $push_sent );
				?>
				<p>
					<label>
						<input type="checkbox" name="inos_send_push" value="1" <?php checked( $push_default ); ?> />
						<?php
						echo $push_sent
							? esc_html__( 'Send web push again (subscribers already received this article)', 'infy-news-os-core' )
							: esc_html__( 'Send web push when this article is published', 'infy-news-os-core' );
						?>
					</label>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Persist metabox.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['inos_article_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inos_article_nonce'] ) ), 'inos_save_article' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$text = array(
			'inos_kicker'          => '_inos_kicker',
			'inos_dateline'        => '_inos_dateline',
			'inos_sponsored_label' => '_inos_sponsored_label',
			'inos_source'          => '_inos_source',
			'inos_image_credit'    => '_inos_image_credit',
			'inos_news_keywords'   => '_inos_news_keywords',
			'inos_seo_title'       => '_inos_seo_title',
			'inos_robots'          => '_inos_robots',
		);
		foreach ( $text as $field => $meta ) {
			$val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
			update_post_meta( $post_id, $meta, $val );
		}

		$areas = array(
			'inos_dek'         => '_inos_dek',
			'inos_correction'  => '_inos_correction',
			'inos_seo_description' => '_inos_seo_description',
		);
		foreach ( $areas as $field => $meta ) {
			$val = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
			update_post_meta( $post_id, $meta, $val );
		}

		$canonical = isset( $_POST['inos_canonical'] ) ? esc_url_raw( wp_unslash( $_POST['inos_canonical'] ) ) : '';
		update_post_meta( $post_id, '_inos_canonical', $canonical );
		update_post_meta( $post_id, '_inos_primary_section', isset( $_POST['inos_primary_section'] ) ? absint( $_POST['inos_primary_section'] ) : 0 );

		$also_raw = isset( $_POST['inos_also_read_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['inos_also_read_ids'] ) ) : '';
		$also_ids = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $also_raw ) ) ) ) );
		$also_ids = array_values( array_diff( $also_ids, array( $post_id ) ) );
		$also_ids = array_slice( $also_ids, 0, max( 1, absint( inos_get_option( 'also_read_count', 4 ) ) ) );
		update_post_meta( $post_id, '_inos_also_read_ids', implode( ',', $also_ids ) );

		$checks = array( 'inos_breaking' => '_inos_breaking', 'inos_exclusive' => '_inos_exclusive', 'inos_sponsored' => '_inos_sponsored', 'inos_homepage_pin' => '_inos_homepage_pin', 'inos_trending_pin' => '_inos_trending_pin' );
		foreach ( $checks as $field => $meta ) {
			update_post_meta( $post_id, $meta, empty( $_POST[ $field ] ) ? '0' : '1' );
		}

		if ( class_exists( 'INOS_Push' ) ) {
			update_post_meta( $post_id, '_inos_send_push', empty( $_POST['inos_send_push'] ) ? '0' : '1' );
		}

		if ( ! empty( $_POST['inos_breaking'] ) ) {
			$hours = max( 1, absint( inos_get_option( 'breaking_duration_hours', 24 ) ) );
			update_post_meta( $post_id, '_inos_breaking_until', time() + ( $hours * HOUR_IN_SECONDS ) );
		} else {
			delete_post_meta( $post_id, '_inos_breaking_until' );
		}

		$correction = get_post_meta( $post_id, '_inos_correction', true );
		if ( $correction && ! get_post_meta( $post_id, '_inos_correction_time', true ) ) {
			update_post_meta( $post_id, '_inos_correction_time', gmdate( 'c' ) );
		}
		if ( ! $correction ) {
			delete_post_meta( $post_id, '_inos_correction_time' );
		}

		if ( ! empty( $_POST['inos_homepage_pin'] ) ) {
			INOS_Settings::update( array_merge( INOS_Settings::all(), array( 'lead_post_id' => $post_id ) ) );
		}

		if ( ! empty( $_POST['inos_editorial_status'] ) && current_user_can( 'edit_post', $post_id ) ) {
			$status = sanitize_key( wp_unslash( $_POST['inos_editorial_status'] ) );
			$ok     = array( 'draft', 'pending', 'publish', 'inos_review', 'inos_copyedit' );
			if ( in_array( $status, $ok, true ) && $status !== $post->post_status ) {
				remove_action( 'save_post_post', array( __CLASS__, 'save' ), 10 );
				remove_action( 'save_post_inos_live_blog', array( __CLASS__, 'save' ), 10 );
				wp_update_post(
					array(
						'ID'          => $post_id,
						'post_status' => $status,
					)
				);
				add_action( 'save_post_post', array( __CLASS__, 'save' ), 10, 2 );
				add_action( 'save_post_inos_live_blog', array( __CLASS__, 'save' ), 10, 2 );
			}
		}
	}

	/**
	 * AJAX headline search for the Also read picker.
	 */
	public static function ajax_search() {
		check_ajax_referer( 'inos_search_posts', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error();
		}

		$q       = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
		$exclude = isset( $_REQUEST['exclude'] ) ? absint( wp_unslash( $_REQUEST['exclude'] ) ) : 0;
		$picked  = isset( $_REQUEST['picked'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['picked'] ) ) : '';
		$not_in  = array_filter( array_merge( array( $exclude ), array_map( 'absint', preg_split( '/[\s,]+/', $picked ) ) ) );

		$args = array(
			'post_type'           => array( 'post', 'inos_live_blog' ),
			'post_status'         => 'publish',
			'posts_per_page'      => 8,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		if ( $q ) {
			$args['s'] = $q;
		}
		if ( $not_in ) {
			$args['post__not_in'] = $not_in;
		}

		$posts = get_posts( $args );
		$out   = array();
		foreach ( $posts as $item ) {
			$out[] = array(
				'id'    => (int) $item->ID,
				'title' => wp_strip_all_tags( get_the_title( $item ) ),
			);
		}
		wp_send_json_success( $out );
	}
}
