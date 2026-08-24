<?php
/**
 * Infy News OS admin dashboard.
 *
 * @package InfyNewsOS
 *
 * @var array<string,mixed> $s Settings.
 */

defined( 'ABSPATH' ) || exit;

$stats   = class_exists( 'INOS_Admin' ) ? INOS_Admin::dashboard_stats() : array();
$look    = add_query_arg(
	array(
		'autofocus[section]' => 'inos_theme_look',
		'url'                => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
$header  = add_query_arg(
	array(
		'autofocus[panel]' => 'inos_header',
		'url'              => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
$layout  = add_query_arg(
	array(
		'autofocus[section]' => 'inos_theme_layout',
		'url'                => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
$preset  = class_exists( 'INOS_Presets' ) ? INOS_Presets::get() : array();
$preset_id = class_exists( 'INOS_Presets' ) ? INOS_Presets::current() : 'editorial';
?>
<div class="inos-dash">
	<section class="inos-dash__hero">
		<div>
			<h2><?php echo esc_html( isset( $s['publication_name'] ) && $s['publication_name'] ? $s['publication_name'] : get_bloginfo( 'name' ) ); ?></h2>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: plugin version, 2: site look label */
						__( 'Core %1$s · Look: %2$s', 'infy-news-os-core' ),
						INOS_CORE_VERSION,
						isset( $preset['label'] ) ? $preset['label'] : $preset_id
					)
				);
				?>
			</p>
		</div>
		<p class="inos-dash__hero-actions">
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>"><?php esc_html_e( 'Homepage builder', 'infy-news-os-core' ); ?></a>
			<a class="button" href="<?php echo esc_url( $look ); ?>"><?php esc_html_e( 'Customize', 'infy-news-os-core' ); ?></a>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View site', 'infy-news-os-core' ); ?></a>
		</p>
	</section>

	<section class="inos-dash__stats">
		<?php foreach ( $stats as $card ) : ?>
			<a class="inos-dash__stat" href="<?php echo esc_url( $card['url'] ); ?>">
				<strong><?php echo esc_html( $card['value'] ); ?></strong>
				<span><?php echo esc_html( $card['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</section>

	<div class="inos-dash__grid">
		<section class="inos-dash__card">
			<h3><?php esc_html_e( 'Stack health', 'infy-news-os-core' ); ?></h3>
			<ul class="inos-dash__health">
				<?php if ( class_exists( 'INOS_Setup' ) ) : ?>
					<?php foreach ( INOS_Setup::environment() as $row ) : ?>
						<li>
							<span class="inos-setup__pill <?php echo ! empty( $row['ok'] ) ? 'is-ok' : 'is-warn'; ?>">
								<?php echo ! empty( $row['ok'] ) ? esc_html__( 'Ready', 'infy-news-os-core' ) : esc_html__( 'Check', 'infy-news-os-core' ); ?>
							</span>
							<span><?php echo esc_html( $row['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
					<?php foreach ( INOS_Setup::companions() as $raw ) : ?>
						<?php
						if ( empty( $raw['required'] ) ) {
							continue;
						}
						$row   = INOS_Setup::companion_status( $raw );
						$state = isset( $row['state'] ) ? $row['state'] : 'missing';
						$pill  = 'active' === $state ? 'is-ok' : ( 'installed' === $state ? 'is-info' : 'is-warn' );
						?>
						<li>
							<span class="inos-setup__pill <?php echo esc_attr( $pill ); ?>"><?php echo esc_html( $state ); ?></span>
							<span><?php echo esc_html( $row['name'] ); ?></span>
							<?php if ( 'active' !== $state && ! empty( $row['action'] ) ) : ?>
								<span class="inos-dash__inline"><?php echo $row['action']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
			<p>
				<?php if ( class_exists( 'INOS_Setup' ) ) : ?>
					<a href="<?php echo esc_url( INOS_Setup::url( 'welcome' ) ); ?>"><?php esc_html_e( 'Open setup wizard', 'infy-news-os-core' ); ?></a>
				<?php endif; ?>
			</p>
		</section>

		<section class="inos-dash__card">
			<h3><?php esc_html_e( 'Design studio', 'infy-news-os-core' ); ?></h3>
			<p><?php esc_html_e( 'Astra-style Customizer panels for global tokens, header, footer, and blog — plus the magazine homepage builder.', 'infy-news-os-core' ); ?></p>
			<ul class="inos-dash__links">
				<li><a href="<?php echo esc_url( $look ); ?>"><?php esc_html_e( 'Site look & colors', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( $layout ); ?>"><?php esc_html_e( 'Container & type scale', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( $header ); ?>"><?php esc_html_e( 'Header & ticker', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>"><?php esc_html_e( 'Homepage builder', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=article-sidebar' ) ); ?>"><?php esc_html_e( 'Article sidebar', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=drawer' ) ); ?>"><?php esc_html_e( 'Mobile menu', 'infy-news-os-core' ); ?></a></li>
			</ul>
		</section>

		<section class="inos-dash__card">
			<h3><?php esc_html_e( 'Newsroom', 'infy-news-os-core' ); ?></h3>
			<ul class="inos-dash__links">
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Write an article', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=editorial' ) ); ?>"><?php esc_html_e( 'Editorial tools', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=seo' ) ); ?>"><?php esc_html_e( 'SEO / Search', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=google-news' ) ); ?>"><?php esc_html_e( 'Google News sitemap', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=amp-stories' ) ); ?>"><?php esc_html_e( 'AMP & Web Stories', 'infy-news-os-core' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=demo' ) ); ?>"><?php esc_html_e( 'Import demo content', 'infy-news-os-core' ); ?></a></li>
			</ul>
		</section>
	</div>
</div>
