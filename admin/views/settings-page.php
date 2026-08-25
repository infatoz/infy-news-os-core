<?php
/**
 * Settings form.
 *
 * @package InfyNewsOS
 *
 * @var string               $tab  Current tab.
 * @var array<string,string> $tabs Tabs.
 * @var array<string,mixed>  $s    Settings.
 */

defined( 'ABSPATH' ) || exit;

$permalink_structure = get_option( 'permalink_structure' );
$groups              = class_exists( 'INOS_Admin' ) ? INOS_Admin::tab_groups() : array();
$look_url            = add_query_arg(
	array(
		'autofocus[section]' => 'inos_theme_look',
		'url'                => home_url( '/' ),
	),
	admin_url( 'customize.php' )
);
?>
<div class="wrap inos-wrap inos-app">
	<header class="inos-app__head">
		<div>
			<h1><?php esc_html_e( 'Infy News OS', 'infy-news-os-core' ); ?></h1>
			<p class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: plugin version */
						__( 'Newsroom control panel · %s', 'infy-news-os-core' ),
						INOS_CORE_VERSION
					)
				);
				?>
			</p>
		</div>
		<p class="inos-app__head-actions">
			<?php if ( class_exists( 'INOS_Setup' ) ) : ?>
				<a class="button" href="<?php echo esc_url( INOS_Setup::url( 'welcome' ) ); ?>"><?php esc_html_e( 'Setup wizard', 'infy-news-os-core' ); ?></a>
			<?php endif; ?>
			<a class="button" href="<?php echo esc_url( $look_url ); ?>"><?php esc_html_e( 'Customize', 'infy-news-os-core' ); ?></a>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View site', 'infy-news-os-core' ); ?></a>
		</p>
	</header>

	<div class="inos-app__layout">
		<nav class="inos-app__nav" aria-label="<?php esc_attr_e( 'Infy News OS', 'infy-news-os-core' ); ?>">
			<?php foreach ( $groups as $group ) : ?>
				<p class="inos-app__nav-label"><?php echo esc_html( $group['label'] ); ?></p>
				<?php foreach ( $group['tabs'] as $id ) : ?>
					<?php if ( empty( $tabs[ $id ] ) ) { continue; } ?>
					<a class="inos-app__nav-link<?php echo $tab === $id ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=' . $id ) ); ?>"><?php echo esc_html( $tabs[ $id ] ); ?></a>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</nav>
		<div class="inos-app__main">
	<nav class="nav-tab-wrapper inos-app__tabs">
		<?php foreach ( $tabs as $id => $label ) : ?>
			<a class="nav-tab <?php echo $tab === $id ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=' . $id ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php if ( 'dashboard' === $tab ) : ?>
		<?php include INOS_CORE_PATH . 'admin/views/dashboard-page.php'; ?>
	<?php elseif ( 'demo' === $tab ) : ?>
		<?php include INOS_CORE_PATH . 'admin/views/demo-page.php'; ?>
	<?php else : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=' . $tab ) ); ?>">
		<?php wp_nonce_field( 'inos_save_settings', 'inos_settings_nonce' ); ?>
		<?php
		$tab_checks = array(
			'seo'         => array( 'enable_seo', 'max_image_preview_large', 'og_enabled', 'index_category_archives', 'index_tag_archives', 'index_author_archives', 'index_type_archives', 'noindex_search', 'noindex_date_archives', 'noindex_empty_archives' ),
			'google-news' => array( 'enable_news_sitemap', 'enable_googlebot_news', 'enable_google_news_feed', 'rss_full_content', 'disable_core_sitemaps', 'sitemap_include_tags', 'sitemap_include_authors', 'enable_news_keywords_meta' ),
			'tracking'    => array( 'enable_gtm', 'enable_share_utm', 'enable_preferred_source' ),
			'schema'      => array( 'enable_schema', 'enable_speakable' ),
			'editorial'   => array( 'enable_custom_statuses', 'show_reading_time', 'show_view_count', 'show_progress_bar', 'sticky_header_desktop', 'sticky_header_mobile', 'sticky_share', 'article_reader_tools', 'mid_article_also_read' ),
			'ads'         => array( 'ad_header_enable', 'ad_below_ticker_enable', 'ad_in_article_enable', 'ad_sidebar_enable', 'ad_between_cards_enable', 'ad_sticky_mobile_enable', 'ad_footer_enable' ),
			'newsletter'  => array( 'enable_newsletter', 'newsletter_store_local' ),
			'push'        => array( 'enable_web_push' ),
			'performance' => array( 'disable_emojis', 'disable_embeds' ),
			'images'      => array( 'enable_lazy_load', 'skip_lcp_lazy', 'preload_lcp_image', 'lazy_iframes', 'image_webp', 'auto_image_alt', 'enable_image_sitemap', 'schema_multi_aspect', 'keep_original_images' ),
			'homepage'    => array( 'show_also_read', 'related_load_more', 'show_hero', 'show_latest', 'show_trending', 'show_home_newsletter', 'show_home_ads', 'show_home_web_stories', 'show_breaking_ticker', 'show_subscribe_cta' ),
			'builder'     => array( 'home_sidebar', 'home_unique_posts' ),
			'labels'      => array( 'show_theme_credit' ),
		);
		if ( ! empty( $tab_checks[ $tab ] ) ) {
			echo '<input type="hidden" name="inos_checkbox_keys" value="' . esc_attr( implode( ',', $tab_checks[ $tab ] ) ) . '" />';
		}
		?>
		<?php if ( 'builder' === $tab ) : ?>
			<?php include INOS_CORE_PATH . 'admin/views/builder-page.php'; ?>
		<?php elseif ( 'article-sidebar' === $tab ) : ?>
			<?php include INOS_CORE_PATH . 'admin/views/article-sidebar-page.php'; ?>
		<?php elseif ( 'drawer' === $tab ) : ?>
			<?php include INOS_CORE_PATH . 'admin/views/drawer-page.php'; ?>
		<?php else : ?>
		<?php if ( 'general' === $tab ) : ?>
			<div class="inos-quickstart">
				<div class="inos-quickstart__intro">
					<h2><?php esc_html_e( 'Quick start', 'infy-news-os-core' ); ?></h2>
					<p><?php esc_html_e( 'Open the setup wizard, install companions Core expects, or jump to the look and homepage tools.', 'infy-news-os-core' ); ?></p>
				</div>
				<p class="inos-quickstart__links">
					<?php if ( class_exists( 'INOS_Setup' ) ) : ?>
						<a class="button button-primary" href="<?php echo esc_url( INOS_Setup::url( 'welcome' ) ); ?>"><?php esc_html_e( 'Setup wizard', 'infy-news-os-core' ); ?></a>
						<a class="button" href="<?php echo esc_url( INOS_Setup::url( 'plugins' ) ); ?>"><?php esc_html_e( 'Required plugins', 'infy-news-os-core' ); ?></a>
					<?php endif; ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>"><?php esc_html_e( 'Homepage builder', 'infy-news-os-core' ); ?></a>
					<?php
					$look_url = add_query_arg(
						array(
							'autofocus[section]' => 'inos_theme_look',
							'url'                => home_url( '/' ),
						),
						admin_url( 'customize.php' )
					);
					?>
					<a class="button" href="<?php echo esc_url( $look_url ); ?>"><?php esc_html_e( 'Site look', 'infy-news-os-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=amp-stories' ) ); ?>"><?php esc_html_e( 'AMP & Stories', 'infy-news-os-core' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=demo' ) ); ?>"><?php esc_html_e( 'Import demo', 'infy-news-os-core' ); ?></a>
				</p>
				<?php if ( class_exists( 'INOS_Setup' ) ) : ?>
					<ul class="inos-quickstart__status">
						<?php foreach ( INOS_Setup::companions() as $raw ) : ?>
							<?php
							if ( empty( $raw['required'] ) ) {
								continue;
							}
							$row   = INOS_Setup::companion_status( $raw );
							$state = isset( $row['state'] ) ? $row['state'] : 'missing';
							$pill  = 'active' === $state ? 'is-ok' : ( 'installed' === $state ? 'is-info' : 'is-warn' );
							$labels = array(
								'active'    => __( 'Active', 'infy-news-os-core' ),
								'installed' => __( 'Installed', 'infy-news-os-core' ),
								'missing'   => __( 'Missing', 'infy-news-os-core' ),
							);
							?>
							<li>
								<span class="inos-setup__pill <?php echo esc_attr( $pill ); ?>"><?php echo esc_html( isset( $labels[ $state ] ) ? $labels[ $state ] : $state ); ?></span>
								<strong><?php echo esc_html( $row['name'] ); ?></strong>
								<?php if ( 'active' !== $state && ! empty( $row['action'] ) ) : ?>
									<span class="inos-quickstart__action"><?php echo $row['action']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<?php if ( 'general' === $tab ) : ?>
				<tr>
					<th><label for="inos_publication_name"><?php esc_html_e( 'Publication name', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_publication_name" name="inos[publication_name]" value="<?php echo esc_attr( $s['publication_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_publication_language"><?php esc_html_e( 'Language (ISO 639-1)', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="small-text" id="inos_publication_language" name="inos[publication_language]" value="<?php echo esc_attr( $s['publication_language'] ); ?>" />
						<p class="description">
							<?php esc_html_e( 'Used in schema and the public HTML lang attribute (for example en, hi, kn, ar). Translate button labels on the Language / Labels tab — you do not need a translation file.', 'infy-news-os-core' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_text_direction"><?php esc_html_e( 'Text direction', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_text_direction" name="inos[text_direction]">
							<option value="auto" <?php selected( $s['text_direction'], 'auto' ); ?>><?php esc_html_e( 'Auto (WordPress locale or RTL language code)', 'infy-news-os-core' ); ?></option>
							<option value="ltr" <?php selected( $s['text_direction'], 'ltr' ); ?>><?php esc_html_e( 'Left to right', 'infy-news-os-core' ); ?></option>
							<option value="rtl" <?php selected( $s['text_direction'], 'rtl' ); ?>><?php esc_html_e( 'Right to left', 'infy-news-os-core' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="inos_datetime_display"><?php esc_html_e( 'Datetime display', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_datetime_display" name="inos[datetime_display]">
							<option value="site" <?php selected( $s['datetime_display'], 'site' ); ?>><?php esc_html_e( 'Site timezone (WordPress settings)', 'infy-news-os-core' ); ?></option>
							<option value="utc" <?php selected( $s['datetime_display'], 'utc' ); ?>><?php esc_html_e( 'UTC', 'infy-news-os-core' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Permalinks', 'infy-news-os-core' ); ?></th>
					<td>
						<p><?php esc_html_e( 'Recommended for news: /%category%/%postname%/', 'infy-news-os-core' ); ?></p>
						<p>
							<code><?php echo esc_html( $permalink_structure ? $permalink_structure : __( 'Plain', 'infy-news-os-core' ) ); ?></code>
							— <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Change permalinks', 'infy-news-os-core' ); ?></a>
						</p>
					</td>
				</tr>
			<?php elseif ( 'labels' === $tab ) : ?>
				<?php
				$label_stored = class_exists( 'INOS_Labels' ) ? INOS_Labels::stored() : array();
				$label_hints  = class_exists( 'INOS_Labels' ) ? INOS_Labels::hints() : array();
				$wp_lang      = get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : 'en_US';
				?>
				<tr>
					<th><?php esc_html_e( 'How this works', 'infy-news-os-core' ); ?></th>
					<td>
						<p><?php esc_html_e( 'Type chrome text in the language of your newsroom. Leave a field blank to keep the English (or translated) default. Article headlines and body stay in whatever language you publish.', 'infy-news-os-core' ); ?></p>
						<p class="description">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: current WordPress locale */
									__( 'WordPress admin language is %s. You can keep that in English and still run a Hindi, Kannada, or Arabic public site from this tab.', 'infy-news-os-core' ),
									$wp_lang
								)
							);
							?>
							<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>"><?php esc_html_e( 'Site language', 'infy-news-os-core' ); ?></a>
						</p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Fallback headings', 'infy-news-os-core' ); ?></th>
					<td>
						<p class="description"><?php esc_html_e( 'Used when a homepage builder block leaves its title blank.', 'infy-news-os-core' ); ?></p>
						<p>
							<label for="inos_latest_title"><?php esc_html_e( 'Latest', 'infy-news-os-core' ); ?></label><br />
							<input type="text" class="regular-text" id="inos_latest_title" name="inos[latest_title]" value="<?php echo esc_attr( $s['latest_title'] ); ?>" />
						</p>
						<p>
							<label for="inos_trending_title"><?php esc_html_e( 'Trending', 'infy-news-os-core' ); ?></label><br />
							<input type="text" class="regular-text" id="inos_trending_title" name="inos[trending_title]" value="<?php echo esc_attr( $s['trending_title'] ); ?>" />
						</p>
					</td>
				</tr>
				<?php if ( class_exists( 'INOS_Labels' ) ) : ?>
					<?php foreach ( INOS_Labels::groups() as $group_id => $group ) : ?>
						<tr>
							<th><?php echo esc_html( $group['title'] ); ?></th>
							<td class="inos-labels-group" data-group="<?php echo esc_attr( $group_id ); ?>">
								<?php foreach ( $group['keys'] as $key => $default ) : ?>
									<p>
										<label for="inos_label_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $default ); ?></label><br />
										<input type="text" class="regular-text" id="inos_label_<?php echo esc_attr( $key ); ?>" name="inos_labels[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( isset( $label_stored[ $key ] ) ? $label_stored[ $key ] : '' ); ?>" placeholder="<?php echo esc_attr( $default ); ?>" />
										<?php if ( ! empty( $label_hints[ $key ] ) ) : ?>
											<br /><span class="description"><?php echo esc_html( $label_hints[ $key ] ); ?></span>
										<?php endif; ?>
									</p>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'Theme credit', 'infy-news-os-core' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="inos[show_theme_credit]" value="1" <?php checked( ! empty( $s['show_theme_credit'] ), true ); ?> />
							<?php esc_html_e( 'Show “Theme by Infatoz Technologies LLP” in the footer (uses the string above if you translated it).', 'infy-news-os-core' ); ?>
						</label>
					</td>
				</tr>
			<?php elseif ( 'publisher' === $tab ) : ?>
				<tr>
					<th><label for="inos_org_name"><?php esc_html_e( 'Organization name', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_org_name" name="inos[org_name]" value="<?php echo esc_attr( $s['org_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_legal_name"><?php esc_html_e( 'Legal name', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_legal_name" name="inos[legal_name]" value="<?php echo esc_attr( $s['legal_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Publisher logo', 'infy-news-os-core' ); ?></th>
					<td>
						<input type="hidden" id="inos_logo_id" name="inos[logo_id]" value="<?php echo esc_attr( (string) $s['logo_id'] ); ?>" />
						<button type="button" class="button" id="inos-logo-select"><?php esc_html_e( 'Select logo (600×60 or larger)', 'infy-news-os-core' ); ?></button>
						<?php if ( $s['logo_id'] ) : ?>
							<p><?php echo wp_get_attachment_image( (int) $s['logo_id'], 'medium' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><label for="inos_sameas"><?php esc_html_e( 'sameAs URLs', 'infy-news-os-core' ); ?></label></th>
					<td><textarea class="large-text" rows="4" id="inos_sameas" name="inos[sameas]"><?php echo esc_textarea( $s['sameas'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Official social / Wikipedia URLs, one per line. These also power the Social profiles block in the mobile menu.', 'infy-news-os-core' ); ?></p></td>
				</tr>
				<tr>
					<th><label for="inos_contact_email"><?php esc_html_e( 'Contact email', 'infy-news-os-core' ); ?></label></th>
					<td><input type="email" class="regular-text" id="inos_contact_email" name="inos[contact_email]" value="<?php echo esc_attr( $s['contact_email'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_contact_page_url"><?php esc_html_e( 'Contact page URL', 'infy-news-os-core' ); ?></label></th>
					<td><input type="url" class="regular-text" id="inos_contact_page_url" name="inos[contact_page_url]" value="<?php echo esc_attr( $s['contact_page_url'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_founding_date"><?php esc_html_e( 'Founding date', 'infy-news-os-core' ); ?></label></th>
					<td><input type="date" id="inos_founding_date" name="inos[founding_date]" value="<?php echo esc_attr( $s['founding_date'] ); ?>" /></td>
				</tr>
			<?php elseif ( 'seo' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Enable SEO layer', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_seo]" value="1" <?php checked( $s['enable_seo'], 1 ); ?> /> <?php esc_html_e( 'Output titles, robots, canonical, Open Graph', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Google Discover images', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[max_image_preview_large]" value="1" <?php checked( $s['max_image_preview_large'], 1 ); ?> /> <?php esc_html_e( 'max-image-preview:large (required for large Discover images)', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Open Graph', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[og_enabled]" value="1" <?php checked( $s['og_enabled'], 1 ); ?> /> <?php esc_html_e( 'Enable Open Graph and Twitter cards', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="inos_title_separator"><?php esc_html_e( 'Title separator', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="small-text" id="inos_title_separator" name="inos[title_separator]" value="<?php echo esc_attr( $s['title_separator'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_homepage_title"><?php esc_html_e( 'Homepage title override', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_homepage_title" name="inos[homepage_title]" value="<?php echo esc_attr( $s['homepage_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_homepage_description"><?php esc_html_e( 'Homepage meta description', 'infy-news-os-core' ); ?></label></th>
					<td><textarea class="large-text" rows="3" id="inos_homepage_description" name="inos[homepage_description]"><?php echo esc_textarea( $s['homepage_description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="inos_homepage_keywords"><?php esc_html_e( 'Homepage keywords', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="inos_homepage_keywords" name="inos[homepage_keywords]" value="<?php echo esc_attr( isset( $s['homepage_keywords'] ) ? $s['homepage_keywords'] : '' ); ?>" placeholder="<?php esc_attr_e( 'smartphones, AI, India', 'infy-news-os-core' ); ?>" />
						<p class="description"><?php esc_html_e( 'Comma-separated, up to 10. Used in the homepage keywords meta tag. Leave empty to use the publication name and homepage sections.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Archive indexing', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[index_category_archives]" value="1" <?php checked( $s['index_category_archives'], 1 ); ?> /> <?php esc_html_e( 'Index category / section archives', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[index_tag_archives]" value="1" <?php checked( $s['index_tag_archives'], 1 ); ?> /> <?php esc_html_e( 'Index tag archives', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[index_author_archives]" value="1" <?php checked( $s['index_author_archives'], 1 ); ?> /> <?php esc_html_e( 'Index author archives', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[index_type_archives]" value="1" <?php checked( $s['index_type_archives'], 1 ); ?> /> <?php esc_html_e( 'Index article-type archives (News, Opinion, …)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[noindex_search]" value="1" <?php checked( $s['noindex_search'], 1 ); ?> /> <?php esc_html_e( 'noindex search results (recommended)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[noindex_date_archives]" value="1" <?php checked( $s['noindex_date_archives'], 1 ); ?> /> <?php esc_html_e( 'noindex date archives (recommended)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[noindex_empty_archives]" value="1" <?php checked( $s['noindex_empty_archives'], 1 ); ?> /> <?php esc_html_e( 'noindex empty archives', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Each category, tag, and article type also has optional SEO title, description, and robots fields on its edit screen. Authors have the same fields on their profile.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_twitter_card"><?php esc_html_e( 'Twitter card', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_twitter_card" name="inos[twitter_card]">
							<option value="summary_large_image" <?php selected( $s['twitter_card'], 'summary_large_image' ); ?>>summary_large_image</option>
							<option value="summary" <?php selected( $s['twitter_card'], 'summary' ); ?>>summary</option>
						</select>
					</td>
				</tr>
			<?php elseif ( 'google-news' === $tab ) : ?>
				<tr>
					<th><label for="inos_news_publication_name"><?php esc_html_e( 'Google News publication name', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="inos_news_publication_name" name="inos[news_publication_name]" value="<?php echo esc_attr( $s['news_publication_name'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Must match the name in Google Publisher Center.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'News sitemap', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_news_sitemap]" value="1" <?php checked( $s['enable_news_sitemap'], 1 ); ?> /> <?php esc_html_e( 'Enable Google News sitemap (articles from the last 48 hours)', 'infy-news-os-core' ); ?></label>
						<p>
							<a href="<?php echo esc_url( home_url( '/news-sitemap.xml' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( home_url( '/news-sitemap.xml' ) ); ?></a>
						</p>
						<p class="description"><?php esc_html_e( 'Submit this URL in Google Search Console and Publisher Center. It includes publication date, headline, language, lead image (1200px), keywords, and genres (opinion/live). Password and noindex articles are omitted.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Search sitemap', 'infy-news-os-core' ); ?></th>
					<td>
						<p>
							<a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( home_url( '/sitemap.xml' ) ); ?></a>
						</p>
						<p class="description"><?php esc_html_e( 'Index for Google Search, Discover, and Top Stories: homepage, articles (with lastmod and lead images), pages, sections, authors, tags, and images. Submit sitemap.xml in Search Console.', 'infy-news-os-core' ); ?></p>
						<label><input type="checkbox" name="inos[disable_core_sitemaps]" value="1" <?php checked( ! empty( $s['disable_core_sitemaps'] ), true ); ?> /> <?php esc_html_e( 'Disable WordPress core /wp-sitemap.xml (avoids duplicate sitemaps)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sitemap_include_authors]" value="1" <?php checked( ! empty( $s['sitemap_include_authors'] ), true ); ?> /> <?php esc_html_e( 'Include author archives', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sitemap_include_tags]" value="1" <?php checked( ! empty( $s['sitemap_include_tags'] ), true ); ?> /> <?php esc_html_e( 'Include tag archives', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Googlebot-News', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_googlebot_news]" value="1" <?php checked( $s['enable_googlebot_news'], 1 ); ?> /> <?php esc_html_e( 'Allow Googlebot-News except on noindex articles', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'News keywords', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_news_keywords_meta]" value="1" <?php checked( ! empty( $s['enable_news_keywords_meta'] ), true ); ?> /> <?php esc_html_e( 'Output keywords and news_keywords meta tags in the document head (article field, section, tags, or headline)', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Google News RSS', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_google_news_feed]" value="1" <?php checked( $s['enable_google_news_feed'], 1 ); ?> /> <?php esc_html_e( 'Enable /feed/google-news/', 'infy-news-os-core' ); ?></label>
						<p><a href="<?php echo esc_url( get_feed_link( 'google-news' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( get_feed_link( 'google-news' ) ); ?></a></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Full-content RSS', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[rss_full_content]" value="1" <?php checked( ! empty( $s['rss_full_content'] ), true ); ?> /> <?php esc_html_e( 'Include full article body in RSS (recommended for Publisher Center)', 'infy-news-os-core' ); ?></label></td>
				</tr>
			<?php elseif ( 'tracking' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Google Tag Manager', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_gtm]" value="1" <?php checked( ! empty( $s['enable_gtm'] ), true ); ?> /> <?php esc_html_e( 'Load GTM on the public site', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Share clicks fire a dataLayer share event (method, item_id, UTM). Create a GTM trigger on event name “share” or “inos_share”.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_gtm_container_id"><?php esc_html_e( 'GTM container ID', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="inos_gtm_container_id" name="inos[gtm_container_id]" value="<?php echo esc_attr( $s['gtm_container_id'] ); ?>" placeholder="GTM-XXXXXXX" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'From tagmanager.google.com. Leave empty until you have a container — nothing is invented.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Share UTM parameters', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_share_utm]" value="1" <?php checked( ! empty( $s['enable_share_utm'] ), true ); ?> /> <?php esc_html_e( 'Append UTM tags to shared article URLs and related-story links', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><label for="inos_utm_campaign"><?php esc_html_e( 'utm_campaign', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_utm_campaign" name="inos[utm_campaign]" value="<?php echo esc_attr( $s['utm_campaign'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_utm_medium"><?php esc_html_e( 'utm_medium (social)', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="inos_utm_medium" name="inos[utm_medium]" value="<?php echo esc_attr( $s['utm_medium'] ); ?>" />
						<p class="description"><?php esc_html_e( 'utm_source is the network (twitter, facebook, whatsapp…). Email uses medium “email”. Related links use source “related”.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Preferred Sources', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_preferred_source]" value="1" <?php checked( ! empty( $s['enable_preferred_source'] ), true ); ?> /> <?php esc_html_e( 'Show Google Preferred Sources button on article pages', 'infy-news-os-core' ); ?></label>
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: Google documentation URL */
									__( 'Uses Google’s official publisher button. Confirm the site appears in the <a href="%s" target="_blank" rel="noopener">source preferences tool</a>.', 'infy-news-os-core' ),
									'https://www.google.com/preferences/source'
								),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
										'rel'    => array(),
									),
								)
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_preferred_source_theme"><?php esc_html_e( 'Button theme', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_preferred_source_theme" name="inos[preferred_source_theme]">
							<option value="light" <?php selected( $s['preferred_source_theme'], 'light' ); ?>><?php esc_html_e( 'Light', 'infy-news-os-core' ); ?></option>
							<option value="dark" <?php selected( $s['preferred_source_theme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'infy-news-os-core' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="inos_preferred_source_lang"><?php esc_html_e( 'Button language', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="small-text" id="inos_preferred_source_lang" name="inos[preferred_source_lang]" value="<?php echo esc_attr( $s['preferred_source_lang'] ); ?>" placeholder="en" />
						<p class="description"><?php esc_html_e( 'Optional ISO language code. Empty = visitor’s browser language.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_preferred_source_domain"><?php esc_html_e( 'Preferred source domain', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="inos_preferred_source_domain" name="inos[preferred_source_domain]" value="<?php echo esc_attr( $s['preferred_source_domain'] ); ?>" placeholder="<?php echo esc_attr( wp_parse_url( home_url( '/' ), PHP_URL_HOST ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Root domain shown in Google’s tool (e.g. techunfold.in). Use this on local/staging so the deeplink targets production.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
			<?php elseif ( 'schema' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'JSON-LD', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_schema]" value="1" <?php checked( $s['enable_schema'], 1 ); ?> /> <?php esc_html_e( 'Output NewsArticle / LiveBlogPosting / Organization graph', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="inos_default_article_type"><?php esc_html_e( 'Default article @type', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_default_article_type" name="inos[default_article_type]">
							<?php
							$types = array( 'NewsArticle', 'ReportageNewsArticle', 'AnalysisNewsArticle', 'OpinionNewsArticle', 'BackgroundNewsArticle', 'ReviewNewsArticle' );
							foreach ( $types as $type ) :
								?>
								<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $s['default_article_type'], $type ); ?>><?php echo esc_html( $type ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Speakable', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_speakable]" value="1" <?php checked( $s['enable_speakable'], 1 ); ?> /> <?php esc_html_e( 'Mark headline and dek as Speakable for Google Assistant / voice', 'infy-news-os-core' ); ?></label></td>
				</tr>
			<?php elseif ( 'editorial' === $tab ) : ?>
				<tr>
					<th><label for="inos_breaking_duration_hours"><?php esc_html_e( 'Breaking news window (hours)', 'infy-news-os-core' ); ?></label></th>
					<td><input type="number" min="1" id="inos_breaking_duration_hours" name="inos[breaking_duration_hours]" value="<?php echo esc_attr( (string) $s['breaking_duration_hours'] ); ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Custom statuses', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_custom_statuses]" value="1" <?php checked( $s['enable_custom_statuses'], 1 ); ?> /> <?php esc_html_e( 'Add In Review and Copy Edit statuses', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="inos_correction_label"><?php esc_html_e( 'Correction label', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_correction_label" name="inos[correction_label]" value="<?php echo esc_attr( $s['correction_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Article experience', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[show_reading_time]" value="1" <?php checked( $s['show_reading_time'], 1 ); ?> /> <?php esc_html_e( 'Reading time in the byline', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_view_count]" value="1" <?php checked( $s['show_view_count'], 1 ); ?> /> <?php esc_html_e( 'Show view counts (after traffic builds)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_progress_bar]" value="1" <?php checked( $s['show_progress_bar'], 1 ); ?> /> <?php esc_html_e( 'Reading progress bar', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sticky_header_desktop]" value="1" <?php checked( ! empty( $s['sticky_header_desktop'] ), true ); ?> /> <?php esc_html_e( 'Sticky header on desktop (also in Appearance → Customize → Infy News OS → Masthead & subscribe)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sticky_header_mobile]" value="1" <?php checked( ! empty( $s['sticky_header_mobile'] ), true ); ?> /> <?php esc_html_e( 'Sticky header on mobile', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[sticky_share]" value="1" <?php checked( $s['sticky_share'], 1 ); ?> /> <?php esc_html_e( 'Floating share bar on articles (also in Appearance → Customize → Infy News OS → Article)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[article_reader_tools]" value="1" <?php checked( $s['article_reader_tools'], 1 ); ?> /> <?php esc_html_e( 'Article text size and header dark mode', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[mid_article_also_read]" value="1" <?php checked( $s['mid_article_also_read'], 1 ); ?> /> <?php esc_html_e( 'Inline “Also read” after a few paragraphs', 'infy-news-os-core' ); ?></label>
						<p class="description">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=article-sidebar' ) ); ?>"><?php esc_html_e( 'Edit article sidebar blocks', 'infy-news-os-core' ); ?></a>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_archive_pagination"><?php esc_html_e( 'Archive pagination', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_archive_pagination" name="inos[archive_pagination]">
							<option value="numbered" <?php selected( $s['archive_pagination'], 'numbered' ); ?>><?php esc_html_e( 'Numbered pages', 'infy-news-os-core' ); ?></option>
							<option value="load_more" <?php selected( $s['archive_pagination'], 'load_more' ); ?>><?php esc_html_e( 'Load more button', 'infy-news-os-core' ); ?></option>
							<option value="infinite" <?php selected( $s['archive_pagination'], 'infinite' ); ?>><?php esc_html_e( 'Infinite scroll', 'infy-news-os-core' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Category, tag, author, date, search, live coverage, and latest listings. AMP always uses numbered pages. Also in Appearance → Customize → Infy News OS → Archives.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
			<?php elseif ( 'homepage' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Layout', 'infy-news-os-core' ); ?></th>
					<td>
						<p>
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>">
								<?php esc_html_e( 'Open homepage builder', 'infy-news-os-core' ); ?>
							</a>
							<a class="button" href="<?php echo esc_url( add_query_arg( array( 'autofocus[section]' => 'inos_theme_light', 'url' => home_url( '/' ) ), admin_url( 'customize.php' ) ) ); ?>">
								<?php esc_html_e( 'Theme colors', 'infy-news-os-core' ); ?>
							</a>
						</p>
						<p class="description"><?php esc_html_e( 'Blocks, categories, and card layouts are in the builder. This tab is site-wide chrome, the ticker, the lead pin, and article recirc.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Homepage chrome', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[show_breaking_ticker]" value="1" <?php checked( $s['show_breaking_ticker'], 1 ); ?> /> <?php esc_html_e( 'Headline ticker', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_subscribe_cta]" value="1" <?php checked( $s['show_subscribe_cta'], 1 ); ?> /> <?php esc_html_e( 'Subscribe in the masthead', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_hero]" value="1" <?php checked( $s['show_hero'], 1 ); ?> /> <?php esc_html_e( 'Hero / lead story', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_latest]" value="1" <?php checked( $s['show_latest'], 1 ); ?> /> <?php esc_html_e( 'Latest rail', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_trending]" value="1" <?php checked( $s['show_trending'], 1 ); ?> /> <?php esc_html_e( 'Trending rail', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_home_web_stories]" value="1" <?php checked( $s['show_home_web_stories'], 1 ); ?> /> <?php esc_html_e( 'Web Stories rail', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_home_newsletter]" value="1" <?php checked( $s['show_home_newsletter'], 1 ); ?> /> <?php esc_html_e( 'Homepage newsletter', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[show_home_ads]" value="1" <?php checked( $s['show_home_ads'], 1 ); ?> /> <?php esc_html_e( 'Homepage ad modules', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Turn a module off here to hide it site-wide, even if it is still in the builder.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Headline ticker', 'infy-news-os-core' ); ?></th>
					<td>
						<p>
							<label for="inos_ticker_source"><?php esc_html_e( 'Show', 'infy-news-os-core' ); ?></label><br />
							<select id="inos_ticker_source" name="inos[ticker_source]">
								<option value="latest" <?php selected( $s['ticker_source'], 'latest' ); ?>><?php esc_html_e( 'Latest stories', 'infy-news-os-core' ); ?></option>
								<option value="live_latest" <?php selected( $s['ticker_source'], 'live_latest' ); ?>><?php esc_html_e( 'Open live blogs, then latest stories', 'infy-news-os-core' ); ?></option>
								<option value="breaking" <?php selected( $s['ticker_source'], 'breaking' ); ?>><?php esc_html_e( 'Breaking and live only', 'infy-news-os-core' ); ?></option>
							</select>
						</p>
						<p>
							<label for="inos_ticker_count"><?php esc_html_e( 'Number of headlines', 'infy-news-os-core' ); ?></label><br />
							<input type="number" min="3" max="20" id="inos_ticker_count" name="inos[ticker_count]" value="<?php echo esc_attr( (string) $s['ticker_count'] ); ?>" />
						</p>
						<p>
							<label for="inos_ticker_category"><?php esc_html_e( 'Section (optional)', 'infy-news-os-core' ); ?></label><br />
							<?php
							wp_dropdown_categories(
								array(
									'show_option_all' => __( 'All sections', 'infy-news-os-core' ),
									'name'            => 'inos[ticker_category]',
									'id'              => 'inos_ticker_category',
									'selected'        => (int) $s['ticker_category'],
									'hierarchical'    => true,
									'hide_empty'      => false,
									'orderby'         => 'name',
								)
							);
							?>
						</p>
						<p>
							<label for="inos_ticker_label"><?php esc_html_e( 'Label', 'infy-news-os-core' ); ?></label><br />
							<input type="text" class="regular-text" id="inos_ticker_label" name="inos[ticker_label]" value="<?php echo esc_attr( $s['ticker_label'] ); ?>" placeholder="<?php echo esc_attr( (string) $s['latest_title'] ); ?>" />
							<span class="description"><?php esc_html_e( 'Leave blank to use Latest, Live, or Breaking automatically.', 'infy-news-os-core' ); ?></span>
						</p>
						<p>
							<label for="inos_ticker_speed"><?php esc_html_e( 'Marquee speed', 'infy-news-os-core' ); ?></label><br />
							<select id="inos_ticker_speed" name="inos[ticker_speed]">
								<option value="slow" <?php selected( $s['ticker_speed'], 'slow' ); ?>><?php esc_html_e( 'Slow', 'infy-news-os-core' ); ?></option>
								<option value="normal" <?php selected( $s['ticker_speed'], 'normal' ); ?>><?php esc_html_e( 'Normal', 'infy-news-os-core' ); ?></option>
								<option value="fast" <?php selected( $s['ticker_speed'], 'fast' ); ?>><?php esc_html_e( 'Fast', 'infy-news-os-core' ); ?></option>
							</select>
						</p>
						<p>
							<label for="inos_ticker_placement"><?php esc_html_e( 'Where it appears', 'infy-news-os-core' ); ?></label><br />
							<select id="inos_ticker_placement" name="inos[ticker_placement]">
								<option value="all" <?php selected( $s['ticker_placement'], 'all' ); ?>><?php esc_html_e( 'Every page', 'infy-news-os-core' ); ?></option>
								<option value="home" <?php selected( $s['ticker_placement'], 'home' ); ?>><?php esc_html_e( 'Homepage only', 'infy-news-os-core' ); ?></option>
							</select>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_lead_post_id"><?php esc_html_e( 'Lead story', 'infy-news-os-core' ); ?></label></th>
					<td>
						<?php
						$lead_choices = get_posts(
							array(
								'post_type'           => function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : 'post',
								'post_status'         => 'publish',
								'posts_per_page'      => 40,
								'ignore_sticky_posts' => true,
								'no_found_rows'       => true,
							)
						);
						?>
						<select id="inos_lead_post_id" name="inos[lead_post_id]">
							<option value="0"><?php esc_html_e( 'Latest published', 'infy-news-os-core' ); ?></option>
							<?php foreach ( $lead_choices as $lead_choice ) : ?>
								<option value="<?php echo esc_attr( (string) $lead_choice->ID ); ?>" <?php selected( (int) $s['lead_post_id'], (int) $lead_choice->ID ); ?>>
									<?php echo esc_html( wp_trim_words( $lead_choice->post_title, 14, '…' ) ); ?>
									<?php if ( 'inos_live_blog' === $lead_choice->post_type ) : ?>
										<?php echo esc_html( ' — ' . ( function_exists( 'inos_label' ) ? inos_label( 'live' ) : __( 'Live', 'infy-news-os-core' ) ) ); ?>
									<?php endif; ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Pinning an article from the editor also sets this. Live blogs can be the lead story.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_trending_source"><?php esc_html_e( 'Trending source', 'infy-news-os-core' ); ?></label></th>
					<td>
						<select id="inos_trending_source" name="inos[trending_source]">
							<option value="views" <?php selected( $s['trending_source'], 'views' ); ?>><?php esc_html_e( 'View counts', 'infy-news-os-core' ); ?></option>
							<option value="editorial" <?php selected( $s['trending_source'], 'editorial' ); ?>><?php esc_html_e( 'Editor pins', 'infy-news-os-core' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="inos_trending_count"><?php esc_html_e( 'Trending count', 'infy-news-os-core' ); ?></label></th>
					<td><input type="number" min="1" max="20" id="inos_trending_count" name="inos[trending_count]" value="<?php echo esc_attr( (string) $s['trending_count'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_related_count"><?php esc_html_e( 'Related articles', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="number" min="1" max="24" id="inos_related_count" name="inos[related_count]" value="<?php echo esc_attr( (string) $s['related_count'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Maximum related stories on the article page (first batch plus Load more). Also in Appearance → Customize → Infy News OS → Article.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Related Load more', 'infy-news-os-core' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="inos[related_load_more]" value="1" <?php checked( ! empty( $s['related_load_more'] ), true ); ?> />
							<?php esc_html_e( 'Show a Load more button under related stories', 'infy-news-os-core' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'AMP always shows the full list with no button. Keep the first batch small so the related block does not jump while images load.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_related_load_more_initial"><?php esc_html_e( 'Related shown first', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="number" min="1" max="8" id="inos_related_load_more_initial" name="inos[related_load_more_initial]" value="<?php echo esc_attr( (string) $s['related_load_more_initial'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Rows rendered on first paint. Smaller values reserve a stable height (less CLS).', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_related_more_count"><?php esc_html_e( 'Related per click', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="number" min="1" max="8" id="inos_related_more_count" name="inos[related_more_count]" value="<?php echo esc_attr( (string) $s['related_more_count'] ); ?>" />
						<p class="description"><?php esc_html_e( 'How many reserved-height rows to append when Load more is pressed.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Also read', 'infy-news-os-core' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="inos[show_also_read]" value="1" <?php checked( $s['show_also_read'], 1 ); ?> />
							<?php esc_html_e( 'Show an Also read section under articles (alongside Related)', 'infy-news-os-core' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Editors pick stories on each article. If none are picked, the section is hidden.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_also_read_title"><?php esc_html_e( 'Also read heading', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_also_read_title" name="inos[also_read_title]" value="<?php echo esc_attr( $s['also_read_title'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_also_read_count"><?php esc_html_e( 'Also read count', 'infy-news-os-core' ); ?></label></th>
					<td><input type="number" min="1" max="8" id="inos_also_read_count" name="inos[also_read_count]" value="<?php echo esc_attr( (string) $s['also_read_count'] ); ?>" /></td>
				</tr>
			<?php elseif ( 'ads' === $tab ) : ?>
				<tr>
					<th><label for="inos_in_article_paragraph"><?php esc_html_e( 'In-article ad after paragraph', 'infy-news-os-core' ); ?></label></th>
					<td><input type="number" min="1" id="inos_in_article_paragraph" name="inos[in_article_paragraph]" value="<?php echo esc_attr( (string) $s['in_article_paragraph'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_ads_txt"><?php esc_html_e( 'ads.txt', 'infy-news-os-core' ); ?></label></th>
					<td>
						<textarea class="large-text code" rows="6" id="inos_ads_txt" name="inos[ads_txt]"><?php echo esc_textarea( $s['ads_txt'] ); ?></textarea>
						<p class="description"><?php echo esc_html( home_url( '/ads.txt' ) ); ?></p>
					</td>
				</tr>
				<?php
				$slots = array(
					'header'        => __( 'Header', 'infy-news-os-core' ),
					'below_ticker'  => __( 'Below ticker', 'infy-news-os-core' ),
					'in_article'    => __( 'In article', 'infy-news-os-core' ),
					'sidebar'       => __( 'Sidebar', 'infy-news-os-core' ),
					'between_cards' => __( 'Between cards', 'infy-news-os-core' ),
					'sticky_mobile' => __( 'Sticky mobile', 'infy-news-os-core' ),
					'footer'        => __( 'Footer', 'infy-news-os-core' ),
				);
				foreach ( $slots as $slot_id => $slot_label ) :
					$en  = 'ad_' . $slot_id . '_enable';
					$html = 'ad_' . $slot_id . '_html';
					$min = 'ad_' . $slot_id . '_min_height';
					?>
					<tr>
						<th><?php echo esc_html( $slot_label ); ?></th>
						<td>
							<label><input type="checkbox" name="inos[<?php echo esc_attr( $en ); ?>]" value="1" <?php checked( $s[ $en ], 1 ); ?> /> <?php esc_html_e( 'Enable', 'infy-news-os-core' ); ?></label>
							<p><textarea class="large-text code" rows="3" name="inos[<?php echo esc_attr( $html ); ?>]"><?php echo esc_textarea( $s[ $html ] ); ?></textarea></p>
							<label><?php esc_html_e( 'Min height (px)', 'infy-news-os-core' ); ?>
								<input type="number" min="0" name="inos[<?php echo esc_attr( $min ); ?>]" value="<?php echo esc_attr( (string) $s[ $min ] ); ?>" />
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php elseif ( 'newsletter' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Enable newsletter', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[enable_newsletter]" value="1" <?php checked( $s['enable_newsletter'], 1 ); ?> /> <?php esc_html_e( 'Show forms and accept signups', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="inos_newsletter_heading"><?php esc_html_e( 'Heading', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_newsletter_heading" name="inos[newsletter_heading]" value="<?php echo esc_attr( $s['newsletter_heading'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_newsletter_description"><?php esc_html_e( 'Description', 'infy-news-os-core' ); ?></label></th>
					<td><textarea class="large-text" rows="2" id="inos_newsletter_description" name="inos[newsletter_description]"><?php echo esc_textarea( $s['newsletter_description'] ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="inos_newsletter_button"><?php esc_html_e( 'Button label', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_newsletter_button" name="inos[newsletter_button]" value="<?php echo esc_attr( $s['newsletter_button'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_newsletter_success"><?php esc_html_e( 'Success message', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_newsletter_success" name="inos[newsletter_success]" value="<?php echo esc_attr( $s['newsletter_success'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_newsletter_webhook"><?php esc_html_e( 'Webhook URL (optional)', 'infy-news-os-core' ); ?></label></th>
					<td><input type="url" class="regular-text" id="inos_newsletter_webhook" name="inos[newsletter_webhook]" value="<?php echo esc_attr( $s['newsletter_webhook'] ); ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Local store', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[newsletter_store_local]" value="1" <?php checked( $s['newsletter_store_local'], 1 ); ?> /> <?php esc_html_e( 'Save subscribers in WordPress (CSV export on Subscribers page)', 'infy-news-os-core' ); ?></label></td>
				</tr>
			<?php elseif ( 'push' === $tab ) : ?>
				<?php
				$push_test = isset( $_GET['inos_push_test'] ) ? sanitize_key( wp_unslash( $_GET['inos_push_test'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$has_sa    = ! empty( $s['firebase_service_account'] );
				$subs      = class_exists( 'INOS_Push' ) ? INOS_Push::token_count() : 0;
				?>
				<?php if ( 'ok' === $push_test ) : ?>
					<tr><td colspan="2"><div class="notice notice-success inline"><p><?php esc_html_e( 'Test notification sent to subscribed browsers.', 'infy-news-os-core' ); ?></p></div></td></tr>
				<?php elseif ( 'fail' === $push_test ) : ?>
					<tr><td colspan="2"><div class="notice notice-error inline"><p><?php esc_html_e( 'Test send failed. Check the Firebase project ID, VAPID key, and service account JSON.', 'infy-news-os-core' ); ?></p></div></td></tr>
				<?php elseif ( 'empty' === $push_test ) : ?>
					<tr><td colspan="2"><div class="notice notice-warning inline"><p><?php esc_html_e( 'Publish at least one article before sending a test.', 'infy-news-os-core' ); ?></p></div></td></tr>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'Web push', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_web_push]" value="1" <?php checked( ! empty( $s['enable_web_push'] ), true ); ?> /> <?php esc_html_e( 'Ask visitors for notification permission and send a push when an article is published', 'infy-news-os-core' ); ?></label>
						<p class="description">
							<?php
							printf(
								/* translators: %d: subscriber count */
								esc_html__( 'Browsers currently subscribed: %d. HTTPS is required (except localhost). AMP pages never load this script.', 'infy-news-os-core' ),
								(int) $subs
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_firebase_api_key"><?php esc_html_e( 'Firebase API key', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_api_key" name="inos[firebase_api_key]" value="<?php echo esc_attr( $s['firebase_api_key'] ); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_auth_domain"><?php esc_html_e( 'Auth domain', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_auth_domain" name="inos[firebase_auth_domain]" value="<?php echo esc_attr( $s['firebase_auth_domain'] ); ?>" placeholder="your-project.firebaseapp.com" /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_project_id"><?php esc_html_e( 'Project ID', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_project_id" name="inos[firebase_project_id]" value="<?php echo esc_attr( $s['firebase_project_id'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_storage_bucket"><?php esc_html_e( 'Storage bucket', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_storage_bucket" name="inos[firebase_storage_bucket]" value="<?php echo esc_attr( $s['firebase_storage_bucket'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_messaging_sender_id"><?php esc_html_e( 'Messaging sender ID', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_messaging_sender_id" name="inos[firebase_messaging_sender_id]" value="<?php echo esc_attr( $s['firebase_messaging_sender_id'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_app_id"><?php esc_html_e( 'App ID', 'infy-news-os-core' ); ?></label></th>
					<td><input type="text" class="regular-text" id="inos_firebase_app_id" name="inos[firebase_app_id]" value="<?php echo esc_attr( $s['firebase_app_id'] ); ?>" placeholder="1:...:web:..." /></td>
				</tr>
				<tr>
					<th><label for="inos_firebase_vapid_key"><?php esc_html_e( 'Web Push certificate (VAPID key)', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="inos_firebase_vapid_key" name="inos[firebase_vapid_key]" value="<?php echo esc_attr( $s['firebase_vapid_key'] ); ?>" autocomplete="off" />
						<p class="description"><?php esc_html_e( 'Firebase console → Project settings → Cloud Messaging → Web Push certificates → Key pair.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_firebase_service_account"><?php esc_html_e( 'Service account JSON', 'infy-news-os-core' ); ?></label></th>
					<td>
						<textarea class="large-text code" rows="6" id="inos_firebase_service_account" name="inos[firebase_service_account]" placeholder="<?php echo $has_sa ? esc_attr__( 'Saved. Paste a new JSON file to replace it.', 'infy-news-os-core' ) : esc_attr__( '{ "type": "service_account", ... }', 'infy-news-os-core' ); ?>"></textarea>
						<p class="description">
							<?php
							echo $has_sa ? esc_html__( 'A service account is already stored. Leave this empty to keep it.', 'infy-news-os-core' ) . ' ' : '';
							esc_html_e( 'Firebase console → Project settings → Service accounts → Generate new private key. Used only on the server to send messages.', 'infy-news-os-core' );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_push_prompt_delay"><?php esc_html_e( 'Prompt delay (seconds)', 'infy-news-os-core' ); ?></label></th>
					<td><input type="number" min="2" max="60" id="inos_push_prompt_delay" name="inos[push_prompt_delay]" value="<?php echo esc_attr( (string) ( isset( $s['push_prompt_delay'] ) ? $s['push_prompt_delay'] : 8 ) ); ?>" /></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Test', 'infy-news-os-core' ); ?></th>
					<td>
						<?php
						$test_url = wp_nonce_url( admin_url( 'admin-post.php?action=inos_push_test' ), 'inos_push_test' );
						?>
						<a class="button" href="<?php echo esc_url( $test_url ); ?>"><?php esc_html_e( 'Send a test using the latest article', 'infy-news-os-core' ); ?></a>
						<p class="description"><?php esc_html_e( 'Save settings first. Then allow notifications on the live site and click test. The notice uses the site logo, featured image, headline, short description, and a Read more button.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
			<?php elseif ( 'performance' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Emojis', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[disable_emojis]" value="1" <?php checked( $s['disable_emojis'], 1 ); ?> /> <?php esc_html_e( 'Disable WordPress emoji scripts', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Embeds', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[disable_embeds]" value="1" <?php checked( $s['disable_embeds'], 1 ); ?> /> <?php esc_html_e( 'Disable wp-embed script', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Images', 'infy-news-os-core' ); ?></th>
					<td>
						<p class="description">
							<?php
							printf(
								/* translators: %s: settings tab URL */
								esc_html__( 'Lazy-load, WebP, Discover sizes, and the image sitemap are on the %s tab.', 'infy-news-os-core' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=inos-settings&tab=images' ) ) . '">' . esc_html__( 'Images', 'infy-news-os-core' ) . '</a>'
							);
							?>
						</p>
					</td>
				</tr>
			<?php elseif ( 'images' === $tab ) : ?>
				<tr>
					<th><?php esc_html_e( 'Lazy loading', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_lazy_load]" value="1" <?php checked( ! empty( $s['enable_lazy_load'] ), true ); ?> /> <?php esc_html_e( 'Native lazy-load for images (loading="lazy")', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Uses the browser loading attribute. Google can still crawl and index lazy-loaded images.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'LCP / lead image', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[skip_lcp_lazy]" value="1" <?php checked( ! empty( $s['skip_lcp_lazy'] ), true ); ?> /> <?php esc_html_e( 'Load the article featured image and homepage lead eagerly (fetchpriority=high)', 'infy-news-os-core' ); ?></label><br />
						<label><input type="checkbox" name="inos[preload_lcp_image]" value="1" <?php checked( ! empty( $s['preload_lcp_image'] ), true ); ?> /> <?php esc_html_e( 'Preload that image in the document head (Core Web Vitals / Discover)', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Embeds', 'infy-news-os-core' ); ?></th>
					<td><label><input type="checkbox" name="inos[lazy_iframes]" value="1" <?php checked( ! empty( $s['lazy_iframes'] ), true ); ?> /> <?php esc_html_e( 'Lazy-load iframes (YouTube, maps, and other embeds)', 'infy-news-os-core' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'WebP', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[image_webp]" value="1" <?php checked( ! empty( $s['image_webp'] ), true ); ?> /> <?php esc_html_e( 'Generate WebP derivatives for new JPEG/PNG uploads', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Applies to images uploaded after this is turned on. Re-generate thumbnails if you want WebP for older files.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="inos_image_quality"><?php esc_html_e( 'Compression quality', 'infy-news-os-core' ); ?></label></th>
					<td>
						<input type="number" min="60" max="95" id="inos_image_quality" name="inos[image_quality]" value="<?php echo esc_attr( (string) $s['image_quality'] ); ?>" />
						<p class="description"><?php esc_html_e( 'JPEG/WebP quality (60–95). 82 is a good balance for news photos.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Original size', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[keep_original_images]" value="1" <?php checked( ! empty( $s['keep_original_images'] ), true ); ?> /> <?php esc_html_e( 'Keep originals up to 2560px wide (Google Image Search and Discover prefer high-resolution photos)', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Alt text', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[auto_image_alt]" value="1" <?php checked( ! empty( $s['auto_image_alt'] ), true ); ?> /> <?php esc_html_e( 'Fill empty alt text from the headline or filename (editors can still override)', 'infy-news-os-core' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Discover / Top Stories schema', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[schema_multi_aspect]" value="1" <?php checked( ! empty( $s['schema_multi_aspect'] ), true ); ?> /> <?php esc_html_e( 'Publish 16:9, 4:3, and 1:1 crops (1200px) in NewsArticle image markup', 'infy-news-os-core' ); ?></label>
						<p class="description"><?php esc_html_e( 'Google Discover recommends those three aspect ratios on a featured image at least 1200px wide.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Image license (Search)', 'infy-news-os-core' ); ?></th>
					<td>
						<p>
							<label for="inos_image_license_url"><?php esc_html_e( 'License page URL', 'infy-news-os-core' ); ?></label><br />
							<input type="url" class="large-text" id="inos_image_license_url" name="inos[image_license_url]" value="<?php echo esc_attr( isset( $s['image_license_url'] ) ? $s['image_license_url'] : '' ); ?>" placeholder="https://" />
						</p>
						<p>
							<label for="inos_image_acquire_license_url"><?php esc_html_e( 'Acquire license page URL', 'infy-news-os-core' ); ?></label><br />
							<input type="url" class="large-text" id="inos_image_acquire_license_url" name="inos[image_acquire_license_url]" value="<?php echo esc_attr( isset( $s['image_acquire_license_url'] ) ? $s['image_acquire_license_url'] : '' ); ?>" placeholder="https://" />
						</p>
						<p>
							<label for="inos_image_copyright_notice"><?php esc_html_e( 'Copyright notice', 'infy-news-os-core' ); ?></label><br />
							<input type="text" class="large-text" id="inos_image_copyright_notice" name="inos[image_copyright_notice]" value="<?php echo esc_attr( isset( $s['image_copyright_notice'] ) ? $s['image_copyright_notice'] : '' ); ?>" />
						</p>
						<p class="description"><?php esc_html_e( 'Used in ImageObject schema for Google Image Search. Leave blank to use the editorial-policy page, the contact page, and “© year publication”.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Image sitemap', 'infy-news-os-core' ); ?></th>
					<td>
						<label><input type="checkbox" name="inos[enable_image_sitemap]" value="1" <?php checked( ! empty( $s['enable_image_sitemap'] ), true ); ?> /> <?php esc_html_e( 'Image sitemap for Google Image Search (title + caption)', 'infy-news-os-core' ); ?></label>
						<p class="description">
							<?php
							printf(
								/* translators: %s: sitemap URL */
								esc_html__( 'Listed in robots.txt. URL: %s', 'infy-news-os-core' ),
								'<code>' . esc_html( home_url( '/inos-sitemap-images.xml' ) ) . '</code>'
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Search preview', 'infy-news-os-core' ); ?></th>
					<td>
						<p class="description"><?php esc_html_e( 'max-image-preview:large is set on the SEO tab so Google Search and Discover can show large thumbnails.', 'infy-news-os-core' ); ?></p>
					</td>
				</tr>
			<?php elseif ( 'amp-stories' === $tab ) : ?>
				<?php
				$amp_on       = class_exists( 'INOS_AMP' ) && INOS_AMP::is_active();
				$stories_on   = class_exists( 'INOS_Web_Stories' ) && INOS_Web_Stories::is_active();
				$amp_settings = admin_url( 'admin.php?page=amp-options' );
				$stories_edit = admin_url( 'edit.php?post_type=web-story' );
				?>
				<tr>
					<th><?php esc_html_e( 'AMP plugin', 'infy-news-os-core' ); ?></th>
					<td>
						<?php if ( $amp_on ) : ?>
							<p><strong><?php esc_html_e( 'Official AMP plugin is active.', 'infy-news-os-core' ); ?></strong></p>
							<p class="description"><?php esc_html_e( 'Infy News OS keeps AMP in Transitional (paired) mode with /amp/ URLs. Normal permalinks always use the default theme. AMP HTML is served only when the URL ends in /amp/.', 'infy-news-os-core' ); ?></p>
							<ul class="ul-disc">
								<li><?php esc_html_e( 'Template mode: Transitional — not Standard (AMP-first) and not Reader.', 'infy-news-os-core' ); ?></li>
								<li><?php esc_html_e( 'Paired URL structure: Path suffix (/amp/).', 'infy-news-os-core' ); ?></li>
								<li><?php esc_html_e( 'Mobile redirection: Off — phones are not forced onto AMP.', 'infy-news-os-core' ); ?></li>
							</ul>
							<p class="description"><?php esc_html_e( 'Example: /technology/my-article/ is the regular page. /technology/my-article/amp/ is AMP. Web Stories are skipped because they are already AMP documents.', 'infy-news-os-core' ); ?></p>
							<p><a class="button" href="<?php echo esc_url( $amp_settings ); ?>"><?php esc_html_e( 'Open AMP settings', 'infy-news-os-core' ); ?></a></p>
						<?php else : ?>
							<p><?php esc_html_e( 'AMP pages are served by the official AMP plugin (wordpress.org/plugins/amp). Infy News OS does not generate AMP HTML itself.', 'infy-news-os-core' ); ?></p>
							<p><?php echo class_exists( 'INOS_AMP' ) ? INOS_AMP::action_link_html() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Web Stories plugin', 'infy-news-os-core' ); ?></th>
					<td>
						<?php if ( $stories_on ) : ?>
							<p><strong><?php esc_html_e( 'Official Web Stories plugin is active.', 'infy-news-os-core' ); ?></strong></p>
							<p class="description"><?php esc_html_e( 'Create stories in the Web Stories editor. Add or hide the homepage rail in Homepage builder. The Homepage tab can still switch the rail off site-wide.', 'infy-news-os-core' ); ?></p>
							<p>
								<a class="button" href="<?php echo esc_url( $stories_edit ); ?>"><?php esc_html_e( 'Open Web Stories', 'infy-news-os-core' ); ?></a>
								<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=inos-settings&tab=builder' ) ); ?>"><?php esc_html_e( 'Homepage builder', 'infy-news-os-core' ); ?></a>
							</p>
						<?php else : ?>
							<p><?php esc_html_e( 'The homepage Stories rail uses the official Web Stories plugin (wordpress.org/plugins/web-stories). Install it, then publish stories.', 'infy-news-os-core' ); ?></p>
							<p><?php echo class_exists( 'INOS_Web_Stories' ) ? INOS_Web_Stories::action_link_html() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		<?php endif; ?>
		<?php submit_button(); ?>
	</form>
	<?php endif; ?>
		</div>
	</div>
</div>
