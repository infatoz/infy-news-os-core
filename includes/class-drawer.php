<?php
/**
 * Mobile menu drawer builder.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ordered drawer modules stored in option `inos_drawer`.
 */
class INOS_Drawer {

	const OPTION = 'inos_drawer';

	const WIDGET_ID = 'inos-drawer';

	const MENU_LOCATION = 'drawer';

	/**
	 * Blocks that fit the slide-out mobile menu.
	 *
	 * @return array<string, array{label:string, fields:string[]}>
	 */
	public static function types() {
		return array(
			'date'      => array(
				'label'  => __( 'Date / masthead text', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'search'    => array(
				'label'  => __( 'Search', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'menu'      => array(
				'label'  => __( 'Navigation menu', 'infy-news-os-core' ),
				'fields' => array( 'title', 'menu' ),
			),
			'sections'  => array(
				'label'  => __( 'Sections', 'infy-news-os-core' ),
				'fields' => array( 'title' ),
			),
			'social'    => array(
				'label'  => __( 'Social profiles', 'infy-news-os-core' ),
				'fields' => array( 'title' ),
			),
			'subscribe' => array(
				'label'  => __( 'Subscribe button', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'widgets'   => array(
				'label'  => __( 'WordPress widgets', 'infy-news-os-core' ),
				'fields' => array(),
			),
			'html'      => array(
				'label'  => __( 'Custom HTML', 'infy-news-os-core' ),
				'fields' => array( 'title', 'html' ),
			),
		);
	}

	/**
	 * Menus available for a Navigation menu block.
	 *
	 * @return array<string, string>
	 */
	public static function menu_choices() {
		$out = array(
			'0' => __( 'Assigned Mobile menu (or Top bar)', 'infy-news-os-core' ),
		);
		$menus = wp_get_nav_menus();
		if ( is_array( $menus ) ) {
			foreach ( $menus as $menu ) {
				$out[ (string) $menu->term_id ] = $menu->name;
			}
		}
		return $out;
	}

	/**
	 * Empty module.
	 *
	 * @param string               $type Type.
	 * @param array<string, mixed> $over Overrides.
	 * @return array<string, mixed>
	 */
	public static function blank( $type, $over = array() ) {
		$base = array(
			'id'      => 'mod_' . wp_generate_password( 8, false, false ),
			'type'    => $type,
			'enabled' => 1,
			'title'   => '',
			'menu'    => 0,
			'html'    => '',
		);
		return wp_parse_args( $over, $base );
	}

	/**
	 * Default drawer (matches the previous hardcoded panel).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function defaults() {
		return array(
			self::blank( 'date' ),
			self::blank( 'search' ),
			self::blank(
				'menu',
				array(
					'title' => '',
				)
			),
			self::blank( 'sections' ),
			self::blank( 'social' ),
			self::blank( 'widgets' ),
			self::blank( 'subscribe' ),
		);
	}

	/**
	 * Stored or default modules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		$stored = get_option( self::OPTION, null );
		if ( null === $stored || false === $stored ) {
			return self::defaults();
		}
		if ( ! is_array( $stored ) ) {
			return self::defaults();
		}
		return array_values( array_map( array( __CLASS__, 'sanitize_module' ), $stored ) );
	}

	/**
	 * Enabled modules.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function enabled() {
		$out = array();
		foreach ( self::all() as $mod ) {
			if ( ! empty( $mod['enabled'] ) ) {
				$out[] = $mod;
			}
		}
		return $out;
	}

	/**
	 * Persist modules.
	 *
	 * @param mixed $raw JSON string or array.
	 */
	public static function save( $raw ) {
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $raw ) ) {
			return;
		}
		$clean = array();
		foreach ( $raw as $mod ) {
			if ( is_array( $mod ) ) {
				$clean[] = self::sanitize_module( $mod );
			}
		}
		update_option( self::OPTION, $clean, false );
	}

	/**
	 * Sanitize one module.
	 *
	 * @param array<string, mixed> $mod Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_module( $mod ) {
		$types = array_keys( self::types() );
		$type  = isset( $mod['type'] ) ? sanitize_key( $mod['type'] ) : 'menu';
		if ( ! in_array( $type, $types, true ) ) {
			$type = 'menu';
		}
		$out            = self::blank( $type );
		if ( ! empty( $mod['id'] ) ) {
			$out['id'] = sanitize_key( $mod['id'] );
		}
		$out['enabled'] = empty( $mod['enabled'] ) ? 0 : 1;
		$out['title']   = isset( $mod['title'] ) ? sanitize_text_field( $mod['title'] ) : '';
		$out['menu']    = isset( $mod['menu'] ) ? absint( $mod['menu'] ) : 0;
		$out['html']    = isset( $mod['html'] ) ? wp_kses_post( $mod['html'] ) : '';
		return $out;
	}

	/**
	 * Heading for a module.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return string
	 */
	public static function title( $mod ) {
		if ( ! empty( $mod['title'] ) ) {
			return (string) $mod['title'];
		}
		$type = isset( $mod['type'] ) ? $mod['type'] : '';
		if ( 'menu' === $type ) {
			return function_exists( 'inos_theme_label' ) ? inos_theme_label( 'menu' ) : __( 'Menu', 'infy-news-os-core' );
		}
		if ( 'sections' === $type ) {
			return function_exists( 'inos_theme_label' ) ? inos_theme_label( 'sections' ) : __( 'Sections', 'infy-news-os-core' );
		}
		if ( 'social' === $type ) {
			return function_exists( 'inos_theme_label' ) ? inos_theme_label( 'follow_us' ) : __( 'Follow', 'infy-news-os-core' );
		}
		return '';
	}

	/**
	 * wp_nav_menu args for a Navigation menu block.
	 *
	 * @param array<string, mixed> $mod Module.
	 * @return array<string, mixed>|null
	 */
	public static function nav_args( $mod ) {
		$args = array(
			'container'   => false,
			'menu_class'  => 'inos-drawer__links',
			'depth'       => 2,
			'fallback_cb' => false,
		);
		$menu_id = isset( $mod['menu'] ) ? absint( $mod['menu'] ) : 0;
		if ( $menu_id ) {
			$args['menu'] = $menu_id;
			return $args;
		}
		if ( has_nav_menu( self::MENU_LOCATION ) ) {
			$args['theme_location'] = self::MENU_LOCATION;
			return $args;
		}
		if ( has_nav_menu( 'primary' ) ) {
			$args['theme_location'] = 'primary';
			return $args;
		}
		return null;
	}
}
