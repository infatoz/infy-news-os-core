<?php
/**
 * Local news-portal typefaces.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sans/serif stacks hosted in the theme (no remote font CDN).
 */
class INOS_Fonts {

	/**
	 * UI / UI-adjacent sans faces used by digital newsrooms.
	 *
	 * @return array<string, string>
	 */
	public static function sans_choices() {
		return array(
			'inter'          => __( 'Inter — digital news UI', 'infy-news-os-core' ),
			'source-sans-3'  => __( 'Source Sans 3 — magazine UI', 'infy-news-os-core' ),
			'libre-franklin' => __( 'Libre Franklin — newspaper Franklin', 'infy-news-os-core' ),
			'noto-sans'      => __( 'Noto Sans — wire-service / multilingual', 'infy-news-os-core' ),
			'ibm-plex-sans'  => __( 'IBM Plex Sans — finance news', 'infy-news-os-core' ),
			'roboto'         => __( 'Roboto — Google News', 'infy-news-os-core' ),
			'system-sans'    => __( 'System UI — device fonts', 'infy-news-os-core' ),
		);
	}

	/**
	 * Article / headline serif faces used by news publishers.
	 *
	 * @return array<string, string>
	 */
	public static function serif_choices() {
		return array(
			'source-serif-4'    => __( 'Source Serif 4 — magazine body', 'infy-news-os-core' ),
			'newsreader'        => __( 'Newsreader — news body', 'infy-news-os-core' ),
			'merriweather'      => __( 'Merriweather — long-form news', 'infy-news-os-core' ),
			'noto-serif'        => __( 'Noto Serif — multilingual news', 'infy-news-os-core' ),
			'libre-baskerville' => __( 'Libre Baskerville — newspaper', 'infy-news-os-core' ),
			'lora'              => __( 'Lora — editorial', 'infy-news-os-core' ),
			'system-serif'      => __( 'Georgia — classic news (system)', 'infy-news-os-core' ),
		);
	}

	/**
	 * CSS font-family stacks.
	 *
	 * @param string $role sans|serif.
	 * @return array<string, string>
	 */
	public static function stacks( $role = 'sans' ) {
		if ( 'serif' === $role ) {
			return array(
				'source-serif-4'    => '"Source Serif 4", Georgia, "Times New Roman", serif',
				'newsreader'        => 'Newsreader, Georgia, "Times New Roman", serif',
				'merriweather'      => 'Merriweather, Georgia, "Times New Roman", serif',
				'noto-serif'        => '"Noto Serif", Georgia, "Times New Roman", serif',
				'libre-baskerville' => '"Libre Baskerville", "Palatino Linotype", Georgia, serif',
				'lora'              => 'Lora, Georgia, "Times New Roman", serif',
				'system-serif'      => 'Georgia, "Times New Roman", serif',
			);
		}
		return array(
			'inter'          => 'Inter, system-ui, -apple-system, "Segoe UI", sans-serif',
			'source-sans-3'  => '"Source Sans 3", "Segoe UI", system-ui, sans-serif',
			'libre-franklin' => '"Libre Franklin", "Franklin Gothic Medium", "Segoe UI", sans-serif',
			'noto-sans'      => '"Noto Sans", "Segoe UI", sans-serif',
			'ibm-plex-sans'  => '"IBM Plex Sans", "Segoe UI", sans-serif',
			'roboto'         => 'Roboto, "Segoe UI", system-ui, sans-serif',
			'system-sans'    => 'system-ui, -apple-system, "Segoe UI", sans-serif',
		);
	}

	/**
	 * Sanitize a sans key.
	 *
	 * @param mixed $value Raw.
	 * @return string
	 */
	public static function sanitize_sans( $value ) {
		$value = sanitize_key( (string) $value );
		return isset( self::sans_choices()[ $value ] ) ? $value : 'inter';
	}

	/**
	 * Sanitize a serif key.
	 *
	 * @param mixed $value Raw.
	 * @return string
	 */
	public static function sanitize_serif( $value ) {
		$value = sanitize_key( (string) $value );
		return isset( self::serif_choices()[ $value ] ) ? $value : 'source-serif-4';
	}

	/**
	 * Stack for a role from settings.
	 *
	 * @param string $role sans|serif.
	 * @return string
	 */
	public static function stack( $role = 'sans' ) {
		if ( 'serif' === $role ) {
			$key     = self::sanitize_serif( inos_get_option( 'font_serif', 'source-serif-4' ) );
			$stacks  = self::stacks( 'serif' );
			return $stacks[ $key ];
		}
		$key    = self::sanitize_sans( inos_get_option( 'font_sans', 'inter' ) );
		$stacks = self::stacks( 'sans' );
		return $stacks[ $key ];
	}

	/**
	 * Relative woff2 files to preload for the current pair.
	 *
	 * @return string[] Paths under assets/fonts/.
	 */
	public static function preload_files() {
		$sans = array(
			'inter'          => 'inter/inter-latin-wght-normal.woff2',
			'source-sans-3'  => 'source-sans-3/source-sans-3-latin-400-normal.woff2',
			'libre-franklin' => 'libre-franklin/libre-franklin-latin-400-normal.woff2',
			'noto-sans'      => 'noto-sans/noto-sans-latin-400-normal.woff2',
			'ibm-plex-sans'  => 'ibm-plex-sans/ibm-plex-sans-latin-400-normal.woff2',
			'roboto'         => 'roboto/roboto-latin-400-normal.woff2',
			'system-sans'    => '',
		);
		$serif = array(
			'source-serif-4'    => 'source-serif-4/source-serif-4-latin-400-normal.woff2',
			'newsreader'        => 'newsreader/newsreader-latin-400-normal.woff2',
			'merriweather'      => 'merriweather/merriweather-latin-400-normal.woff2',
			'noto-serif'        => 'noto-serif/noto-serif-latin-400-normal.woff2',
			'libre-baskerville' => 'libre-baskerville/libre-baskerville-latin-400-normal.woff2',
			'lora'              => 'lora/lora-latin-400-normal.woff2',
			'system-serif'      => '',
		);
		$files = array();
		$sans_key  = self::sanitize_sans( inos_get_option( 'font_sans', 'inter' ) );
		$serif_key = self::sanitize_serif( inos_get_option( 'font_serif', 'source-serif-4' ) );
		if ( ! empty( $sans[ $sans_key ] ) ) {
			$files[] = $sans[ $sans_key ];
		}
		if ( ! empty( $serif[ $serif_key ] ) ) {
			$files[] = $serif[ $serif_key ];
		}
		return $files;
	}
}
