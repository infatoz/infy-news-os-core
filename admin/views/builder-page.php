<?php
/**
 * Homepage builder canvas.
 *
 * @package InfyNewsOS
 *
 * @var array<string,mixed> $s Settings.
 */

defined( 'ABSPATH' ) || exit;

$modules = class_exists( 'INOS_Home_Builder' ) ? INOS_Home_Builder::all() : array();
$preview = add_query_arg(
	array(
		'autofocus[panel]' => 'inos_homepage',
		'url'              => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
?>
<div class="inos-builder" data-inos-builder>
	<p class="inos-builder__lead">
		<?php esc_html_e( 'Stack blocks like a magazine theme: drag to reorder, duplicate, and open a block for query, layout, spacing, background, and device visibility. Global colors live in Appearance → Customize.', 'infy-news-os-core' ); ?>
		<a href="<?php echo esc_url( $preview ); ?>"><?php esc_html_e( 'Open live preview', 'infy-news-os-core' ); ?></a>
	</p>

	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Homepage sidebar', 'infy-news-os-core' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="inos[home_sidebar]" value="1" <?php checked( ! empty( $s['home_sidebar'] ), true ); ?> />
					<?php esc_html_e( 'Show the sidebar (widgets + sidebar ad) beside homepage blocks', 'infy-news-os-core' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Unique stories', 'infy-news-os-core' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="inos[home_unique_posts]" value="1" <?php checked( ! empty( $s['home_unique_posts'] ), true ); ?> />
					<?php esc_html_e( 'Honor “unique” on blocks so a story is not reused lower on the page', 'infy-news-os-core' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><label for="inos_theme_preset"><?php esc_html_e( 'Site look', 'infy-news-os-core' ); ?></label></th>
			<td>
				<select id="inos_theme_preset" name="inos[theme_preset]">
					<?php
					$looks        = class_exists( 'INOS_Presets' ) ? INOS_Presets::all() : array();
					$current_look = isset( $s['theme_preset'] ) ? $s['theme_preset'] : 'editorial';
					foreach ( $looks as $look_id => $look ) :
						?>
						<option value="<?php echo esc_attr( $look_id ); ?>" <?php selected( $current_look, $look_id ); ?>><?php echo esc_html( $look['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Also in Appearance → Customize → Infy News OS → Site look. Changing the look applies that palette, fonts, and chrome.', 'infy-news-os-core' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="inos_home_title_style"><?php esc_html_e( 'Block title style', 'infy-news-os-core' ); ?></label></th>
			<td>
				<select id="inos_home_title_style" name="inos[home_title_style]">
					<option value="bar" <?php selected( $s['home_title_style'], 'bar' ); ?>><?php esc_html_e( 'Bar (underline)', 'infy-news-os-core' ); ?></option>
					<option value="underline" <?php selected( $s['home_title_style'], 'underline' ); ?>><?php esc_html_e( 'Accent underline', 'infy-news-os-core' ); ?></option>
					<option value="boxed" <?php selected( $s['home_title_style'], 'boxed' ); ?>><?php esc_html_e( 'Boxed label', 'infy-news-os-core' ); ?></option>
					<option value="pill" <?php selected( $s['home_title_style'], 'pill' ); ?>><?php esc_html_e( 'Pill', 'infy-news-os-core' ); ?></option>
					<option value="minimal" <?php selected( $s['home_title_style'], 'minimal' ); ?>><?php esc_html_e( 'Minimal', 'infy-news-os-core' ); ?></option>
				</select>
			</td>
		</tr>
	</table>

	<div class="inos-builder__toolbar">
		<label class="screen-reader-text" for="inos-builder-add"><?php esc_html_e( 'Add block', 'infy-news-os-core' ); ?></label>
		<select id="inos-builder-add">
			<option value=""><?php esc_html_e( 'Add a block…', 'infy-news-os-core' ); ?></option>
			<?php if ( class_exists( 'INOS_Home_Builder' ) ) : ?>
				<?php foreach ( INOS_Home_Builder::types() as $type => $meta ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $meta['label'] ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<button type="button" class="button" id="inos-builder-add-btn"><?php esc_html_e( 'Add', 'infy-news-os-core' ); ?></button>
		<button type="button" class="button" id="inos-builder-reset"><?php esc_html_e( 'Reset to current homepage layout', 'infy-news-os-core' ); ?></button>
	</div>

	<ul class="inos-builder__list" id="inos-builder-list"></ul>
	<textarea name="inos_home_modules" id="inos-home-modules" class="inos-builder__json" hidden><?php echo esc_textarea( wp_json_encode( $modules ) ); ?></textarea>
</div>
