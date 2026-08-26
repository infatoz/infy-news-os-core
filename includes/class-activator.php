<?php
/**
 * Activation / deactivation / ZIP replace.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin lifecycle.
 *
 * WordPress does not run register_activation_hook() when a plugin ZIP is
 * re-uploaded over the same folder. maybe_upgrade() and the upgrader hook
 * keep schema, tables, and rewrites in sync without wiping settings.
 */
class INOS_Activator {

	/**
	 * Run on activation.
	 *
	 * Do not register rewrites or CPTs here. Hostinger and ZIP-activate can
	 * run this before WP_Rewrite exists, which fatals add_rewrite_rule().
	 */
	public static function activate() {
		self::install( true );
	}

	/**
	 * Whether rewrite APIs are safe to call.
	 *
	 * @return bool
	 */
	public static function rewrites_ready() {
		global $wp_rewrite;
		return $wp_rewrite instanceof WP_Rewrite;
	}

	/**
	 * Idempotent install / upgrade. Never deletes inos_settings.
	 *
	 * CPT, taxonomy, feed, and rewrite registration belong on init — not here.
	 *
	 * @param bool $is_activation True when the activation hook fired.
	 */
	public static function install( $is_activation = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( ! get_option( INOS_CORE_OPTION ) ) {
			add_option( INOS_CORE_OPTION, INOS_Settings::defaults() );
		}

		INOS_Newsletter::create_table();

		update_option( 'inos_flush_rewrites', '1' );
		update_option( 'inos_core_version', INOS_CORE_VERSION );
	}

	/**
	 * Apply pending upgrades after a ZIP replace or version bump.
	 */
	public static function maybe_upgrade() {
		$stored = (string) get_option( 'inos_core_version', '' );
		if ( $stored === INOS_CORE_VERSION ) {
			return;
		}
		if ( $stored && class_exists( 'INOS_Setup' ) && ! INOS_Setup::is_complete() ) {
			INOS_Setup::mark_complete();
		}
		self::install( false );
	}

	/**
	 * Flush rewrites after CPTs and taxonomies are registered.
	 */
	public static function maybe_flush_rewrites() {
		if ( ! get_option( 'inos_flush_rewrites' ) ) {
			return;
		}
		if ( ! self::rewrites_ready() ) {
			return;
		}
		flush_rewrite_rules( false );
		delete_option( 'inos_flush_rewrites' );
	}

	/**
	 * ZIP upload / overwrite (activation hook does not fire).
	 *
	 * @param WP_Upgrader          $upgrader   Upgrader.
	 * @param array<string, mixed> $hook_extra Extra data.
	 */
	public static function on_upgrader_complete( $upgrader, $hook_extra ) {
		if ( ! is_array( $hook_extra ) || empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
			return;
		}

		$ours = false;
		if ( ! empty( $hook_extra['plugins'] ) && in_array( INOS_CORE_BASENAME, (array) $hook_extra['plugins'], true ) ) {
			$ours = true;
		}
		if ( ! empty( $hook_extra['plugin'] ) && INOS_CORE_BASENAME === $hook_extra['plugin'] ) {
			$ours = true;
		}
		if ( is_object( $upgrader ) && ! empty( $upgrader->result['destination_name'] ) && 'infy-news-os-core' === $upgrader->result['destination_name'] ) {
			$ours = true;
		}

		if ( $ours ) {
			self::install( false );
		}
	}

	/**
	 * Run on deactivation. Settings stay in the database.
	 */
	public static function deactivate() {
		if ( self::rewrites_ready() ) {
			flush_rewrite_rules( false );
		}
	}
}
