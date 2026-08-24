<?php
/**
 * Setup wizard.
 *
 * @package InfyNewsOS
 *
 * @var string               $step  Current step.
 * @var array<string,string> $steps Step labels.
 * @var array<string,mixed>  $s     Settings.
 */

defined( 'ABSPATH' ) || exit;

$ids        = array_keys( $steps );
$index      = array_search( $step, $ids, true );
$index      = false === $index ? 0 : (int) $index;
$next_id    = isset( $ids[ $index + 1 ] ) ? $ids[ $index + 1 ] : '';
$prev_id    = $index > 0 ? $ids[ $index - 1 ] : '';
$checks     = class_exists( 'INOS_Setup' ) ? INOS_Setup::environment() : array();
$items      = class_exists( 'INOS_Setup' ) ? INOS_Setup::companions() : array();
$presets    = class_exists( 'INOS_Presets' ) ? INOS_Presets::all() : array();
$pretty     = (string) get_option( 'permalink_structure' );
$static_on  = ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) );
$look_url   = add_query_arg(
	array(
		'autofocus[section]' => 'inos_theme_look',
		'url'                => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
$state_labels = array(
	'active'    => __( 'Active', 'infy-news-os-core' ),
	'installed' => __( 'Installed', 'infy-news-os-core' ),
	'missing'   => __( 'Missing', 'infy-news-os-core' ),
);
?>
<div class="wrap inos-wrap inos-setup">
	<h1><?php esc_html_e( 'Infy News OS setup', 'infy-news-os-core' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Four steps: confirm the stack, install companion plugins, pick a look, and turn on news essentials.', 'infy-news-os-core' ); ?></p>

	<ol class="inos-setup__steps">
		<?php foreach ( $ids as $i => $id ) : ?>
			<li class="<?php echo $id === $step ? 'is-current' : ( $i < $index ? 'is-done' : '' ); ?>">
				<?php if ( $id !== $step ) : ?>
					<a href="<?php echo esc_url( INOS_Setup::url( $id ) ); ?>"><?php echo esc_html( $steps[ $id ] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $steps[ $id ] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php if ( 'welcome' === $step ) : ?>
		<div class="inos-setup__panel">
			<h2><?php esc_html_e( 'Welcome', 'infy-news-os-core' ); ?></h2>
			<p><?php esc_html_e( 'Infy News OS Core is the engine. The Infy News OS theme is the public site. AMP and Web Stories stay as the official WordPress.org plugins — Core does not vendor them.', 'infy-news-os-core' ); ?></p>
			<table class="widefat striped inos-setup__table">
				<tbody>
					<?php foreach ( $checks as $row ) : ?>
						<tr>
							<th><?php echo esc_html( $row['label'] ); ?></th>
							<td>
								<span class="inos-setup__pill <?php echo ! empty( $row['ok'] ) ? 'is-ok' : 'is-warn'; ?>">
									<?php echo ! empty( $row['ok'] ) ? esc_html__( 'Ready', 'infy-news-os-core' ) : esc_html__( 'Action needed', 'infy-news-os-core' ); ?>
								</span>
								<span class="inos-setup__detail"><?php echo esc_html( $row['detail'] ); ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="inos-setup__actions">
				<a class="button button-primary button-hero" href="<?php echo esc_url( INOS_Setup::url( 'plugins' ) ); ?>"><?php esc_html_e( 'Continue', 'infy-news-os-core' ); ?></a>
				<?php INOS_Setup::skip_button(); ?>
			</p>
		</div>

	<?php elseif ( 'plugins' === $step ) : ?>
		<div class="inos-setup__panel">
			<h2><?php esc_html_e( 'Required for a compatible news stack', 'infy-news-os-core' ); ?></h2>
			<p><?php esc_html_e( 'Core is already active. These companions are what the theme and plugin expect so AMP articles, Web Stories, and the public chrome stay in sync. SEO packs such as Yoast or Rank Math are not required — Core ships titles, schema, and news sitemaps.', 'infy-news-os-core' ); ?></p>

			<?php
			foreach ( array( true, false ) as $need_required ) :
				$heading = $need_required
					? __( 'Required', 'infy-news-os-core' )
					: __( 'Recommended', 'infy-news-os-core' );
				?>
				<h3><?php echo esc_html( $heading ); ?></h3>
				<table class="widefat striped inos-setup__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plugin / theme', 'infy-news-os-core' ); ?></th>
							<th><?php esc_html_e( 'Why', 'infy-news-os-core' ); ?></th>
							<th><?php esc_html_e( 'Status', 'infy-news-os-core' ); ?></th>
							<th><?php esc_html_e( 'Action', 'infy-news-os-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $raw ) : ?>
							<?php
							if ( (bool) $raw['required'] !== $need_required ) {
								continue;
							}
							$row   = INOS_Setup::companion_status( $raw );
							$state = isset( $row['state'] ) ? $row['state'] : 'missing';
							$pill  = 'active' === $state ? 'is-ok' : ( 'installed' === $state ? 'is-info' : 'is-warn' );
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $row['name'] ); ?></strong>
									<?php if ( ! empty( $row['url'] ) ) : ?>
										<br /><a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Details', 'infy-news-os-core' ); ?></a>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $row['why'] ); ?></td>
								<td>
									<span class="inos-setup__pill <?php echo esc_attr( $pill ); ?>">
										<?php echo esc_html( isset( $state_labels[ $state ] ) ? $state_labels[ $state ] : $state ); ?>
									</span>
								</td>
								<td>
									<?php
									if ( 'active' === $state ) {
										echo '&mdash;';
									} else {
										echo $row['action']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

			<p class="inos-setup__actions">
				<?php if ( $prev_id ) : ?>
					<a class="button" href="<?php echo esc_url( INOS_Setup::url( $prev_id ) ); ?>"><?php esc_html_e( 'Back', 'infy-news-os-core' ); ?></a>
				<?php endif; ?>
				<a class="button button-primary button-hero" href="<?php echo esc_url( INOS_Setup::url( 'options' ) ); ?>"><?php esc_html_e( 'Continue', 'infy-news-os-core' ); ?></a>
				<?php INOS_Setup::skip_button(); ?>
			</p>
		</div>

	<?php elseif ( 'options' === $step ) : ?>
		<form method="post" action="<?php echo esc_url( INOS_Setup::url( 'options' ) ); ?>" class="inos-setup__panel">
			<?php wp_nonce_field( 'inos_setup', 'inos_setup_nonce' ); ?>
			<input type="hidden" name="inos_setup_action" value="save" />
			<h2><?php esc_html_e( 'Quick options', 'infy-news-os-core' ); ?></h2>
			<p><?php esc_html_e( 'These are the same settings as Infy News OS → Settings and the Customizer. You can change them later.', 'infy-news-os-core' ); ?></p>

			<table class="form-table" role="presentation">
				<tr>
					<th><label for="inos_setup_publication"><?php esc_html_e( 'Publication name', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="inos_setup_publication" name="inos[publication_name]" value="<?php echo esc_attr( isset( $s['publication_name'] ) ? $s['publication_name'] : get_bloginfo( 'name' ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Site look', 'infy-news-os-core' ); ?></th>
					<td>
						<div class="inos-setup__presets">
							<?php foreach ( $presets as $pid => $preset ) : ?>
								<label class="inos-setup__preset">
									<input type="radio" name="inos[theme_preset]" value="<?php echo esc_attr( $pid ); ?>" <?php checked( isset( $s['theme_preset'] ) ? $s['theme_preset'] : 'editorial', $pid ); ?> />
									<strong><?php echo esc_html( $preset['label'] ); ?></strong>
									<span><?php echo esc_html( $preset['description'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Chrome', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[sticky_header_desktop]" value="1" <?php checked( ! empty( $s['sticky_header_desktop'] ) ); ?> /> <?php esc_html_e( 'Sticky header on desktop', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sticky_header_mobile]" value="1" <?php checked( ! empty( $s['sticky_header_mobile'] ) ); ?> /> <?php esc_html_e( 'Sticky header on mobile', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_breaking_ticker]" value="1" <?php checked( ! empty( $s['show_breaking_ticker'] ) ); ?> /> <?php esc_html_e( 'Breaking news ticker', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_subscribe_cta]" value="1" <?php checked( ! empty( $s['show_subscribe_cta'] ) ); ?> /> <?php esc_html_e( 'Subscribe button in the header', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_home_newsletter]" value="1" <?php checked( ! empty( $s['show_home_newsletter'] ) ); ?> /> <?php esc_html_e( 'Homepage newsletter block', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Search and schema', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_seo]" value="1" <?php checked( ! empty( $s['enable_seo'] ) ); ?> /> <?php esc_html_e( 'Built-in titles, canonicals, and Open Graph', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[enable_news_sitemap]" value="1" <?php checked( ! empty( $s['enable_news_sitemap'] ) ); ?> /> <?php esc_html_e( 'Google News sitemap', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[enable_schema]" value="1" <?php checked( ! empty( $s['enable_schema'] ) ); ?> /> <?php esc_html_e( 'NewsArticle JSON-LD', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Reading', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[show_progress_bar]" value="1" <?php checked( ! empty( $s['show_progress_bar'] ) ); ?> /> <?php esc_html_e( 'Article progress bar', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[article_reader_tools]" value="1" <?php checked( ! empty( $s['article_reader_tools'] ) ); ?> /> <?php esc_html_e( 'Reader tools (text size, share)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sticky_share]" value="1" <?php checked( ! empty( $s['sticky_share'] ) ); ?> /> <?php esc_html_e( 'Sticky share bar', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_reading_time]" value="1" <?php checked( ! empty( $s['show_reading_time'] ) ); ?> /> <?php esc_html_e( 'Reading time', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<?php if ( ! $pretty ) : ?>
					<tr>
						<th><?php esc_html_e( 'Permalinks', 'infy-news-os-core' ); ?></th>
						<td>
							<label><input type="checkbox" name="inos_pretty_permalinks" value="1" checked /> <?php esc_html_e( 'Switch to /%category%/%postname%/ (recommended for news and AMP)', 'infy-news-os-core' ); ?></label>
						</td>
					</tr>
				<?php endif; ?>
				<?php if ( ! $static_on ) : ?>
					<tr>
						<th><?php esc_html_e( 'Front page', 'infy-news-os-core' ); ?></th>
						<td>
							<label><input type="checkbox" name="inos_static_home" value="1" /> <?php esc_html_e( 'Create a Home page and use it as the static front page (optional)', 'infy-news-os-core' ); ?></label>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<p class="inos-setup__actions">
				<?php if ( $prev_id ) : ?>
					<a class="button" href="<?php echo esc_url( INOS_Setup::url( $prev_id ) ); ?>"><?php esc_html_e( 'Back', 'infy-news-os-core' ); ?></a>
				<?php endif; ?>
				<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save and finish', 'infy-news-os-core' ); ?></button>
				<?php INOS_Setup::skip_button(); ?>
			</p>
		</form>

	<?php else : ?>
		<div class="inos-setup__panel">
			<h2><?php esc_html_e( 'You are set', 'infy-news-os-core' ); ?></h2>
			<p><?php esc_html_e( 'Open any of these next — the wizard stays under Infy News OS → Setup wizard if you need it again.', 'infy-news-os-core' ); ?></p>
			<ul class="inos-setup__next">
				<li><a class="button button-primary" href="<?php echo esc_url( $look_url ); ?>"><?php esc_html_e( 'Customize site look', 'infy-news-os-core' ); ?></a></li>
				<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>"><?php esc_html_e( 'Homepage builder', 'infy-news-os-core' ); ?></a></li>
				<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=demo' ) ); ?>"><?php esc_html_e( 'Import demo content', 'infy-news-os-core' ); ?></a></li>
				<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=amp-stories' ) ); ?>"><?php esc_html_e( 'AMP and Stories', 'infy-news-os-core' ); ?></a></li>
				<li><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings' ) ); ?>"><?php esc_html_e( 'All settings', 'infy-news-os-core' ); ?></a></li>
			</ul>
			<form method="post" action="<?php echo esc_url( INOS_Setup::url( 'done' ) ); ?>">
				<?php wp_nonce_field( 'inos_setup', 'inos_setup_nonce' ); ?>
				<input type="hidden" name="inos_setup_action" value="finish" />
				<p class="inos-setup__actions">
					<button type="submit" class="button"><?php esc_html_e( 'Close wizard', 'infy-news-os-core' ); ?></button>
				</p>
			</form>
		</div>
	<?php endif; ?>
</div>
