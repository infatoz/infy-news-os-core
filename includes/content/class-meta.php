<?php
/**
 * Article post meta (no UI — metabox saves these keys).
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key helpers.
 */
class INOS_Meta {

	/**
	 * Registered keys.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function keys() {
		return array(
			'_inos_kicker'           => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_dek'              => array( 'type' => 'string', 'sanitize' => 'sanitize_textarea_field' ),
			'_inos_dateline'         => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_breaking'         => array( 'type' => 'boolean', 'sanitize' => 'absint' ),
			'_inos_breaking_until'   => array( 'type' => 'integer', 'sanitize' => 'absint' ),
			'_inos_exclusive'        => array( 'type' => 'boolean', 'sanitize' => 'absint' ),
			'_inos_sponsored'        => array( 'type' => 'boolean', 'sanitize' => 'absint' ),
			'_inos_sponsored_label'  => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_primary_section'  => array( 'type' => 'integer', 'sanitize' => 'absint' ),
			'_inos_source'           => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_correction'       => array( 'type' => 'string', 'sanitize' => 'sanitize_textarea_field' ),
			'_inos_correction_time'  => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_robots'           => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_canonical'        => array( 'type' => 'string', 'sanitize' => 'esc_url_raw' ),
			'_inos_seo_title'        => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_seo_description'  => array( 'type' => 'string', 'sanitize' => 'sanitize_textarea_field' ),
			'_inos_homepage_pin'     => array( 'type' => 'boolean', 'sanitize' => 'absint' ),
			'_inos_trending_pin'     => array( 'type' => 'boolean', 'sanitize' => 'absint' ),
			'_inos_image_credit'     => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_news_keywords'    => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
			'_inos_views'            => array( 'type' => 'integer', 'sanitize' => 'absint' ),
		);
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	/**
	 * register_post_meta for REST / block editor.
	 */
	public static function register_meta() {
		foreach ( self::keys() as $key => $args ) {
			if ( '_inos_views' === $key ) {
				continue;
			}
			register_post_meta(
				'post',
				$key,
				array(
					'type'              => $args['type'],
					'single'            => true,
					'show_in_rest'      => true,
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
					'sanitize_callback' => $args['sanitize'],
				)
			);
		}
	}
}
