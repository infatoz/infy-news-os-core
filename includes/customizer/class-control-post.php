<?php
/**
 * Customizer post picker.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

if ( class_exists( 'INOS_Customize_Post_Control' ) ) {
	return;
}

/**
 * Dropdown of recent posts.
 */
class INOS_Customize_Post_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'inos_post';

	/**
	 * Render select.
	 */
	public function render_content() {
		$posts = get_posts(
			array(
				'post_type'      => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		?>
		<label>
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<select <?php $this->link(); ?>>
				<option value="0"><?php esc_html_e( 'Latest published', 'infy-news-os-core' ); ?></option>
				<?php foreach ( $posts as $post ) : ?>
					<option value="<?php echo esc_attr( (string) $post->ID ); ?>" <?php selected( (int) $this->value(), (int) $post->ID ); ?>>
						<?php echo esc_html( wp_trim_words( $post->post_title, 12, '…' ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php
	}
}
