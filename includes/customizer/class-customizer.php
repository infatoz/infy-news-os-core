<?php
/**
 * Site chrome Customizer (Appearance → Customize).
 *
 * exposes settings that still apply with a saved builder: colors, fonts, subscribe,
 * article chrome, and the headline ticker.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers Infy News OS Customizer controls.
 */
class INOS_Customizer {

	const OPTION = 'inos_settings';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
		add_action( 'customize_preview_init', array( __CLASS__, 'preview_assets' ) );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'controls_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'inline_colors' ), 30 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Register panel, sections, settings, controls.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register( $wp_customize ) {
		$wp_customize->add_panel(
			'inos_homepage',
			array(
				'title'       => __( 'Infy News OS — Global', 'infy-news-os-core' ),
				'description' => __( 'Site look, colors, type, and layout tokens. Header, footer, and blog have their own panels.', 'infy-news-os-core' ),
				'priority'    => 28,
				'capability'  => 'edit_theme_options',
			)
		);
		$wp_customize->add_panel(
			'inos_header',
			array(
				'title'       => __( 'Infy News OS — Header', 'infy-news-os-core' ),
				'description' => __( 'Masthead, sticky header, top bar, mobile menu colors, and the headline ticker.', 'infy-news-os-core' ),
				'priority'    => 29,
				'capability'  => 'edit_theme_options',
			)
		);
		$wp_customize->add_panel(
			'inos_footer',
			array(
				'title'       => __( 'Infy News OS — Footer', 'infy-news-os-core' ),
				'description' => __( 'Footer copy, theme credit, and scroll to top.', 'infy-news-os-core' ),
				'priority'    => 30,
				'capability'  => 'edit_theme_options',
			)
		);
		$wp_customize->add_panel(
			'inos_blog',
			array(
				'title'       => __( 'Infy News OS — Blog', 'infy-news-os-core' ),
				'description' => __( 'Archives, article chrome, and breadcrumbs. Homepage blocks are Infy News OS → Homepage builder.', 'infy-news-os-core' ),
				'priority'    => 31,
				'capability'  => 'edit_theme_options',
			)
		);

