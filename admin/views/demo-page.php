<?php
/**
 * Demo content tab.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

$imported = INOS_Demo::is_imported();
$state    = INOS_Demo::state();
$removed  = isset( $_GET['inos_demo_removed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$just_ok  = isset( $_GET['inos_demo_imported'] ) && '1' === (string) wp_unslash( $_GET['inos_demo_imported'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$just_err = isset( $_GET['inos_demo_error'] ) ? sanitize_text_field( wp_unslash( $_GET['inos_demo_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$post_n   = $imported ? count( array_unique( $state['posts'] ) ) : 0;
?>
<?php if ( $removed ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Demo content was removed. Homepage settings were restored.', 'infy-news-os-core' ); ?></p></div>
<?php endif; ?>
<?php if ( $just_ok ) : ?>
	<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Demo newsroom imported. Open the homepage to review it.', 'infy-news-os-core' ); ?></p></div>
<?php endif; ?>
<?php if ( $just_err ) : ?>
	<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $just_err ); ?></p></div>
<?php endif; ?>

<div class="inos-demo-panel">
	<h2><?php esc_html_e( 'Complete site demo', 'infy-news-os-core' ); ?></h2>
	<p><?php esc_html_e( 'Import a full fictional newsroom so you can judge the homepage, article pages, live blogs, Web Stories, mega menu, ticker, trending, and newsletter. Stories are dated within the last 48 hours so they appear in the Google News sitemap.', 'infy-news-os-core' ); ?></p>

	<ul class="inos-demo-panel__list">
		<li><?php esc_html_e( '6 sections (Technology, Business, Science, World, Culture, Opinion), extra topic tags, and policy pages', 'infy-news-os-core' ); ?></li>
		<li><?php esc_html_e( '3 reporter profiles with E-E-A-T bios', 'infy-news-os-core' ); ?></li>
		<li><?php esc_html_e( '28 articles with kickers, deks, datelines, headings, quotes, lists, Discover-sized images, in-story media, breaking, exclusive, sponsored, and a correction', 'infy-news-os-core' ); ?></li>
		<li><?php esc_html_e( '2 live blogs with timed updates', 'infy-news-os-core' ); ?></li>
		<li><?php esc_html_e( '6 official Web Stories (portrait AMP) when the Web Stories plugin is active, plus extra media-library stills', 'infy-news-os-core' ); ?></li>
		<li><?php esc_html_e( 'Primary, sections, and footer menus; sidebar widgets; homepage module stack; comments; and sample subscribers', 'infy-news-os-core' ); ?></li>
	</ul>

	<div id="inos-demo-progress" class="inos-demo-progress" hidden>
		<div class="inos-demo-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
			<span></span>
		</div>
		<p class="inos-demo-progress__label"><?php esc_html_e( 'Starting…', 'infy-news-os-core' ); ?></p>
	</div>

	<p id="inos-demo-done" class="inos-demo-done" hidden>
		<a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View homepage', 'infy-news-os-core' ); ?></a>
		<a class="button" href="<?php echo esc_url( add_query_arg( array( 'autofocus[section]' => 'inos_theme_light', 'url' => home_url( '/' ) ), admin_url( 'customize.php' ) ) ); ?>"><?php esc_html_e( 'Theme colors', 'infy-news-os-core' ); ?></a>
	</p>

	<p class="inos-demo-actions">
		<button type="button" class="button button-primary button-hero" id="inos-demo-import">
			<?php echo $imported ? esc_html__( 'Re-import missing demo items', 'infy-news-os-core' ) : esc_html__( 'Import complete site demo', 'infy-news-os-core' ); ?>
		</button>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inos-demo-fallback">
		<?php wp_nonce_field( 'inos_demo_import' ); ?>
		<input type="hidden" name="action" value="inos_demo_import" />
		<?php submit_button( __( 'Import without progress bar (if the button above fails)', 'infy-news-os-core' ), 'secondary', 'submit', false ); ?>
	</form>

	<?php if ( $imported ) : ?>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: story count, 2: datetime */
					__( 'Demo last completed %2$s (%1$d stories). Re-import skips existing slugs. Remove deletes only tracked demo items, including Web Stories and media.', 'infy-news-os-core' ),
					$post_n,
					$state['imported_at']
				)
			);
			?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Delete demo stories, authors, menus, and images? Your own content is kept.', 'infy-news-os-core' ) ); ?>');">
			<?php wp_nonce_field( 'inos_demo_remove' ); ?>
			<input type="hidden" name="action" value="inos_demo_remove" />
			<?php submit_button( __( 'Remove demo content', 'infy-news-os-core' ), 'delete', 'submit', false ); ?>
		</form>
	<?php endif; ?>

	<p class="description"><?php esc_html_e( 'Demo copy is fictional. It is not real reporting. Featured images are generated locally (1200×675 for articles, 720×1280 for Web Stories) so they meet Discover and Stories size guidance.', 'infy-news-os-core' ); ?></p>
</div>
