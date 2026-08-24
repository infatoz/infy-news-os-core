<?php
/**
 * Article details sidebar builder.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

$modules = class_exists( 'INOS_Article_Sidebar' ) ? INOS_Article_Sidebar::all() : array();
$widgets = admin_url( 'widgets.php' );
?>
<div class="inos-builder" data-inos-builder>
	<p class="inos-builder__lead">
		<?php esc_html_e( 'Build the article details rail: add, edit, reorder, or remove blocks. The default stack is the sidebar ad, trending, and WordPress widgets. AMP article pages still hide this rail.', 'infy-news-os-core' ); ?>
		<a href="<?php echo esc_url( $widgets ); ?>"><?php esc_html_e( 'Edit WordPress widgets', 'infy-news-os-core' ); ?></a>
	</p>

	<div class="inos-builder__toolbar">
		<label class="screen-reader-text" for="inos-builder-add"><?php esc_html_e( 'Add block', 'infy-news-os-core' ); ?></label>
		<select id="inos-builder-add" data-inos-builder-add>
			<option value=""><?php esc_html_e( 'Add a block…', 'infy-news-os-core' ); ?></option>
			<?php if ( class_exists( 'INOS_Article_Sidebar' ) ) : ?>
				<?php foreach ( INOS_Article_Sidebar::types() as $type => $meta ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $meta['label'] ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<button type="button" class="button" id="inos-builder-add-btn" data-inos-builder-add-btn><?php esc_html_e( 'Add', 'infy-news-os-core' ); ?></button>
		<button type="button" class="button" id="inos-builder-reset" data-inos-builder-reset><?php esc_html_e( 'Reset to default article sidebar', 'infy-news-os-core' ); ?></button>
	</div>

	<ul class="inos-builder__list" id="inos-builder-list" data-inos-builder-list></ul>
	<textarea name="inos_article_sidebar" id="inos-article-sidebar-modules" class="inos-builder__json" hidden><?php echo esc_textarea( wp_json_encode( $modules ) ); ?></textarea>
</div>