		self::section_look( $wp_customize );
		self::section_light_theme( $wp_customize );
		self::section_dark_theme( $wp_customize );
		self::section_typography( $wp_customize );
		self::section_layout( $wp_customize );
		self::section_branding( $wp_customize );
		self::section_topbar( $wp_customize );
		self::section_drawer( $wp_customize );
		self::section_ticker( $wp_customize );
		self::section_footer( $wp_customize );
		self::section_article( $wp_customize );
		self::section_archives( $wp_customize );
	}

	/**
	 * Site look presets.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_look( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_look',
			array(
				'title'       => __( 'Site look', 'infy-news-os-core' ),
				'description' => __( 'Five bundled looks. Choosing one applies its colors, fonts, and chrome. You can still edit Light theme, Dark theme, and Fonts after.', 'infy-news-os-core' ),
				'panel'       => 'inos_homepage',
				'priority'    => 8,
			)
		);

		$choices = class_exists( 'INOS_Presets' ) ? INOS_Presets::choices() : array( 'editorial' => __( 'Editorial', 'infy-news-os-core' ) );
		self::add_select(
			$wp_customize,
			'theme_preset',
			__( 'Look', 'infy-news-os-core' ),
			'inos_theme_look',
			$choices,
			'postMessage',
			array( 'INOS_Presets', 'sanitize' ),
			__( 'Editorial is the classic newspaper. Broadsheet is black-and-white. Magazine is warm and large. Digital is a tech news UI. News app is a hybrid mobile-app shell with all features kept.', 'infy-news-os-core' )
		);

		$wp_customize->add_setting(
			self::OPTION . '[home_title_style]',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => 'bar',
				'sanitize_callback' => array( __CLASS__, 'sanitize_title_style' ),
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			self::OPTION . '[home_title_style]',
			array(
				'label'   => __( 'Block title style', 'infy-news-os-core' ),
				'section' => 'inos_theme_look',
				'type'    => 'select',
				'choices' => array(
					'bar'       => __( 'Bar', 'infy-news-os-core' ),
					'underline' => __( 'Underline', 'infy-news-os-core' ),
					'boxed'     => __( 'Boxed', 'infy-news-os-core' ),
					'pill'      => __( 'Pill', 'infy-news-os-core' ),
					'minimal'   => __( 'Minimal', 'infy-news-os-core' ),
				),
			)
		);
	}

	/**
	 * Block title style from a preset.
	 *
	 * @param mixed $value Raw.
	 * @return string
	 */
	public static function sanitize_title_style( $value ) {
		$value = sanitize_key( (string) $value );
		$ok    = array( 'bar', 'underline', 'boxed', 'pill', 'minimal' );
		return in_array( $value, $ok, true ) ? $value : 'bar';
	}

	/**
	 * Local news typefaces.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_typography( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_fonts',
			array(
				'title'       => __( 'Fonts', 'infy-news-os-core' ),
				'description' => __( 'Readable typefaces used by news publishers, hosted with the theme (no Google Fonts request). UI is the sans face; headlines and article body use the serif face.', 'infy-news-os-core' ),
				'panel'       => 'inos_homepage',
				'priority'    => 12,
			)
		);

		$sans = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::sans_choices() : array();
		$serif = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::serif_choices() : array();

		self::add_select(
			$wp_customize,
			'font_sans',
			__( 'UI / sans-serif', 'infy-news-os-core' ),
			'inos_theme_fonts',
			$sans,
			'postMessage',
			array( 'INOS_Fonts', 'sanitize_sans' ),
			__( 'Navigation, bylines, buttons, and captions.', 'infy-news-os-core' )
		);
		self::add_select(
			$wp_customize,
			'font_serif',
			__( 'Headlines / serif', 'infy-news-os-core' ),
			'inos_theme_fonts',
			$serif,
			'postMessage',
			array( 'INOS_Fonts', 'sanitize_serif' ),
			__( 'Story titles and article body.', 'infy-news-os-core' )
		);
		self::add_number( $wp_customize, 'font_size_base', __( 'Body size (px)', 'infy-news-os-core' ), 'inos_theme_fonts', 18, 14, 22, __( 'Base size for article body and UI copy.', 'infy-news-os-core' ) );
	}

	/**
	 * Container, buttons, and type radius.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_layout( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_layout',
			array(
				'title'       => __( 'Layout', 'infy-news-os-core' ),
				'description' => __( 'Site container, article measure, and button corners. Homepage columns are Infy News OS → Homepage builder.', 'infy-news-os-core' ),
				'panel'       => 'inos_homepage',
				'priority'    => 14,
			)
		);
		self::add_number( $wp_customize, 'container_width', __( 'Container width (px)', 'infy-news-os-core' ), 'inos_theme_layout', 1180, 960, 1600, __( 'Max width of the masthead, homepage, and archives.', 'infy-news-os-core' ) );
		self::add_number( $wp_customize, 'content_width', __( 'Article measure (px)', 'infy-news-os-core' ), 'inos_theme_layout', 760, 560, 900, __( 'WordPress content_width and the article column.', 'infy-news-os-core' ) );
		self::add_number( $wp_customize, 'button_radius', __( 'Button radius (px)', 'infy-news-os-core' ), 'inos_theme_layout', 0, 0, 24, __( 'Subscribe, load more, and call-to-action buttons. 0 keeps sharp newspaper corners.', 'infy-news-os-core' ) );
	}

	/**
	 * Shared labels for light and dark palettes.
	 *
	 * @return array<string, array{label:string, description:string}>
	 */
	private static function palette_labels() {
		return array(
			'accent'    => array(
				'label'       => __( 'Primary', 'infy-news-os-core' ),
				'description' => __( 'Kickers, buttons, and highlights.', 'infy-news-os-core' ),
			),
			'secondary' => array(
				'label'       => __( 'Secondary', 'infy-news-os-core' ),
				'description' => __( 'Article links and supporting UI.', 'infy-news-os-core' ),
			),
			'paper'     => array(
				'label'       => __( 'Page background', 'infy-news-os-core' ),
				'description' => __( 'The page canvas behind cards and type.', 'infy-news-os-core' ),
			),
			'card'      => array(
				'label'       => __( 'Surface', 'infy-news-os-core' ),
				'description' => __( 'Cards, boxes, and raised panels.', 'infy-news-os-core' ),
			),
			'ink'       => array(
				'label'       => __( 'Text', 'infy-news-os-core' ),
				'description' => __( 'Headlines and body copy.', 'infy-news-os-core' ),
			),
			'muted'     => array(
				'label'       => __( 'Muted text', 'infy-news-os-core' ),
				'description' => __( 'Bylines, timestamps, and captions.', 'infy-news-os-core' ),
			),
			'line'      => array(
				'label'       => __( 'Borders', 'infy-news-os-core' ),
				'description' => __( 'Rules, dividers, and card outlines.', 'infy-news-os-core' ),
			),
			'mast'      => array(
				'label'       => __( 'Masthead', 'infy-news-os-core' ),
				'description' => __( 'Header bar behind the logo and section nav.', 'infy-news-os-core' ),
			),
			'breaking'  => array(
				'label'       => __( 'Breaking / live', 'infy-news-os-core' ),
				'description' => __( 'Breaking ticker, live badges, and alert chips.', 'infy-news-os-core' ),
			),
		);
	}

	/**
	 * Light theme palette.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_light_theme( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_light',
			array(
				'title'       => __( 'Light theme', 'infy-news-os-core' ),
				'description' => __( 'Colors for the default light appearance. Dark mode uses the Dark theme section.', 'infy-news-os-core' ),
				'panel'       => 'inos_homepage',
				'priority'    => 10,
			)
		);

		$defaults = INOS_Settings::defaults();
		$map      = array(
			'accent'    => 'accent_color',
			'secondary' => 'secondary_color',
			'paper'     => 'paper_color',
			'card'      => 'card_color',
			'ink'       => 'ink_color',
			'muted'     => 'muted_color',
			'line'      => 'line_color',
			'mast'      => 'mast_color',
			'breaking'  => 'breaking_color',
		);
		self::register_palette( $wp_customize, 'inos_theme_light', $map, $defaults );
	}

	/**
	 * Dark theme palette.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_dark_theme( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_dark',
			array(
				'title'       => __( 'Dark theme', 'infy-news-os-core' ),
				'description' => __( 'Colors when visitors turn on dark mode (masthead sun/moon). Editing a swatch here previews the dark palette.', 'infy-news-os-core' ),
				'panel'       => 'inos_homepage',
				'priority'    => 11,
			)
		);

		$defaults = INOS_Settings::defaults();
		$map      = array(
			'accent'    => 'dark_accent_color',
			'secondary' => 'dark_secondary_color',
			'paper'     => 'dark_paper_color',
			'card'      => 'dark_card_color',
			'ink'       => 'dark_ink_color',
			'muted'     => 'dark_muted_color',
			'line'      => 'dark_line_color',
			'mast'      => 'dark_mast_color',
			'breaking'  => 'dark_breaking_color',
		);
		self::register_palette( $wp_customize, 'inos_theme_dark', $map, $defaults );
	}

	/**
	 * Register a light or dark palette of color controls.
	 *
	 * @param WP_Customize_Manager  $wp_customize Manager.
	 * @param string                $section      Section id.
	 * @param array<string, string> $map          Token => option key.
	 * @param array<string, mixed>  $defaults     Settings defaults.
	 */
	private static function register_palette( $wp_customize, $section, $map, $defaults ) {
		$labels = self::palette_labels();
		foreach ( $map as $token => $key ) {
			$meta = isset( $labels[ $token ] ) ? $labels[ $token ] : array(
				'label'       => $key,
				'description' => '',
			);
			$hex = isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '#000000';
			self::add_color( $wp_customize, $key, $meta['label'], $hex, $section, $meta['description'] );
		}
	}

	/**
	 * Masthead subscribe + sticky header.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_branding( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_branding',
			array(
				'title'       => __( 'Masthead & subscribe', 'infy-news-os-core' ),
				'description' => __( 'Theme colors are in Light theme and Dark theme. This section is masthead chrome.', 'infy-news-os-core' ),
				'panel'       => 'inos_header',
				'priority'    => 13,
			)
		);

		self::add_select(
			$wp_customize,
			'masthead_identity',
			__( 'Logo, title, and description', 'infy-news-os-core' ),
			'inos_home_branding',
			function_exists( 'inos_masthead_identity_choices' ) ? inos_masthead_identity_choices() : array(),
			'postMessage',
			array( 'INOS_Settings', 'sanitize_masthead_identity' ),
			__( 'The site description is not shown under the logo unless you choose a combination that includes it. If there is no logo file, logo modes fall back to the title.', 'infy-news-os-core' )
		);
		self::add_checkbox( $wp_customize, 'show_subscribe_cta', __( 'Show Subscribe in the masthead', 'infy-news-os-core' ), 'inos_home_branding', true );
		$sticky_legacy  = class_exists( 'INOS_Settings' ) ? (int) INOS_Settings::get( 'sticky_header', 1 ) : 1;
		$sticky_desktop = class_exists( 'INOS_Settings' ) ? (int) INOS_Settings::get( 'sticky_header_desktop', $sticky_legacy ) : 1;
		$sticky_mobile  = class_exists( 'INOS_Settings' ) ? (int) INOS_Settings::get( 'sticky_header_mobile', $sticky_legacy ) : 1;
		self::add_checkbox(
			$wp_customize,
			'sticky_header_desktop',
			__( 'Sticky header on desktop', 'infy-news-os-core' ),
			'inos_home_branding',
			true,
			$sticky_desktop,
			__( 'Keep the logo bar and section nav at the top while scrolling on wide screens.', 'infy-news-os-core' )
		);
		self::add_checkbox(
			$wp_customize,
			'sticky_header_mobile',
			__( 'Sticky header on mobile', 'infy-news-os-core' ),
			'inos_home_branding',
			true,
			$sticky_mobile,
			__( 'Keep the logo bar at the top while scrolling on phones and tablets.', 'infy-news-os-core' )
		);
		self::add_checkbox(
			$wp_customize,
			'sticky_header_compact',
			__( 'Shrink sticky header while scrolling', 'infy-news-os-core' ),
			'inos_home_branding',
			true,
			1,
			__( 'Tighter masthead once the page has scrolled. Turn off to keep full height.', 'infy-news-os-core' )
		);
	}

	/**
	 * Top bar date, time, and custom line.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_topbar( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_topbar',
			array(
				'title'       => __( 'Date, time & custom text', 'infy-news-os-core' ),
				'description' => __( 'Shown in the masthead utility bar (and the mobile menu date). Formats use PHP date tokens, for example l, F j, Y or g:i a.', 'infy-news-os-core' ),
				'panel'       => 'inos_header',
			)
		);

		self::add_checkbox( $wp_customize, 'show_topbar_date', __( 'Show date', 'infy-news-os-core' ), 'inos_home_topbar' );
		self::add_text(
			$wp_customize,
			'topbar_date_format',
			__( 'Date format', 'infy-news-os-core' ),
			'inos_home_topbar',
			false,
			__( 'Examples: l, F j, Y — Sunday, August 23, 2026. F j, Y — August 23, 2026. D, j M Y — Sun, 23 Aug 2026.', 'infy-news-os-core' ),
			array( __CLASS__, 'sanitize_date_format' )
		);
		self::add_checkbox( $wp_customize, 'show_topbar_time', __( 'Show time', 'infy-news-os-core' ), 'inos_home_topbar' );
		self::add_text(
			$wp_customize,
			'topbar_time_format',
			__( 'Time format', 'infy-news-os-core' ),
			'inos_home_topbar',
			false,
			__( 'Examples: g:i a — 7:40 pm. H:i — 19:40. g:i:s a — 7:40:12 pm.', 'infy-news-os-core' ),
			array( __CLASS__, 'sanitize_date_format' )
		);
		self::add_checkbox( $wp_customize, 'show_topbar_text', __( 'Show custom text', 'infy-news-os-core' ), 'inos_home_topbar', true );
		self::add_text(
			$wp_customize,
			'topbar_text',
			__( 'Custom text', 'infy-news-os-core' ),
			'inos_home_topbar',
			true,
			__( 'Blank uses the site tagline from Site Identity. This line is only in the top bar, not the logo or footer.', 'infy-news-os-core' )
		);
	}

	/**
	 * Mobile navigation drawer.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_drawer( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_drawer',
			array(
				'title'       => __( 'Mobile menu', 'infy-news-os-core' ),
				'description' => __( 'Colors for the slide-out navigation. Menu items, search, social, subscribe, and widgets are Infy News OS → Mobile menu. Assign a WordPress menu under Appearance → Menus → Mobile menu.', 'infy-news-os-core' ),
				'panel'       => 'inos_header',
			)
		);

		self::add_color( $wp_customize, 'drawer_bg_color', __( 'Background color', 'infy-news-os-core' ), '#0c0f14', 'inos_home_drawer' );
		self::add_color( $wp_customize, 'drawer_link_color', __( 'Menu item color', 'infy-news-os-core' ), '#e8edf2', 'inos_home_drawer' );
		self::add_color( $wp_customize, 'drawer_hover_color', __( 'Active / hover color', 'infy-news-os-core' ), '#0e7490', 'inos_home_drawer' );
		self::add_color( $wp_customize, 'drawer_muted_color', __( 'Date and labels', 'infy-news-os-core' ), '#9aa3ad', 'inos_home_drawer' );
	}

	/**
	 * Article page chrome.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_article( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_article',
			array(
				'title'       => __( 'Article', 'infy-news-os-core' ),
				'description' => __( 'Share bar and related stories on article pages. The article details rail is Infy News OS → Article sidebar (add, edit, reorder, or remove blocks; WordPress widgets are one block type). Same related keys as Infy News OS → Editorial / Homepage. Keep the first related batch small so the list height stays stable (less layout shift while images load). AMP shows the full related list without a Load more button and hides the sidebar.', 'infy-news-os-core' ),
				'panel'       => 'inos_blog',
			)
		);

		self::add_checkbox(
			$wp_customize,
			'sticky_share',
			__( 'Show floating share bar', 'infy-news-os-core' ),
			'inos_home_article',
			true,
			1,
			__( 'Turn off to hide the sticky share icons on articles (including AMP).', 'infy-news-os-core' )
		);

		self::add_checkbox(
			$wp_customize,
			'related_load_more',
			__( 'Load more on related stories', 'infy-news-os-core' ),
			'inos_home_article',
			false,
			1,
			__( 'Show a Load more button under related stories. The button hides when there are no more matches, or on AMP.', 'infy-news-os-core' )
		);

		self::add_number(
			$wp_customize,
			'related_load_more_initial',
			__( 'Related stories shown first', 'infy-news-os-core' ),
			'inos_home_article',
			3,
			1,
			8,
			__( 'First paint row count. A smaller number reserves a stable height and avoids a jump when thumbnails load.', 'infy-news-os-core' )
		);

		self::add_number(
			$wp_customize,
			'related_more_count',
			__( 'Related stories per click', 'infy-news-os-core' ),
			'inos_home_article',
			3,
			1,
			8,
			__( 'How many rows to append when Load more is pressed. Rows use a reserved height so the page does not jump.', 'infy-news-os-core' )
		);

		self::add_number(
			$wp_customize,
			'related_count',
			__( 'Maximum related stories', 'infy-news-os-core' ),
			'inos_home_article',
			6,
			1,
			24,
			__( 'Total related stories (first batch plus extra clicks). Same setting as Infy News OS → Homepage.', 'infy-news-os-core' )
		);
	}

	/**
	 * Archive listing pagination.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_archives( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_archives',
			array(
				'title'       => __( 'Archives', 'infy-news-os-core' ),
				'description' => __( 'How category, tag, author, date, search, live coverage, and latest listings move between pages. AMP always uses numbered pages. Same setting as Infy News OS → Editorial.', 'infy-news-os-core' ),
				'panel'       => 'inos_blog',
			)
		);

		self::add_select(
			$wp_customize,
			'archive_pagination',
			__( 'Pagination', 'infy-news-os-core' ),
			'inos_home_archives',
			array(
				'numbered'  => __( 'Numbered pages', 'infy-news-os-core' ),
				'load_more' => __( 'Load more button', 'infy-news-os-core' ),
				'infinite'  => __( 'Infinite scroll', 'infy-news-os-core' ),
			),
			'refresh',
			array( 'INOS_Settings', 'sanitize_archive_pagination' )
		);
		self::add_select(
			$wp_customize,
			'archive_layout',
			__( 'Archive layout', 'infy-news-os-core' ),
			'inos_home_archives',
			array(
				'list' => __( 'List (lead + compact)', 'infy-news-os-core' ),
				'grid' => __( 'Card grid', 'infy-news-os-core' ),
			)
		);
		self::add_checkbox( $wp_customize, 'show_breadcrumbs', __( 'Show breadcrumbs', 'infy-news-os-core' ), 'inos_home_archives', false, 1, __( 'Category trail on archives and articles.', 'infy-news-os-core' ) );
	}

	/**
	 * Headline ticker (not a builder block).
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_ticker( $wp_customize ) {
		$wp_customize->add_section(
			'inos_home_ticker',
			array(
				'title'       => __( 'Headline ticker', 'infy-news-os-core' ),
				'description' => __( 'The same controls as Infy News OS → Homepage. The ticker is not a homepage builder block.', 'infy-news-os-core' ),
				'panel'       => 'inos_header',
			)
		);

		self::add_checkbox( $wp_customize, 'show_breaking_ticker', __( 'Show headline ticker', 'infy-news-os-core' ), 'inos_home_ticker', true );
		self::add_select(
			$wp_customize,
			'ticker_source',
			__( 'Ticker stories', 'infy-news-os-core' ),
			'inos_home_ticker',
			array(
				'latest'      => __( 'Latest stories', 'infy-news-os-core' ),
				'live_latest' => __( 'Open live blogs, then latest', 'infy-news-os-core' ),
				'breaking'    => __( 'Breaking and live only', 'infy-news-os-core' ),
			)
		);
		self::add_number( $wp_customize, 'ticker_count', __( 'Ticker headline count', 'infy-news-os-core' ), 'inos_home_ticker', 10, 3, 20 );
		self::add_text( $wp_customize, 'ticker_label', __( 'Ticker label (blank = automatic)', 'infy-news-os-core' ), 'inos_home_ticker' );
		self::add_select(
			$wp_customize,
			'ticker_speed',
			__( 'Ticker marquee speed', 'infy-news-os-core' ),
			'inos_home_ticker',
			array(
				'slow'   => __( 'Slow', 'infy-news-os-core' ),
				'normal' => __( 'Normal', 'infy-news-os-core' ),
				'fast'   => __( 'Fast', 'infy-news-os-core' ),
			)
		);
		self::add_select(
			$wp_customize,
			'ticker_placement',
			__( 'Ticker placement', 'infy-news-os-core' ),
			'inos_home_ticker',
			array(
				'all'  => __( 'Every page', 'infy-news-os-core' ),
				'home' => __( 'Homepage only', 'infy-news-os-core' ),
			)
		);
	}

	/**
	 * Footer copy and utilities.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	private static function section_footer( $wp_customize ) {
		$wp_customize->add_section(
			'inos_theme_footer',
			array(
				'title'       => __( 'Footer', 'infy-news-os-core' ),
				'description' => __( 'Footer menus are Appearance → Menus. This section is copy, credit, and the back-to-top control.', 'infy-news-os-core' ),
				'panel'       => 'inos_footer',
			)
		);
		self::add_text( $wp_customize, 'footer_text', __( 'Footer blurb', 'infy-news-os-core' ), 'inos_theme_footer', false, __( 'Short line under the site name. Blank uses the tagline.', 'infy-news-os-core' ) );
		self::add_checkbox( $wp_customize, 'show_theme_credit', __( 'Show theme credit', 'infy-news-os-core' ), 'inos_theme_footer', false, 1 );
		self::add_checkbox( $wp_customize, 'scroll_top', __( 'Scroll to top button', 'infy-news-os-core' ), 'inos_theme_footer', false, 1 );
	}

	/**
	 * Option checkbox.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 * @param string               $key          Key.
	 * @param string               $label        Label.
	 * @param string               $section      Section.
	 * @param bool                 $post_message Live preview.
	 * @param int                  $default      Default 1/0.
	 * @param string               $description  Help text.
	 */
	private static function add_checkbox( $wp_customize, $key, $label, $section, $post_message = false, $default = 1, $description = '' ) {
		$wp_customize->add_setting(
			self::OPTION . '[' . $key . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => $default ? 1 : 0,
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'transport'         => $post_message ? 'postMessage' : 'refresh',
			)
		);
		$control = array(
			'label'   => $label,
			'type'    => 'checkbox',
			'section' => $section,
		);
		if ( $description ) {
			$control['description'] = $description;
		}
		$wp_customize->add_control( self::OPTION . '[' . $key . ']', $control );
	}

	/**
	 * Option select.
	 *
	 * @param WP_Customize_Manager     $wp_customize Manager.
	 * @param string                   $key          Key.
	 * @param string                   $label        Label.
	 * @param string                   $section      Section.
	 * @param array<int|string,string> $choices      Choices.
	 * @param string                   $transport    Transport.
	 * @param callable|string          $sanitize     Sanitize.
	 * @param string                   $description  Optional help text.
	 */
	private static function add_select( $wp_customize, $key, $label, $section, $choices, $transport = 'refresh', $sanitize = 'sanitize_text_field', $description = '' ) {
		$wp_customize->add_setting(
			self::OPTION . '[' . $key . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => inos_get_option( $key, '' ),
				'sanitize_callback' => $sanitize,
				'transport'         => $transport,
			)
		);
		$control = array(
			'label'   => $label,
			'type'    => 'select',
			'section' => $section,
			'choices' => $choices,
		);
		if ( $description ) {
			$control['description'] = $description;
		}
		$wp_customize->add_control( self::OPTION . '[' . $key . ']', $control );
	}

	/**
	 * Option number.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 * @param string               $key          Key.
	 * @param string               $label        Label.
	 * @param string               $section      Section.
	 * @param int                  $default      Default.
	 * @param int                  $min          Min.
	 * @param int                  $max          Max.
	 * @param string               $description  Optional help text.
	 */
	private static function add_number( $wp_customize, $key, $label, $section, $default, $min, $max, $description = '' ) {
		$wp_customize->add_setting(
			self::OPTION . '[' . $key . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => $default,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$control = array(
			'label'       => $label,
			'type'        => 'number',
			'section'     => $section,
			'input_attrs' => array(
				'min' => $min,
				'max' => $max,
			),
		);
		if ( $description ) {
			$control['description'] = $description;
		}
		$wp_customize->add_control( self::OPTION . '[' . $key . ']', $control );
	}

	/**
	 * Option text.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 * @param string               $key          Key.
	 * @param string               $label        Label.
	 * @param string               $section      Section.
	 * @param bool                 $post_message Live.
	 * @param string               $description  Help text.
	 * @param callable|string      $sanitize     Sanitize.
	 */
	private static function add_text( $wp_customize, $key, $label, $section, $post_message = false, $description = '', $sanitize = 'sanitize_text_field' ) {
		$wp_customize->add_setting(
			self::OPTION . '[' . $key . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => (string) inos_get_option( $key, '' ),
				'sanitize_callback' => $sanitize,
				'transport'         => $post_message ? 'postMessage' : 'refresh',
			)
		);
		$control = array(
			'label'   => $label,
			'type'    => 'text',
			'section' => $section,
		);
		if ( $description ) {
			$control['description'] = $description;
		}
		$wp_customize->add_control( self::OPTION . '[' . $key . ']', $control );
	}

	/**
	 * Color control.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 * @param string               $key          Key.
	 * @param string               $label        Label.
	 * @param string               $default      Default hex.
	 * @param string               $section      Section.
	 * @param string               $description  Optional help text.
	 */
	private static function add_color( $wp_customize, $key, $label, $default, $section, $description = '' ) {
		$wp_customize->add_setting(
			self::OPTION . '[' . $key . ']',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'default'           => $default,
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$args = array(
			'label'   => $label,
			'section' => $section,
		);
		if ( $description ) {
			$args['description'] = $description;
		}
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				self::OPTION . '[' . $key . ']',
				$args
			)
		);
	}

	/**
	 * Checkbox to 1/0.
	 *
	 * @param mixed $value Value.
	 * @return int
	 */
	public static function sanitize_checkbox( $value ) {
		return $value ? 1 : 0;
	}

	/**
	 * Restrict PHP date/time format strings.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public static function sanitize_date_format( $value ) {
		$value = sanitize_text_field( (string) $value );
		$value = preg_replace( '/[^a-zA-Z0-9\s:\-\/.,\\\\]/', '', $value );
		return substr( is_string( $value ) ? $value : '', 0, 64 );
	}

	/**
	 * Preview script.
	 */
	public static function preview_assets() {
		wp_enqueue_script(
			'inos-customize-preview',
			INOS_CORE_URL . 'admin/js/inos-customize-preview.js',
			array( 'customize-preview' ),
			INOS_CORE_VERSION,
			true
		);
		wp_localize_script(
			'inos-customize-preview',
			'inosCustomize',
			array(
				'tagline'  => get_bloginfo( 'description', 'display' ),
				'sans'     => class_exists( 'INOS_Fonts' ) ? INOS_Fonts::stacks( 'sans' ) : array(),
				'serif'    => class_exists( 'INOS_Fonts' ) ? INOS_Fonts::stacks( 'serif' ) : array(),
				'presets'  => class_exists( 'INOS_Presets' ) ? INOS_Presets::js_catalog() : array(),
				'colorMap' => class_exists( 'INOS_Presets' ) ? INOS_Presets::color_css_map() : array(),
			)
		);
	}

	/**
	 * Controls script so choosing a look fills colors and fonts.
	 */
	public static function controls_assets() {
		wp_enqueue_script(
			'inos-customize-controls',
			INOS_CORE_URL . 'admin/js/inos-customize-controls.js',
			array( 'customize-controls' ),
			INOS_CORE_VERSION,
			true
		);
		wp_localize_script(
			'inos-customize-controls',
			'inosCustomizeControls',
			array(
				'presets' => class_exists( 'INOS_Presets' ) ? INOS_Presets::js_catalog() : array(),
			)
		);
	}

	/**
	 * CSS variables from settings.
	 */
	public static function inline_colors() {
		$hex = static function ( $key, $fallback ) {
			$value = sanitize_hex_color( (string) inos_get_option( $key, $fallback ) );
			return $value ? $value : $fallback;
		};

		$light_accent    = $hex( 'accent_color', '#0e7490' );
		$light_secondary = $hex( 'secondary_color', '#1e3a5f' );
		$light_paper     = $hex( 'paper_color', '#f6f5f1' );
		$light_card      = $hex( 'card_color', '#fffcf8' );
		$light_ink       = $hex( 'ink_color', '#121212' );
		$light_muted     = $hex( 'muted_color', '#5a5854' );
		$light_line      = $hex( 'line_color', '#e4e0d8' );
		$light_mast      = $hex( 'mast_color', '#05070a' );
		$light_breaking  = $hex( 'breaking_color', '#e11d48' );

		$dark_accent    = $hex( 'dark_accent_color', '#5eead4' );
		$dark_secondary = $hex( 'dark_secondary_color', '#7dd3fc' );
		$dark_paper     = $hex( 'dark_paper_color', '#0e0f12' );
		$dark_card      = $hex( 'dark_card_color', '#16181d' );
		$dark_ink       = $hex( 'dark_ink_color', '#eeeae3' );
		$dark_muted     = $hex( 'dark_muted_color', '#9b9890' );
		$dark_line      = $hex( 'dark_line_color', '#2a2c32' );
		$dark_mast      = $hex( 'dark_mast_color', '#05070a' );
		$dark_breaking  = $hex( 'dark_breaking_color', '#fb7185' );

		$dbg    = $hex( 'drawer_bg_color', '#0c0f14' );
		$dlink  = $hex( 'drawer_link_color', '#e8edf2' );
		$dhover = $hex( 'drawer_hover_color', $light_accent );
		$dmuted = $hex( 'drawer_muted_color', '#9aa3ad' );

		$sans  = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::stack( 'sans' ) : 'Inter, system-ui, sans-serif';
		$serif = class_exists( 'INOS_Fonts' ) ? INOS_Fonts::stack( 'serif' ) : '"Source Serif 4", Georgia, serif';
		$look  = class_exists( 'INOS_Presets' ) ? INOS_Presets::get() : array( 'radius' => '0px', 'shadow' => 'none' );
		$radius = isset( $look['radius'] ) ? $look['radius'] : '0px';
		$shadow = isset( $look['shadow'] ) ? $look['shadow'] : 'none';

		$css = sprintf(
			':root{--inos-light-accent:%s;--inos-light-secondary:%s;--inos-light-paper:%s;--inos-light-card:%s;--inos-light-ink:%s;--inos-light-muted:%s;--inos-light-line:%s;--inos-light-mast:%s;--inos-light-breaking:%s;--inos-dark-accent:%s;--inos-dark-secondary:%s;--inos-dark-paper:%s;--inos-dark-card:%s;--inos-dark-ink:%s;--inos-dark-muted:%s;--inos-dark-line:%s;--inos-dark-mast:%s;--inos-dark-breaking:%s;--inos-drawer-bg:%s;--inos-drawer-link:%s;--inos-drawer-hover:%s;--inos-drawer-muted:%s;}',
			$light_accent,
			$light_secondary,
			$light_paper,
			$light_card,
			$light_ink,
			$light_muted,
			$light_line,
			$light_mast,
			$light_breaking,
			$dark_accent,
			$dark_secondary,
			$dark_paper,
			$dark_card,
			$dark_ink,
			$dark_muted,
			$dark_line,
			$dark_mast,
			$dark_breaking,
			$dbg,
			$dlink,
			$dhover,
			$dmuted
		);
		$css .= sprintf(
			':root{--inos-sans:%s;--inos-serif:%s;--inos-radius:%s;--inos-shadow:%s;--inos-wrap:%spx;--inos-measure:%spx;--inos-btn-radius:%spx;--inos-font-size:%spx;}',
			$sans,
			$serif,
			$radius,
			$shadow,
			max( 960, min( 1600, absint( inos_get_option( 'container_width', 1180 ) ) ) ),
			max( 560, min( 900, absint( inos_get_option( 'content_width', 760 ) ) ) ),
			max( 0, min( 24, absint( inos_get_option( 'button_radius', 0 ) ) ) ),
			max( 14, min( 22, absint( inos_get_option( 'font_size_base', 18 ) ) ) )
		);
		$handle = wp_style_is( 'inos-editorial', 'enqueued' ) ? 'inos-editorial' : 'inos-theme';
		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Layout classes.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( is_customize_preview() ) {
			$classes[] = 'inos-customizer-preview';
		}
		if ( ! inos_get_option( 'sticky_header_compact', 1 ) ) {
			$classes[] = 'inos-sticky-full';
		}
		$classes[] = 'inos-archive--' . sanitize_html_class( (string) inos_get_option( 'archive_layout', 'list' ) );
		if ( is_front_page() ) {
			$classes[] = 'inos-home-layout';
			$classes[] = 'inos-hero--' . sanitize_html_class( (string) inos_get_option( 'hero_layout', 'lead-grid' ) );
			$classes[] = 'inos-titles--' . sanitize_html_class( (string) inos_get_option( 'home_title_style', 'bar' ) );
			if ( inos_get_option( 'home_sidebar', 0 ) ) {
				$classes[] = 'inos-home-has-sidebar';
			}
			if ( ! inos_get_option( 'show_breaking_ticker', 1 ) ) {
				$classes[] = 'inos-hide-ticker';
			}
			if ( ! inos_get_option( 'show_subscribe_cta', 1 ) ) {
				$classes[] = 'inos-hide-subscribe';
			}
			if ( ! inos_get_option( 'show_home_web_stories', 1 ) ) {
				$classes[] = 'inos-hide-web-stories';
			}
		}
		return $classes;
	}
}
