<?php
/**
 * GitHub updates for Infy News OS Core and the Infy News OS theme.
 *
 * Reads public releases (preferred) or the main-branch Version header from
 * https://github.com/infatoz/infy-news-os-core and https://github.com/infatoz/infy-news-os
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'INOS_GitHub_Updater' ) ) {
	return;
}

/**
 * WordPress 5.8+ Update URI host filters → GitHub.
 */
class INOS_GitHub_Updater {

	const PLUGIN_REPO = 'infatoz/infy-news-os-core';
	const THEME_REPO  = 'infatoz/infy-news-os';
	const PLUGIN_SLUG = 'infy-news-os-core';
	const THEME_SLUG  = 'infy-news-os';
	const BRANCH      = 'main';
	const CACHE_TTL   = 21600;

	/**
	 * Hooks once.
	 */
	public static function init() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		add_filter( 'update_plugins_github.com', array( __CLASS__, 'plugin_update' ), 10, 4 );
		add_filter( 'update_themes_github.com', array( __CLASS__, 'theme_update' ), 10, 4 );
		add_filter( 'upgrader_source_selection', array( __CLASS__, 'rename_github_folder' ), 10, 4 );
		add_filter( 'plugins_api', array( __CLASS__, 'plugins_api' ), 10, 3 );
		add_filter( 'themes_api', array( __CLASS__, 'themes_api' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'clear_cache' ), 10, 2 );
	}

	/**
	 * Plugin update payload for Update URI github.com.
	 *
	 * @param array|false $update      Existing.
	 * @param array       $plugin_data Headers.
	 * @param string      $plugin_file Basename.
	 * @param string[]    $locales     Locales.
	 * @return array|false
	 */
	public static function plugin_update( $update, $plugin_data, $plugin_file, $locales ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( ! self::is_core_plugin_file( $plugin_file ) ) {
			return $update;
		}

		$remote = self::remote_info( 'plugin' );
		if ( empty( $remote['version'] ) || empty( $remote['package'] ) ) {
			return $update;
		}

		return array(
			'slug'          => self::PLUGIN_SLUG,
			'version'       => $remote['version'],
			'url'           => 'https://github.com/' . self::PLUGIN_REPO,
			'package'       => $remote['package'],
			'tested'        => isset( $remote['tested'] ) ? $remote['tested'] : '7.1',
			'requires_php'  => isset( $remote['requires_php'] ) ? $remote['requires_php'] : '7.4',
			'requires'      => isset( $remote['requires'] ) ? $remote['requires'] : '6.7',
			'icons'         => array(),
			'banners'       => array(),
		);
	}

	/**
	 * Theme update payload.
	 *
	 * @param array|false $update          Existing.
	 * @param array       $theme_data      Headers.
	 * @param string      $theme_stylesheet Directory.
	 * @param string[]    $locales         Locales.
	 * @return array|false
	 */
	public static function theme_update( $update, $theme_data, $theme_stylesheet, $locales ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( self::THEME_SLUG !== $theme_stylesheet ) {
			return $update;
		}

		$remote = self::remote_info( 'theme' );
		if ( empty( $remote['version'] ) || empty( $remote['package'] ) ) {
			return $update;
		}

		return array(
			'theme'        => self::THEME_SLUG,
			'version'      => $remote['version'],
			'url'          => 'https://github.com/' . self::THEME_REPO,
			'package'      => $remote['package'],
			'tested'       => isset( $remote['tested'] ) ? $remote['tested'] : '7.1',
			'requires_php' => isset( $remote['requires_php'] ) ? $remote['requires_php'] : '7.4',
		);
	}

	/**
	 * Plugin details iframe.
	 *
	 * @param mixed  $result Result.
	 * @param string $action Action.
	 * @param object $args   Args.
	 * @return mixed
	 */
	public static function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || self::PLUGIN_SLUG !== $args->slug ) {
			return $result;
		}

		$remote = self::remote_info( 'plugin' );
		$version = ! empty( $remote['version'] ) ? $remote['version'] : ( defined( 'INOS_CORE_VERSION' ) ? INOS_CORE_VERSION : '' );

		return (object) array(
			'name'          => 'Infy News OS Core',
			'slug'          => self::PLUGIN_SLUG,
			'version'       => $version,
			'author'        => '<a href="https://infatoz.com">Infatoz Technologies LLP</a>',
			'homepage'      => 'https://github.com/' . self::PLUGIN_REPO,
			'requires'      => isset( $remote['requires'] ) ? $remote['requires'] : '6.7',
			'requires_php'  => isset( $remote['requires_php'] ) ? $remote['requires_php'] : '7.4',
			'tested'        => isset( $remote['tested'] ) ? $remote['tested'] : '7.1',
			'download_link' => isset( $remote['package'] ) ? $remote['package'] : '',
			'sections'      => array(
				'description' => 'News publisher engine for Infy News OS. Updates are served from GitHub.',
				'changelog'   => ! empty( $remote['changelog'] ) ? $remote['changelog'] : 'See the GitHub repository changelog.',
			),
		);
	}

	/**
	 * Theme details iframe.
	 *
	 * @param mixed  $result Result.
	 * @param string $action Action.
	 * @param object $args   Args.
	 * @return mixed
	 */
	public static function themes_api( $result, $action, $args ) {
		if ( 'theme_information' !== $action || empty( $args->slug ) || self::THEME_SLUG !== $args->slug ) {
			return $result;
		}

		$remote  = self::remote_info( 'theme' );
		$version = ! empty( $remote['version'] ) ? $remote['version'] : ( defined( 'INOS_THEME_VERSION' ) ? INOS_THEME_VERSION : '' );

		return (object) array(
			'name'          => 'Infy News OS',
			'slug'          => self::THEME_SLUG,
			'version'       => $version,
			'author'        => array(
				'display_name' => 'Infatoz Technologies LLP',
				'author'       => 'https://infatoz.com',
				'author_uri'   => 'https://infatoz.com',
			),
			'homepage'      => 'https://github.com/' . self::THEME_REPO,
			'download_link' => isset( $remote['package'] ) ? $remote['package'] : '',
			'sections'      => array(
				'description' => 'Classic news theme for Infy News OS Core. Updates are served from GitHub.',
				'changelog'   => ! empty( $remote['changelog'] ) ? $remote['changelog'] : 'See the GitHub repository changelog.',
			),
		);
	}

	/**
	 * GitHub ZIPs extract as infy-news-os-core-main — rename to the installed slug.
	 *
	 * @param string      $source        Extracted path.
	 * @param string      $remote_source Parent extract dir.
	 * @param WP_Upgrader $upgrader      Upgrader.
	 * @param array       $hook_extra    Extra.
	 * @return string|WP_Error
	 */
	public static function rename_github_folder( $source, $remote_source, $upgrader, $hook_extra ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( is_wp_error( $source ) ) {
			return $source;
		}

		$slug = '';
		if ( ! empty( $hook_extra['plugin'] ) && self::is_core_plugin_file( $hook_extra['plugin'] ) ) {
			$slug = self::PLUGIN_SLUG;
		} elseif ( ! empty( $hook_extra['theme'] ) && self::THEME_SLUG === $hook_extra['theme'] ) {
			$slug = self::THEME_SLUG;
		} elseif ( ! empty( $hook_extra['themes'] ) && in_array( self::THEME_SLUG, (array) $hook_extra['themes'], true ) ) {
			$slug = self::THEME_SLUG;
		}

		if ( ! $slug ) {
			return $source;
		}

		$source     = rtrim( str_replace( '\\', '/', $source ), '/' ) . '/';
		$wanted     = trailingslashit( $remote_source ) . $slug;
		$base       = basename( untrailingslashit( $source ) );
		if ( $base === $slug ) {
			return $source;
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
			return $source;
		}

		if ( $wp_filesystem->is_dir( $wanted ) ) {
			$wp_filesystem->delete( $wanted, true );
		}

		if ( $wp_filesystem->move( $source, $wanted, true ) ) {
			return trailingslashit( $wanted );
		}

		return new WP_Error(
			'inos_github_rename',
			sprintf(
				/* translators: %s: folder name */
				__( 'Could not prepare the GitHub package folder (%s).', 'infy-news-os-core' ),
				$slug
			)
		);
	}

	/**
	 * Cached GitHub version + ZIP URL.
	 *
	 * @param string $type plugin|theme.
	 * @return array<string, string>
	 */
	public static function remote_info( $type ) {
		$key  = 'inos_gh_' . ( 'theme' === $type ? 'theme' : 'plugin' );
		$cached = get_site_transient( $key );
		if ( is_array( $cached ) && ! empty( $cached['version'] ) ) {
			return $cached;
		}

		$repo = 'theme' === $type ? self::THEME_REPO : self::PLUGIN_REPO;
		$info = self::from_release( $repo, $type );
		if ( empty( $info['version'] ) ) {
			$info = self::from_branch( $repo, $type );
		}

		if ( ! empty( $info['version'] ) && ! empty( $info['package'] ) ) {
			set_site_transient( $key, $info, self::CACHE_TTL );
		} else {
			set_site_transient( $key, array( 'version' => '', 'package' => '' ), HOUR_IN_SECONDS );
		}

		return $info;
	}

	/**
	 * Latest GitHub Release, if any.
	 *
	 * @param string $repo owner/name.
	 * @param string $type plugin|theme.
	 * @return array<string, string>
	 */
	private static function from_release( $repo, $type ) {
		$body = self::github_get( 'https://api.github.com/repos/' . $repo . '/releases/latest' );
		if ( ! $body ) {
			return array();
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			return array();
		}

		$version = ltrim( (string) $data['tag_name'], 'vV' );
		$package = '';
		if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
				$url  = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';
				if ( $url && preg_match( '/\.zip$/i', $name ) ) {
					$package = $url;
					break;
				}
			}
		}
		if ( ! $package ) {
			$package = 'https://github.com/' . $repo . '/archive/refs/tags/' . rawurlencode( (string) $data['tag_name'] ) . '.zip';
		}

		$changelog = '';
		if ( ! empty( $data['body'] ) ) {
			$changelog = wp_kses_post( wpautop( $data['body'] ) );
		}

		$headers = self::headers_from_repo_file( $repo, $type, (string) $data['tag_name'] );

		return array_merge(
			$headers,
			array(
				'version'   => $version,
				'package'   => $package,
				'changelog' => $changelog,
			)
		);
	}

	/**
	 * Version on the default branch (used when there is no GitHub Release).
	 *
	 * @param string $repo owner/name.
	 * @param string $type plugin|theme.
	 * @return array<string, string>
	 */
	private static function from_branch( $repo, $type ) {
		$headers = self::headers_from_repo_file( $repo, $type, self::BRANCH );
		if ( empty( $headers['version'] ) ) {
			return array();
		}
		$headers['package'] = 'https://github.com/' . $repo . '/archive/refs/heads/' . self::BRANCH . '.zip';
		return $headers;
	}

	/**
	 * Parse Version / Requires from the remote plugin or style.css file.
	 *
	 * @param string $repo owner/name.
	 * @param string $type plugin|theme.
	 * @param string $ref  Branch or tag.
	 * @return array<string, string>
	 */
	private static function headers_from_repo_file( $repo, $type, $ref ) {
		$file = 'theme' === $type ? 'style.css' : 'infy-news-os-core.php';
		$url  = 'https://raw.githubusercontent.com/' . $repo . '/' . rawurlencode( $ref ) . '/' . $file;
		$body = self::http_get( $url );
		if ( ! $body ) {
			return array();
		}

		$out = array();
		if ( preg_match( '/^\s*(?:\*\s*)?Version:\s*(.+)$/mi', $body, $m ) ) {
			$out['version'] = trim( $m[1] );
		}
		if ( preg_match( '/^\s*(?:\*\s*)?Requires at least:\s*(.+)$/mi', $body, $m ) ) {
			$out['requires'] = trim( $m[1] );
		}
		if ( preg_match( '/^\s*(?:\*\s*)?Requires PHP:\s*(.+)$/mi', $body, $m ) ) {
			$out['requires_php'] = trim( $m[1] );
		}
		if ( preg_match( '/^\s*(?:\*\s*)?Tested up to:\s*(.+)$/mi', $body, $m ) ) {
			$out['tested'] = trim( $m[1] );
		}
		return $out;
	}

	/**
	 * GitHub API GET.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private static function github_get( $url ) {
		$headers = array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'InfyNewsOS-WordPress-Updater',
		);
		if ( defined( 'INOS_GITHUB_TOKEN' ) && INOS_GITHUB_TOKEN ) {
			$headers['Authorization'] = 'Bearer ' . INOS_GITHUB_TOKEN;
		}
		return self::http_get( $url, $headers );
	}

	/**
	 * HTTP GET body.
	 *
	 * @param string                $url     URL.
	 * @param array<string, string> $headers Headers.
	 * @return string
	 */
	private static function http_get( $url, $headers = array() ) {
		if ( empty( $headers['User-Agent'] ) ) {
			$headers['User-Agent'] = 'InfyNewsOS-WordPress-Updater';
		}
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 12,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			return '';
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return '';
		}
		return (string) wp_remote_retrieve_body( $response );
	}

	/**
	 * Whether this is Infy News OS Core.
	 *
	 * @param string $plugin_file Plugin basename.
	 * @return bool
	 */
	private static function is_core_plugin_file( $plugin_file ) {
		if ( defined( 'INOS_CORE_BASENAME' ) && INOS_CORE_BASENAME === $plugin_file ) {
			return true;
		}
		return 'infy-news-os-core/infy-news-os-core.php' === $plugin_file;
	}

	/**
	 * Drop cached GitHub version checks after an upgrade.
	 *
	 * @param WP_Upgrader          $upgrader   Upgrader.
	 * @param array<string, mixed> $hook_extra Extra.
	 */
	public static function clear_cache( $upgrader, $hook_extra ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		delete_site_transient( 'inos_gh_plugin' );
		delete_site_transient( 'inos_gh_theme' );
	}
}
