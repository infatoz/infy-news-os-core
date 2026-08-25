<?php
/**
 * Settings storage and defaults.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option wrapper for Infy News OS.
 */
class INOS_Settings {

	/**
	 * Default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		$blogname = get_option( 'blogname', 'Infy News OS' );

		return array(
			'publication_name'         => $blogname,
			'publication_language'     => 'en',
			'text_direction'           => 'auto',
			'show_theme_credit'        => 1,
			'datetime_display'         => 'site',
			'org_name'                 => $blogname,
			'legal_name'               => '',
			'logo_id'                  => 0,
			'sameas'                   => '',
			'contact_email'            => 'wordpress@infatoz.com',
			'contact_page_url'         => '',
			'founding_date'            => '',
			'enable_seo'               => 1,
			'max_image_preview_large'  => 1,
			'title_separator'          => '|',
			'homepage_title'           => '',
			'homepage_description'     => '',
			'homepage_keywords'        => '',
			'index_category_archives'  => 1,
			'index_tag_archives'       => 1,
			'index_author_archives'    => 1,
			'index_type_archives'      => 1,
			'noindex_search'           => 1,
			'noindex_date_archives'    => 1,
			'noindex_empty_archives'   => 1,
			'og_enabled'               => 1,
			'twitter_card'             => 'summary_large_image',
			'news_publication_name'    => $blogname,
			'enable_news_sitemap'      => 1,
			'disable_core_sitemaps'    => 1,
			'sitemap_include_tags'     => 1,
			'sitemap_include_authors'  => 1,
			'enable_googlebot_news'    => 1,
			'enable_google_news_feed'  => 1,
			'rss_full_content'         => 1,
			'enable_schema'            => 1,
			'enable_speakable'         => 1,
			'enable_news_keywords_meta'=> 1,
			'default_article_type'     => 'NewsArticle',
			'breaking_duration_hours'  => 24,
			'enable_custom_statuses'   => 1,
			'correction_label'         => __( 'Correction', 'infy-news-os-core' ),
			'show_reading_time'        => 1,
			'show_view_count'          => 0,
			'show_progress_bar'        => 1,
			'sticky_header'            => 1,
			'sticky_header_desktop'    => 1,
			'sticky_header_mobile'     => 1,
			'sticky_share'             => 1,
			'archive_pagination'       => 'numbered',
			'article_reader_tools'     => 1,
			'mid_article_also_read'    => 1,
			'lead_post_id'             => 0,
			'secondary_count'          => 4,
			'section_rows'             => '',
			'trending_source'          => 'views',
			'trending_count'           => 6,
			'related_count'            => 6,
			'related_load_more'        => 1,
			'related_load_more_initial'=> 3,
			'related_more_count'       => 3,
			'show_also_read'           => 1,
			'also_read_title'          => __( 'Also read', 'infy-news-os-core' ),
			'also_read_count'          => 4,
			'in_article_paragraph'     => 2,
			'ads_txt'                  => '',
			'ad_header_enable'         => 0,
			'ad_header_html'           => '',
			'ad_header_min_height'     => 90,
			'ad_below_ticker_enable'   => 0,
			'ad_below_ticker_html'     => '',
			'ad_below_ticker_min_height' => 90,
			'ad_in_article_enable'     => 0,
			'ad_in_article_html'       => '',
			'ad_in_article_min_height' => 250,
			'ad_sidebar_enable'        => 0,
			'ad_sidebar_html'          => '',
			'ad_sidebar_min_height'    => 250,
			'ad_between_cards_enable'  => 0,
			'ad_between_cards_html'    => '',
			'ad_between_cards_min_height' => 90,
			'ad_sticky_mobile_enable'  => 0,
			'ad_sticky_mobile_html'    => '',
			'ad_sticky_mobile_min_height' => 50,
			'ad_footer_enable'         => 0,
			'ad_footer_html'           => '',
			'ad_footer_min_height'     => 90,
			'enable_newsletter'        => 1,
			'newsletter_heading'       => __( 'Get the briefing', 'infy-news-os-core' ),
			'newsletter_description'   => __( 'The day’s essential stories, sent once each morning.', 'infy-news-os-core' ),
			'newsletter_button'        => __( 'Subscribe', 'infy-news-os-core' ),
			'newsletter_success'       => __( 'Thanks — you are on the list.', 'infy-news-os-core' ),
			'newsletter_webhook'       => '',
			'newsletter_store_local'   => 1,
			'disable_emojis'           => 1,
			'disable_embeds'           => 0,
			'skip_lcp_lazy'            => 1,
			'enable_lazy_load'         => 1,
			'preload_lcp_image'        => 1,
			'lazy_iframes'             => 1,
			'image_webp'               => 1,
			'auto_image_alt'           => 1,
			'enable_image_sitemap'     => 1,
			'schema_multi_aspect'      => 1,
			'keep_original_images'     => 1,
			'image_quality'            => 82,
			'show_breaking_ticker'     => 1,
			'ticker_source'            => 'latest',
			'ticker_count'             => 10,
			'ticker_category'          => 0,
			'ticker_label'             => '',
			'ticker_speed'             => 'normal',
			'ticker_placement'         => 'all',
			'show_subscribe_cta'       => 1,
			'masthead_identity'        => 'logo',
			'theme_preset'             => 'editorial',
			'font_sans'                => 'inter',
			'font_serif'               => 'source-serif-4',
			'show_topbar_date'         => 1,
			'topbar_date_format'       => 'l, F j, Y',
			'show_topbar_time'         => 1,
			'topbar_time_format'       => 'g:i a',
			'show_topbar_text'         => 1,
			'topbar_text'              => '',
			'show_hero'                => 1,
			'hero_layout'              => 'lead-grid',
			'show_lead_dek'            => 1,
			'section_1'                => 0,
			'section_2'                => 0,
			'section_3'                => 0,
			'section_4'                => 0,
			'section_5'                => 0,
			'section_6'                => 0,
			'section_count'            => 4,
			'section_style'            => 'cards',
			'show_section_more'        => 1,
			'show_latest'              => 1,
			'latest_count'             => 8,
			'latest_title'             => __( 'Latest', 'infy-news-os-core' ),
			'show_trending'            => 1,
			'trending_title'           => __( 'Trending', 'infy-news-os-core' ),
			'split_layout'             => 'latest-trending',
			'show_home_newsletter'     => 1,
			'show_home_ads'            => 1,
			'home_kicker'              => '',
			'home_intro'               => '',
			'home_sidebar'             => 0,
			'home_title_style'         => 'bar',
			'home_unique_posts'        => 1,
			'accent_color'             => '#0e7490',
			'secondary_color'          => '#1e3a5f',
			'mast_color'               => '#05070a',
			'paper_color'              => '#f6f5f1',
			'card_color'               => '#fffcf8',
			'ink_color'                => '#121212',
			'muted_color'              => '#5a5854',
			'line_color'               => '#e4e0d8',
			'breaking_color'           => '#e11d48',
			'dark_accent_color'        => '#5eead4',
			'dark_secondary_color'     => '#7dd3fc',
			'dark_mast_color'          => '#05070a',
			'dark_paper_color'         => '#0e0f12',
			'dark_card_color'          => '#16181d',
			'dark_ink_color'           => '#eeeae3',
			'dark_muted_color'         => '#9b9890',
			'dark_line_color'          => '#2a2c32',
			'dark_breaking_color'      => '#fb7185',
			'drawer_bg_color'          => '#0c0f14',
			'drawer_link_color'        => '#e8edf2',
			'drawer_hover_color'       => '#0e7490',
			'drawer_muted_color'       => '#9aa3ad',
			'enable_gtm'               => 0,
			'gtm_container_id'         => '',
			'enable_share_utm'         => 1,
			'utm_campaign'             => 'article-share',
			'utm_medium'               => 'social',
			'enable_preferred_source'  => 1,
			'preferred_source_theme'   => 'light',
			'preferred_source_lang'    => '',
			'preferred_source_domain'  => '',
			'show_home_web_stories'    => 1,
			'web_stories_title'        => __( 'Stories', 'infy-news-os-core' ),
			'web_stories_count'        => 10,
			'web_stories_view'         => 'circles',
			'container_width'          => 1180,
			'content_width'            => 760,
			'button_radius'            => 0,
			'font_size_base'           => 18,
			'footer_text'              => '',
			'scroll_top'               => 1,
			'archive_layout'           => 'list',
			'show_breadcrumbs'         => 1,
			'sticky_header_compact'    => 1,
			'image_license_url'        => '',
			'image_acquire_license_url'=> '',
			'image_copyright_notice'   => '',
		);
	}

	/**
	 * Hex color option keys (light palette, dark palette, mobile drawer).
	 *
	 * @return string[]
	 */
	public static function color_keys() {
		return array(
			'accent_color',
			'secondary_color',
			'mast_color',
			'paper_color',
			'card_color',
			'ink_color',
			'muted_color',
			'line_color',
			'breaking_color',
			'dark_accent_color',
			'dark_secondary_color',
			'dark_mast_color',
			'dark_paper_color',
			'dark_card_color',
			'dark_ink_color',
			'dark_muted_color',
			'dark_line_color',
			'dark_breaking_color',
			'drawer_bg_color',
			'drawer_link_color',
			'drawer_hover_color',
			'drawer_muted_color',
		);
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function all() {
		$stored = get_option( INOS_CORE_OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return self::with_sticky_header_flags( $stored, wp_parse_args( $stored, self::defaults() ) );
	}

	/**
	 * Desktop / mobile sticky flags, inheriting the legacy sticky_header value.
	 *
	 * @param array<string, mixed> $stored Raw option row.
	 * @param array<string, mixed> $all    Merged settings.
	 * @return array<string, mixed>
	 */
	private static function with_sticky_header_flags( $stored, $all ) {
		$legacy = ! empty( $all['sticky_header'] ) ? 1 : 0;
		if ( ! array_key_exists( 'sticky_header_desktop', $stored ) ) {
			$all['sticky_header_desktop'] = $legacy;
		}
		if ( ! array_key_exists( 'sticky_header_mobile', $stored ) ) {
			$all['sticky_header_mobile'] = $legacy;
		}
		$all['sticky_header'] = ( ! empty( $all['sticky_header_desktop'] ) || ! empty( $all['sticky_header_mobile'] ) ) ? 1 : 0;
		return $all;
	}

	/**
	 * Get one setting.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$all = self::all();
		if ( array_key_exists( $key, $all ) ) {
			return $all[ $key ];
		}
		return $default;
	}

	/**
	 * Persist settings (merges with existing so unused tabs are not reset).
	 *
	 * @param array<string, mixed> $values Values.
	 */
	public static function update( $values ) {
		$clean = self::sanitize( $values );
		update_option( INOS_CORE_OPTION, $clean );
	}

	/**
	 * Sanitize posted settings merged onto current options.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $values ) {
		$defaults = self::defaults();
		$out      = wp_parse_args( self::all(), $defaults );
		$previous_preset = class_exists( 'INOS_Presets' ) ? INOS_Presets::sanitize( isset( $out['theme_preset'] ) ? $out['theme_preset'] : 'editorial' ) : 'editorial';

		if ( ! is_array( $values ) ) {
			return $out;
		}

		$text_keys = array(
			'publication_name',
			'publication_language',
			'text_direction',
			'datetime_display',
			'org_name',
			'legal_name',
			'contact_email',
			'contact_page_url',
			'founding_date',
			'title_separator',
			'homepage_title',
			'homepage_keywords',
			'news_publication_name',
			'default_article_type',
			'correction_label',
			'archive_pagination',
			'section_rows',
			'trending_source',
			'twitter_card',
			'newsletter_heading',
			'newsletter_button',
			'newsletter_webhook',
			'hero_layout',
			'section_style',
			'split_layout',
			'latest_title',
			'trending_title',
			'ticker_source',
			'ticker_label',
			'ticker_speed',
			'ticker_placement',
			'topbar_date_format',
			'topbar_time_format',
			'topbar_text',
			'masthead_identity',
			'theme_preset',
			'font_sans',
			'font_serif',
			'home_kicker',
			'home_title_style',
			'footer_text',
			'archive_layout',
			'accent_color',
			'secondary_color',
			'mast_color',
			'paper_color',
			'card_color',
			'ink_color',
			'muted_color',
			'line_color',
			'breaking_color',
			'dark_accent_color',
			'dark_secondary_color',
			'dark_mast_color',
			'dark_paper_color',
			'dark_card_color',
			'dark_ink_color',
			'dark_muted_color',
			'dark_line_color',
			'dark_breaking_color',
			'drawer_bg_color',
			'drawer_link_color',
			'drawer_hover_color',
			'drawer_muted_color',
			'gtm_container_id',
			'utm_campaign',
			'utm_medium',
			'preferred_source_theme',
			'preferred_source_lang',
			'preferred_source_domain',
			'web_stories_title',
			'web_stories_view',
			'also_read_title',
			'image_copyright_notice',
			'image_license_url',
			'image_acquire_license_url',
		);

		foreach ( $text_keys as $key ) {
			if ( isset( $values[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( wp_unslash( $values[ $key ] ) );
			}
		}

		$out['homepage_description']   = isset( $values['homepage_description'] ) ? sanitize_textarea_field( wp_unslash( $values['homepage_description'] ) ) : $out['homepage_description'];
		$out['sameas']                 = isset( $values['sameas'] ) ? sanitize_textarea_field( wp_unslash( $values['sameas'] ) ) : $out['sameas'];
		$out['newsletter_description'] = isset( $values['newsletter_description'] ) ? sanitize_textarea_field( wp_unslash( $values['newsletter_description'] ) ) : $out['newsletter_description'];
		$out['newsletter_success']     = isset( $values['newsletter_success'] ) ? sanitize_textarea_field( wp_unslash( $values['newsletter_success'] ) ) : $out['newsletter_success'];
		$out['home_intro']             = isset( $values['home_intro'] ) ? sanitize_textarea_field( wp_unslash( $values['home_intro'] ) ) : $out['home_intro'];
		$out['ads_txt']                = isset( $values['ads_txt'] ) ? sanitize_textarea_field( wp_unslash( $values['ads_txt'] ) ) : $out['ads_txt'];
		$out['contact_email']          = sanitize_email( $out['contact_email'] );
		$out['contact_page_url']       = esc_url_raw( $out['contact_page_url'] );
		$out['image_license_url']      = esc_url_raw( isset( $out['image_license_url'] ) ? $out['image_license_url'] : '' );
		$out['image_acquire_license_url'] = esc_url_raw( isset( $out['image_acquire_license_url'] ) ? $out['image_acquire_license_url'] : '' );
		$out['newsletter_webhook']     = esc_url_raw( $out['newsletter_webhook'] );

		$int_keys = array(
			'logo_id',
			'lead_post_id',
			'secondary_count',
			'trending_count',
			'related_count',
			'related_load_more_initial',
			'related_more_count',
			'section_1',
			'section_2',
			'section_3',
			'section_4',
			'section_5',
			'section_6',
			'section_count',
			'latest_count',
			'ticker_count',
			'ticker_category',
			'in_article_paragraph',
			'breaking_duration_hours',
			'ad_header_min_height',
			'ad_below_ticker_min_height',
			'ad_in_article_min_height',
			'ad_sidebar_min_height',
			'ad_between_cards_min_height',
			'ad_sticky_mobile_min_height',
			'ad_footer_min_height',
			'web_stories_count',
			'also_read_count',
			'image_quality',
			'container_width',
			'content_width',
			'button_radius',
			'font_size_base',
		);

		foreach ( $int_keys as $key ) {
			if ( isset( $values[ $key ] ) ) {
				$out[ $key ] = absint( $values[ $key ] );
			}
		}

		$checkboxes = array(
			'enable_seo',
			'max_image_preview_large',
			'og_enabled',
			'index_category_archives',
			'index_tag_archives',
			'index_author_archives',
			'index_type_archives',
			'noindex_search',
			'noindex_date_archives',
			'noindex_empty_archives',
			'enable_news_sitemap',
			'disable_core_sitemaps',
			'sitemap_include_tags',
			'sitemap_include_authors',
			'enable_googlebot_news',
			'enable_google_news_feed',
			'rss_full_content',
			'enable_schema',
			'enable_custom_statuses',
			'enable_newsletter',
			'newsletter_store_local',
			'disable_emojis',
			'disable_embeds',
			'skip_lcp_lazy',
			'enable_lazy_load',
			'preload_lcp_image',
			'lazy_iframes',
			'image_webp',
			'auto_image_alt',
			'enable_image_sitemap',
			'schema_multi_aspect',
			'keep_original_images',
			'show_breaking_ticker',
			'show_subscribe_cta',
			'show_topbar_date',
			'show_topbar_time',
			'show_topbar_text',
			'show_hero',
			'show_lead_dek',
			'show_section_more',
			'show_latest',
			'show_trending',
			'show_home_newsletter',
			'show_home_ads',
			'ad_header_enable',
			'ad_below_ticker_enable',
			'ad_in_article_enable',
			'ad_sidebar_enable',
			'ad_between_cards_enable',
			'ad_sticky_mobile_enable',
			'ad_footer_enable',
			'enable_gtm',
			'enable_share_utm',
			'enable_preferred_source',
			'show_home_web_stories',
			'show_also_read',
			'related_load_more',
			'show_theme_credit',
			'scroll_top',
			'show_breadcrumbs',
			'home_sidebar',
			'home_unique_posts',
			'enable_speakable',
			'enable_news_keywords_meta',
			'show_reading_time',
			'show_view_count',
			'show_progress_bar',
			'sticky_header',
			'sticky_header_desktop',
			'sticky_header_mobile',
			'sticky_header_compact',
			'sticky_share',
			'article_reader_tools',
			'mid_article_also_read',
		);

		$posted_checks = array();
		if ( isset( $_POST['inos_checkbox_keys'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$posted_checks = array_filter( array_map( 'sanitize_key', explode( ',', (string) wp_unslash( $_POST['inos_checkbox_keys'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		foreach ( $checkboxes as $key ) {
			if ( in_array( $key, $posted_checks, true ) || array_key_exists( $key, $values ) ) {
				$out[ $key ] = empty( $values[ $key ] ) ? 0 : 1;
			}
		}

		$ad_html_keys = array(
			'ad_header_html',
			'ad_below_ticker_html',
			'ad_in_article_html',
			'ad_sidebar_html',
			'ad_between_cards_html',
			'ad_sticky_mobile_html',
			'ad_footer_html',
		);

		foreach ( $ad_html_keys as $key ) {
			$out[ $key ] = isset( $values[ $key ] ) ? self::sanitize_ad_html( wp_unslash( $values[ $key ] ) ) : $out[ $key ];
		}

		$allowed_types = array( 'NewsArticle', 'ReportageNewsArticle', 'AnalysisNewsArticle', 'OpinionNewsArticle', 'BackgroundNewsArticle', 'ReviewNewsArticle' );
		if ( ! in_array( $out['default_article_type'], $allowed_types, true ) ) {
			$out['default_article_type'] = 'NewsArticle';
		}

		if ( ! in_array( $out['trending_source'], array( 'views', 'editorial' ), true ) ) {
			$out['trending_source'] = 'views';
		}
		if ( ! in_array( $out['ticker_source'], array( 'latest', 'breaking', 'live_latest' ), true ) ) {
			$out['ticker_source'] = 'latest';
		}
		if ( ! in_array( $out['ticker_speed'], array( 'slow', 'normal', 'fast' ), true ) ) {
			$out['ticker_speed'] = 'normal';
		}
		if ( ! in_array( $out['ticker_placement'], array( 'all', 'home' ), true ) ) {
			$out['ticker_placement'] = 'all';
		}
		$out['archive_pagination'] = self::sanitize_archive_pagination( isset( $out['archive_pagination'] ) ? $out['archive_pagination'] : 'numbered' );
		if ( $out['ticker_count'] < 3 ) {
			$out['ticker_count'] = 3;
		}
		if ( $out['ticker_count'] > 20 ) {
			$out['ticker_count'] = 20;
		}

		if ( ! in_array( $out['twitter_card'], array( 'summary_large_image', 'summary' ), true ) ) {
			$out['twitter_card'] = 'summary_large_image';
		}

		if ( $out['breaking_duration_hours'] < 1 ) {
			$out['breaking_duration_hours'] = 24;
		}

		$layouts = array( 'lead-grid', 'stacked', 'lead-only', 'mosaic', 'lead-left', 'slider' );
		if ( ! in_array( $out['hero_layout'], $layouts, true ) ) {
			$out['hero_layout'] = 'lead-grid';
		}
		if ( ! in_array( $out['section_style'], array( 'cards', 'compact' ), true ) ) {
			$out['section_style'] = 'cards';
		}
		if ( ! in_array( $out['split_layout'], array( 'latest-trending', 'trending-latest', 'stacked' ), true ) ) {
			$out['split_layout'] = 'latest-trending';
		}
		if ( ! in_array( $out['home_title_style'], array( 'bar', 'underline', 'boxed', 'pill', 'minimal' ), true ) ) {
			$out['home_title_style'] = 'bar';
		}
		if ( ! in_array( $out['archive_layout'], array( 'list', 'grid' ), true ) ) {
			$out['archive_layout'] = 'list';
		}
		if ( $out['container_width'] < 960 ) {
			$out['container_width'] = 1180;
		}
		if ( $out['container_width'] > 1600 ) {
			$out['container_width'] = 1600;
		}
		if ( $out['content_width'] < 560 ) {
			$out['content_width'] = 760;
		}
		if ( $out['content_width'] > 900 ) {
			$out['content_width'] = 900;
		}
		if ( $out['button_radius'] > 24 ) {
			$out['button_radius'] = 24;
		}
		if ( $out['font_size_base'] < 14 ) {
			$out['font_size_base'] = 18;
		}
		if ( $out['font_size_base'] > 22 ) {
			$out['font_size_base'] = 22;
		}
		if ( ! in_array( $out['web_stories_view'], array( 'circles', 'carousel', 'grid' ), true ) ) {
			$out['web_stories_view'] = 'circles';
		}
		if ( $out['web_stories_count'] < 1 ) {
			$out['web_stories_count'] = 10;
		}
		if ( $out['web_stories_count'] > 20 ) {
			$out['web_stories_count'] = 20;
		}
		if ( $out['also_read_count'] < 1 ) {
			$out['also_read_count'] = 4;
		}
		if ( $out['also_read_count'] > 8 ) {
			$out['also_read_count'] = 8;
		}
		if ( $out['related_count'] < 1 ) {
			$out['related_count'] = 6;
		}
		if ( $out['related_count'] > 24 ) {
			$out['related_count'] = 24;
		}
		if ( $out['related_load_more_initial'] < 1 ) {
			$out['related_load_more_initial'] = 3;
		}
		if ( $out['related_load_more_initial'] > 8 ) {
			$out['related_load_more_initial'] = 8;
		}
		if ( $out['related_load_more_initial'] > $out['related_count'] ) {
			$out['related_load_more_initial'] = $out['related_count'];
		}
		if ( $out['related_more_count'] < 1 ) {
			$out['related_more_count'] = 3;
		}
		if ( $out['related_more_count'] > 8 ) {
			$out['related_more_count'] = 8;
		}
		if ( $out['image_quality'] < 60 ) {
			$out['image_quality'] = 60;
		}
		if ( $out['image_quality'] > 95 ) {
			$out['image_quality'] = 95;
		}
		foreach ( self::color_keys() as $color_key ) {
			$hex = sanitize_hex_color( (string) $out[ $color_key ] );
			$out[ $color_key ] = $hex ? $hex : $defaults[ $color_key ];
		}

		if ( class_exists( 'INOS_Tracking' ) ) {
			$out['gtm_container_id'] = INOS_Tracking::sanitize_container_id( $out['gtm_container_id'] );
		} else {
			$out['gtm_container_id'] = strtoupper( preg_replace( '/[^A-Z0-9\-]/i', '', (string) $out['gtm_container_id'] ) );
		}

		$out['utm_campaign'] = sanitize_title( $out['utm_campaign'] );
		if ( '' === $out['utm_campaign'] ) {
			$out['utm_campaign'] = 'article-share';
		}
		$out['utm_medium'] = sanitize_key( $out['utm_medium'] );
		if ( '' === $out['utm_medium'] ) {
			$out['utm_medium'] = 'social';
		}
		if ( ! in_array( $out['text_direction'], array( 'auto', 'ltr', 'rtl' ), true ) ) {
			$out['text_direction'] = 'auto';
		}

		$out['masthead_identity'] = self::sanitize_masthead_identity( $out['masthead_identity'] );
		$out['theme_preset']      = class_exists( 'INOS_Presets' ) ? INOS_Presets::sanitize( $out['theme_preset'] ) : 'editorial';
		if ( class_exists( 'INOS_Presets' ) && isset( $values['theme_preset'] ) && $out['theme_preset'] !== $previous_preset ) {
			INOS_Presets::apply_to_settings( $out );
		}
		$out['font_sans']         = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::sanitize_sans( $out['font_sans'] ) : 'inter';
		$out['font_serif']        = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::sanitize_serif( $out['font_serif'] ) : 'source-serif-4';
		$out['sticky_header_desktop'] = empty( $out['sticky_header_desktop'] ) ? 0 : 1;
		$out['sticky_header_mobile']  = empty( $out['sticky_header_mobile'] ) ? 0 : 1;
		$out['sticky_header']         = ( $out['sticky_header_desktop'] || $out['sticky_header_mobile'] ) ? 1 : 0;

		if ( ! in_array( $out['preferred_source_theme'], array( 'light', 'dark' ), true ) ) {
			$out['preferred_source_theme'] = 'light';
		}
		$out['preferred_source_lang'] = preg_replace( '/[^a-zA-Z\-]/', '', (string) $out['preferred_source_lang'] );
		$domain = trim( (string) $out['preferred_source_domain'] );
		if ( $domain ) {
			$out['preferred_source_domain'] = preg_replace( '#^https?://#i', '', $domain );
			$out['preferred_source_domain'] = preg_replace( '#/.*$#', '', $out['preferred_source_domain'] );
		} else {
			$out['preferred_source_domain'] = '';
		}

		if ( ! empty( $out['logo_id'] ) ) {
			set_theme_mod( 'custom_logo', (int) $out['logo_id'] );
		} elseif ( empty( $out['logo_id'] ) ) {
			$theme_logo = absint( get_theme_mod( 'custom_logo' ) );
			if ( $theme_logo ) {
				$out['logo_id'] = $theme_logo;
			}
		}

		return $out;
	}

	/**
	 * Masthead logo / title / tagline combination.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_masthead_identity( $value ) {
		$value   = sanitize_key( (string) $value );
		$allowed = array(
			'logo',
			'title',
			'title_tagline',
			'logo_title',
			'logo_tagline',
			'logo_title_tagline',
			'tagline',
		);
		return in_array( $value, $allowed, true ) ? $value : 'logo';
	}

	/**
	 * Archive listing pagination type.
	 *
	 * @param mixed $value Raw value.
	 * @return string numbered|load_more|infinite
	 */
	public static function sanitize_archive_pagination( $value ) {
		$value = sanitize_key( (string) $value );
		if ( ! in_array( $value, array( 'numbered', 'load_more', 'infinite' ), true ) ) {
			return 'numbered';
		}
		return $value;
	}

	/**
	 * Allow ad scripts for administrators; strip PHP.
	 *
	 * @param string $html Raw HTML.
	 * @return string
	 */
	public static function sanitize_ad_html( $html ) {
		$html = (string) $html;
		$html = str_ireplace( array( '<?php', '<?=', '<?' ), '', $html );
		return $html;
	}
}
