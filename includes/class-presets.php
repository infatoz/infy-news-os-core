<?php
/**
 * Visual theme presets (looks).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Five bundled site looks: colors, type, radius, and chrome.
 */
class INOS_Presets {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ), 12 );
	}

	/**
	 * Preset catalog.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all() {
		return array(
			'editorial'  => array(
				'label'       => __( 'Editorial', 'infy-news-os-core' ),
				'description' => __( 'Newspaper masthead, cream paper, and serif stories.', 'infy-news-os-core' ),
				'radius'      => '0px',
				'shadow'      => 'none',
				'font_sans'   => 'inter',
				'font_serif'  => 'source-serif-4',
				'title_style' => 'bar',
				'colors'      => array(
					'accent_color'          => '#0e7490',
					'secondary_color'       => '#1e3a5f',
					'paper_color'           => '#f6f5f1',
					'card_color'            => '#fffcf8',
					'ink_color'             => '#121212',
					'muted_color'           => '#5a5854',
					'line_color'            => '#e4e0d8',
					'mast_color'            => '#05070a',
					'breaking_color'        => '#e11d48',
					'dark_accent_color'     => '#5eead4',
					'dark_secondary_color'  => '#7dd3fc',
					'dark_paper_color'      => '#0e0f12',
					'dark_card_color'       => '#16181d',
					'dark_ink_color'        => '#eeeae3',
					'dark_muted_color'      => '#9b9890',
					'dark_line_color'       => '#2a2c32',
					'dark_mast_color'       => '#05070a',
					'dark_breaking_color'   => '#fb7185',
					'drawer_bg_color'       => '#0c0f14',
					'drawer_link_color'     => '#e8edf2',
					'drawer_hover_color'    => '#0e7490',
					'drawer_muted_color'    => '#9aa3ad',
				),
			),
			'broadsheet' => array(
				'label'       => __( 'Broadsheet', 'infy-news-os-core' ),
				'description' => __( 'High-contrast black-and-white newspaper with strict rules.', 'infy-news-os-core' ),
				'radius'      => '0px',
				'shadow'      => 'none',
				'font_sans'   => 'libre-franklin',
				'font_serif'  => 'libre-baskerville',
				'title_style' => 'boxed',
				'colors'      => array(
					'accent_color'          => '#111111',
					'secondary_color'       => '#111111',
					'paper_color'           => '#ffffff',
					'card_color'            => '#ffffff',
					'ink_color'             => '#111111',
					'muted_color'           => '#5c5c5c',
					'line_color'            => '#111111',
					'mast_color'            => '#111111',
					'breaking_color'        => '#c4101e',
					'dark_accent_color'     => '#f4f4f4',
					'dark_secondary_color'  => '#d4d4d4',
					'dark_paper_color'      => '#0a0a0a',
					'dark_card_color'       => '#141414',
					'dark_ink_color'        => '#f4f4f4',
					'dark_muted_color'      => '#a3a3a3',
					'dark_line_color'       => '#2a2a2a',
					'dark_mast_color'       => '#000000',
					'dark_breaking_color'   => '#ff6b6b',
					'drawer_bg_color'       => '#000000',
					'drawer_link_color'     => '#f4f4f4',
					'drawer_hover_color'    => '#c4101e',
					'drawer_muted_color'    => '#a3a3a3',
				),
			),
			'magazine'   => array(
				'label'       => __( 'Magazine', 'infy-news-os-core' ),
				'description' => __( 'Warm paper, burgundy highlights, and large feature headlines.', 'infy-news-os-core' ),
				'radius'      => '0.45rem',
				'shadow'      => '0 12px 40px rgba(42, 31, 24, 0.08)',
				'font_sans'   => 'source-sans-3',
				'font_serif'  => 'lora',
				'title_style' => 'pill',
				'colors'      => array(
					'accent_color'          => '#8b2942',
					'secondary_color'       => '#5c3d2e',
					'paper_color'           => '#faf6ef',
					'card_color'            => '#fffdf8',
					'ink_color'             => '#2a1f18',
					'muted_color'           => '#7a6a5c',
					'line_color'            => '#eadcc8',
					'mast_color'            => '#2a1f18',
					'breaking_color'        => '#c45c26',
					'dark_accent_color'     => '#e8b4a2',
					'dark_secondary_color'  => '#d4b896',
					'dark_paper_color'      => '#1a1410',
					'dark_card_color'       => '#241c16',
					'dark_ink_color'        => '#f3e6d4',
					'dark_muted_color'      => '#b5a394',
					'dark_line_color'       => '#3a2e24',
					'dark_mast_color'       => '#140f0c',
					'dark_breaking_color'   => '#e07a5f',
					'drawer_bg_color'       => '#140f0c',
					'drawer_link_color'     => '#f3e6d4',
					'drawer_hover_color'    => '#e8b4a2',
					'drawer_muted_color'    => '#b5a394',
				),
			),
			'digital'    => array(
				'label'       => __( 'Digital', 'infy-news-os-core' ),
				'description' => __( 'Bright tech-news UI with rounded cards and sans headlines.', 'infy-news-os-core' ),
				'radius'      => '0.85rem',
				'shadow'      => '0 10px 32px rgba(15, 23, 42, 0.08)',
				'font_sans'   => 'inter',
				'font_serif'  => 'source-serif-4',
				'title_style' => 'underline',
				'colors'      => array(
					'accent_color'          => '#2563eb',
					'secondary_color'       => '#7c3aed',
					'paper_color'           => '#f4f7fb',
					'card_color'            => '#ffffff',
					'ink_color'             => '#0f172a',
					'muted_color'           => '#64748b',
					'line_color'            => '#e2e8f0',
					'mast_color'            => '#0b1220',
					'breaking_color'        => '#f43f5e',
					'dark_accent_color'     => '#60a5fa',
					'dark_secondary_color'  => '#c4b5fd',
					'dark_paper_color'      => '#020617',
					'dark_card_color'       => '#0f172a',
					'dark_ink_color'        => '#f8fafc',
					'dark_muted_color'      => '#94a3b8',
					'dark_line_color'       => '#1e293b',
					'dark_mast_color'       => '#020617',
					'dark_breaking_color'   => '#fb7185',
					'drawer_bg_color'       => '#020617',
					'drawer_link_color'     => '#e2e8f0',
					'drawer_hover_color'    => '#60a5fa',
					'drawer_muted_color'    => '#94a3b8',
				),
			),
			'app'        => array(
				'label'       => __( 'News app', 'infy-news-os-core' ),
				'description' => __( 'Hybrid mobile-app chrome: bottom tabs on phones, side rail on desktop. Search, menu, homepage, and articles stay in place.', 'infy-news-os-core' ),
				'radius'      => '1.15rem',
				'shadow'      => '0 8px 28px rgba(15, 23, 42, 0.1)',
				'font_sans'   => 'roboto',
				'font_serif'  => 'source-serif-4',
				'title_style' => 'minimal',
				'colors'      => array(
					'accent_color'          => '#0ea5e9',
					'secondary_color'       => '#0369a1',
					'paper_color'           => '#f2f4f7',
					'card_color'            => '#ffffff',
					'ink_color'             => '#0f172a',
					'muted_color'           => '#64748b',
					'line_color'            => '#e5e7eb',
					'mast_color'            => '#0f172a',
					'breaking_color'        => '#ef4444',
					'dark_accent_color'     => '#7dd3fc',
					'dark_secondary_color'  => '#38bdf8',
					'dark_paper_color'      => '#000000',
					'dark_card_color'       => '#1c1c1e',
					'dark_ink_color'        => '#f5f5f7',
					'dark_muted_color'      => '#8e8e93',
					'dark_line_color'       => '#2c2c2e',
					'dark_mast_color'       => '#000000',
					'dark_breaking_color'   => '#ff453a',
					'drawer_bg_color'       => '#1c1c1e',
					'drawer_link_color'     => '#f5f5f7',
					'drawer_hover_color'    => '#7dd3fc',
					'drawer_muted_color'    => '#8e8e93',
				),
			),
		);
	}

	/**
	 * Choice list for selects.
	 *
	 * @return array<string, string>
	 */
	public static function choices() {
		$out = array();
		foreach ( self::all() as $id => $preset ) {
			$out[ $id ] = $preset['label'];
		}
		return $out;
	}

	/**
	 * Compact catalog for Customizer JS.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function js_catalog() {
		$out = array();
		foreach ( self::all() as $id => $preset ) {
			$out[ $id ] = array(
				'radius'      => $preset['radius'],
				'shadow'      => $preset['shadow'],
				'font_sans'   => $preset['font_sans'],
				'font_serif'  => $preset['font_serif'],
				'title_style' => $preset['title_style'],
				'colors'      => $preset['colors'],
			);
		}
		return $out;
	}

	/**
	 * Sanitize a preset id.
	 *
	 * @param mixed $value Raw.
	 * @return string
	 */
	public static function sanitize( $value ) {
		$value = sanitize_key( (string) $value );
		$all   = self::all();
		return isset( $all[ $value ] ) ? $value : 'editorial';
	}

	/**
	 * Current preset id.
	 *
	 * @return string
	 */
	public static function current() {
		return self::sanitize( inos_get_option( 'theme_preset', 'editorial' ) );
	}

	/**
	 * One preset row.
	 *
	 * @param string $id Preset id.
	 * @return array<string, mixed>
	 */
	public static function get( $id = '' ) {
		$all = self::all();
		$id  = self::sanitize( $id ? $id : self::current() );
		return $all[ $id ];
	}

	/**
	 * Whether the news-app look is active.
	 *
	 * @return bool
	 */
	public static function is_app() {
		return 'app' === self::current();
	}

	/**
	 * Copy a look’s colors, fonts, and title style onto a settings array.
	 *
	 * @param array<string, mixed> $out Settings (by ref).
	 * @param string               $id  Optional preset id.
	 */
	public static function apply_to_settings( &$out, $id = '' ) {
		$preset = self::get( $id ? $id : ( isset( $out['theme_preset'] ) ? $out['theme_preset'] : '' ) );
		if ( empty( $preset['colors'] ) || ! is_array( $preset['colors'] ) ) {
			return;
		}
		foreach ( $preset['colors'] as $key => $hex ) {
			$out[ $key ] = $hex;
		}
		$out['font_sans']        = $preset['font_sans'];
		$out['font_serif']       = $preset['font_serif'];
		$out['home_title_style'] = $preset['title_style'];
	}

	/**
	 * Body class for the active look.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		$classes[] = 'inos-preset-' . self::current();
		return $classes;
	}

	/**
	 * CSS color-var map used by preview JS.
	 *
	 * @return array<string, array{0:string,1:string}>
	 */
	public static function color_css_map() {
		return array(
			'accent_color'         => array( '--inos-light-accent', 'light' ),
			'secondary_color'      => array( '--inos-light-secondary', 'light' ),
			'paper_color'          => array( '--inos-light-paper', 'light' ),
			'card_color'           => array( '--inos-light-card', 'light' ),
			'ink_color'            => array( '--inos-light-ink', 'light' ),
			'muted_color'          => array( '--inos-light-muted', 'light' ),
			'line_color'           => array( '--inos-light-line', 'light' ),
			'mast_color'           => array( '--inos-light-mast', 'light' ),
			'breaking_color'       => array( '--inos-light-breaking', 'light' ),
			'dark_accent_color'    => array( '--inos-dark-accent', 'dark' ),
			'dark_secondary_color' => array( '--inos-dark-secondary', 'dark' ),
			'dark_paper_color'     => array( '--inos-dark-paper', 'dark' ),
			'dark_card_color'      => array( '--inos-dark-card', 'dark' ),
			'dark_ink_color'       => array( '--inos-dark-ink', 'dark' ),
			'dark_muted_color'     => array( '--inos-dark-muted', 'dark' ),
			'dark_line_color'      => array( '--inos-dark-line', 'dark' ),
			'dark_mast_color'      => array( '--inos-dark-mast', 'dark' ),
			'dark_breaking_color'  => array( '--inos-dark-breaking', 'dark' ),
			'drawer_bg_color'      => array( '--inos-drawer-bg', '' ),
			'drawer_link_color'    => array( '--inos-drawer-link', '' ),
			'drawer_hover_color'   => array( '--inos-drawer-hover', '' ),
			'drawer_muted_color'   => array( '--inos-drawer-muted', '' ),
		);
	}
}
