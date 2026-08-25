<?php
/**
 * Firebase Cloud Messaging web push for published articles.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Subscribe prompt, service worker, and FCM HTTP v1 send.
 */
class INOS_Push {

	const TOPIC     = 'inos-articles';
	const SDK       = '11.6.0';
	const META_SENT = '_inos_push_sent';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrites' ) );
		add_action( 'init', array( __CLASS__, 'maybe_create_table' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_service_worker' ), 0 );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 25 );
		add_action( 'wp_footer', array( __CLASS__, 'prompt' ), 20 );
		add_action( 'wp_after_insert_post', array( __CLASS__, 'maybe_queue' ), 30, 4 );
		add_action( 'inos_push_send', array( __CLASS__, 'send_post' ) );
		add_action( 'admin_post_inos_push_test', array( __CLASS__, 'handle_test' ) );
	}

	/**
	 * Root-scoped service worker URL (required by FCM).
	 */
	public static function register_rewrites() {
		global $wp_rewrite;
		if ( ! $wp_rewrite instanceof WP_Rewrite ) {
			return;
		}
		add_rewrite_rule( '^firebase-messaging-sw\.js$', 'index.php?inos_fcm_sw=1', 'top' );
	}

	/**
	 * Query var.
	 *
	 * @param string[] $vars Vars.
	 * @return string[]
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'inos_fcm_sw';
		return $vars;
	}

	/**
	 * Token table.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'inos_push_tokens';
	}

	/**
	 * Create token table.
	 */
	public static function create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_hash char(64) NOT NULL,
			token text NOT NULL,
			created_at datetime NOT NULL,
			last_seen datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY token_hash (token_hash)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Ensure table exists.
	 */
	public static function maybe_create_table() {
		global $wpdb;
		$table = self::table();
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $found !== $table ) {
			self::create_table();
		}
	}

	/**
	 * Whether push is configured and enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		if ( ! inos_get_option( 'enable_web_push', 0 ) ) {
			return false;
		}
		$config = self::web_config();
		return ( '' !== $config['apiKey'] && '' !== $config['projectId'] && '' !== $config['messagingSenderId'] && '' !== $config['appId'] && '' !== $config['vapidKey'] );
	}

	/**
	 * Public Firebase web config (never includes the service account).
	 *
	 * @return array<string, string>
	 */
	public static function web_config() {
		return array(
			'apiKey'            => trim( (string) inos_get_option( 'firebase_api_key', '' ) ),
			'authDomain'        => trim( (string) inos_get_option( 'firebase_auth_domain', '' ) ),
			'projectId'         => trim( (string) inos_get_option( 'firebase_project_id', '' ) ),
			'storageBucket'     => trim( (string) inos_get_option( 'firebase_storage_bucket', '' ) ),
			'messagingSenderId' => trim( (string) inos_get_option( 'firebase_messaging_sender_id', '' ) ),
			'appId'             => trim( (string) inos_get_option( 'firebase_app_id', '' ) ),
			'vapidKey'          => trim( (string) inos_get_option( 'firebase_vapid_key', '' ) ),
		);
	}

	/**
	 * Front assets. Skipped on AMP (custom JS is invalid there).
	 */
	public static function assets() {
		if ( is_admin() || ! self::is_enabled() ) {
			return;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		if ( ! is_ssl() && false === strpos( (string) wp_parse_url( home_url(), PHP_URL_HOST ), 'localhost' ) ) {
			return;
		}

		wp_enqueue_style(
			'inos-push',
			INOS_CORE_URL . 'public/css/inos-push.css',
			array(),
			INOS_CORE_VERSION
		);
		wp_enqueue_script(
			'inos-push',
			INOS_CORE_URL . 'public/js/inos-push.js',
			array(),
			INOS_CORE_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_localize_script(
			'inos-push',
			'inosPush',
			array(
				'config'   => self::web_config(),
				'sdk'      => self::SDK,
				'swUrl'    => home_url( '/firebase-messaging-sw.js' ),
				'rest'     => esc_url_raw( rest_url( 'inos/v1/push/subscribe' ) ),
				'nonce'    => wp_create_nonce( 'inos_push' ),
				'delay'    => max( 2, absint( inos_get_option( 'push_prompt_delay', 8 ) ) ) * 1000,
				'logo'     => esc_url_raw( self::logo_url() ),
				'title'    => inos_label( 'push_prompt_title' ),
				'text'     => inos_label( 'push_prompt_text' ),
				'allow'    => inos_label( 'push_allow' ),
				'dismiss'  => inos_label( 'push_not_now' ),
				'readMore' => inos_label( 'push_read_more' ),
			)
		);
	}

	/**
	 * Prompt markup is injected by JS; keep a noscript-free hook for CSS stacking.
	 */
	public static function prompt() {
		if ( is_admin() || ! self::is_enabled() ) {
			return;
		}
		if ( function_exists( 'inos_is_amp' ) && inos_is_amp() ) {
			return;
		}
		echo '<div id="inos-push-root" hidden></div>';
	}

	/**
	 * Serve firebase-messaging-sw.js at the domain root.
	 */
	public static function maybe_service_worker() {
		if ( ! get_query_var( 'inos_fcm_sw' ) ) {
			return;
		}

		$config = self::web_config();
		$js     = self::service_worker_js( $config );
		nocache_headers();
		header( 'Content-Type: application/javascript; charset=UTF-8' );
		header( 'Service-Worker-Allowed: /' );
		header( 'X-Robots-Tag: noindex' );
		header( 'Cache-Control: public, max-age=300' );
		echo $js; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Service worker source.
	 *
	 * @param array<string, string> $config Web config.
	 * @return string
	 */
	public static function service_worker_js( $config ) {
		$sdk    = self::SDK;
		$app    = wp_json_encode(
			array(
				'apiKey'            => $config['apiKey'],
				'authDomain'        => $config['authDomain'],
				'projectId'         => $config['projectId'],
				'storageBucket'     => $config['storageBucket'],
				'messagingSenderId' => $config['messagingSenderId'],
				'appId'             => $config['appId'],
			)
		);
		$action = wp_json_encode( inos_label( 'push_read_more' ) );

		return <<<JS
/* Infy News OS FCM service worker */
self.addEventListener('notificationclick', function (event) {
	event.notification.close();
	var url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';
	event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientsArr) {
		for (var i = 0; i < clientsArr.length; i++) {
			var client = clientsArr[i];
			if (client.url === url && 'focus' in client) {
				return client.focus();
			}
		}
		if (self.clients.openWindow) {
			return self.clients.openWindow(url);
		}
	}));
});

importScripts('https://www.gstatic.com/firebasejs/{$sdk}/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/{$sdk}/firebase-messaging-compat.js');

firebase.initializeApp({$app});
var messaging = firebase.messaging();
var readMore = {$action};

function inosShowPush(payload) {
	var data = Object.assign({}, payload.data || {});
	var n = payload.notification || {};
	var title = data.title || n.title || '';
	var options = {
		body: data.body || n.body || '',
		icon: data.icon || n.icon || '',
		image: data.image || n.image || '',
		badge: data.badge || data.icon || n.icon || '',
		tag: data.tag || 'inos-article',
		renotify: true,
		data: { url: data.url || (n.click_action ? n.click_action : '/') },
		actions: [{ action: 'read_more', title: readMore }]
	};
	if (!title) {
		return Promise.resolve();
	}
	return self.registration.showNotification(title, options);
}

messaging.onBackgroundMessage(function (payload) {
	return inosShowPush(payload);
});
JS;
	}

	/**
	 * REST routes.
	 */
	public static function register_rest() {
		register_rest_route(
			'inos/v1',
			'/push/subscribe',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_subscribe' ),
				'permission_callback' => array( __CLASS__, 'rest_can_subscribe' ),
			)
		);
	}

	/**
	 * REST subscribe nonce.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function rest_can_subscribe( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		return $nonce ? (bool) wp_verify_nonce( $nonce, 'inos_push' ) : false;
	}

	/**
	 * Store a token and attach it to the articles topic.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function rest_subscribe( $request ) {
		if ( ! self::is_enabled() ) {
			return new WP_REST_Response( array( 'ok' => false ), 400 );
		}
		$token = trim( (string) $request->get_param( 'token' ) );
		if ( strlen( $token ) < 20 || strlen( $token ) > 4096 ) {
			return new WP_REST_Response( array( 'ok' => false ), 400 );
		}

		self::save_token( $token );
		self::subscribe_topic( $token );

		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	/**
	 * Persist a registration token.
	 *
	 * @param string $token FCM token.
	 */
	public static function save_token( $token ) {
		global $wpdb;
		$hash  = hash( 'sha256', $token );
		$now   = current_time( 'mysql' );
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE token_hash = %s", $hash ) );
		if ( $id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$table,
				array(
					'token'     => $token,
					'last_seen' => $now,
				),
				array( 'id' => (int) $id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			return;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			array(
				'token_hash' => $hash,
				'token'      => $token,
				'created_at' => $now,
				'last_seen'  => $now,
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Subscribe a token to the articles topic.
	 *
	 * @param string $token Token.
	 */
	public static function subscribe_topic( $token ) {
		$access = self::access_token();
		if ( ! $access ) {
			return;
		}
		wp_remote_post(
			'https://iid.googleapis.com/iid/v1:batchAdd',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization'     => 'Bearer ' . $access,
					'Content-Type'      => 'application/json',
					'access_token_auth' => 'true',
				),
				'body'    => wp_json_encode(
					array(
						'to'                  => '/topics/' . self::TOPIC,
						'registration_tokens' => array( $token ),
					)
				),
			)
		);
	}

	/**
	 * Queue a push after an article is inserted/updated.
	 *
	 * @param int          $post_id     Post ID.
	 * @param WP_Post      $post        Post.
	 * @param bool         $update      Whether this is an update.
	 * @param WP_Post|null $post_before Previous post.
	 */
	public static function maybe_queue( $post_id, $post, $update, $post_before ) {
		unset( $update );
		if ( ! self::is_enabled() || ! ( $post instanceof WP_Post ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( 'publish' !== $post->post_status ) {
			return;
		}
		if ( ! in_array( $post->post_type, array( 'post', 'inos_live_blog' ), true ) ) {
			return;
		}

		$was_publish = $post_before instanceof WP_Post && 'publish' === $post_before->post_status;
		$already     = (bool) get_post_meta( $post_id, self::META_SENT, true );
		$want        = (string) get_post_meta( $post_id, '_inos_send_push', true );

		if ( $was_publish || $already ) {
			if ( '1' !== $want ) {
				return;
			}
		} elseif ( '0' === $want ) {
			return;
		}

		if ( wp_next_scheduled( 'inos_push_send', array( $post_id ) ) ) {
			return;
		}
		wp_schedule_single_event( time() + 8, 'inos_push_send', array( $post_id ) );
	}

	/**
	 * Send the article notification.
	 *
	 * @param int $post_id Post ID.
	 * @return true|WP_Error
	 */
	public static function send_post( $post_id ) {
		$post_id = absint( $post_id );
		$post    = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return new WP_Error( 'inos_push_skip', 'not published' );
		}
		if ( ! in_array( $post->post_type, array( 'post', 'inos_live_blog' ), true ) ) {
			return new WP_Error( 'inos_push_skip', 'wrong type' );
		}

		$payload = self::payload_for_post( $post );
		$result  = self::send_message( $payload );
		if ( ! is_wp_error( $result ) ) {
			update_post_meta( $post_id, self::META_SENT, time() );
			delete_post_meta( $post_id, '_inos_send_push' );
		}
		return $result;
	}

	/**
	 * Notification fields for an article.
	 *
	 * @param WP_Post $post Post.
	 * @return array<string, mixed>
	 */
	public static function payload_for_post( $post ) {
		$title = wp_strip_all_tags( get_the_title( $post ) );
		$body  = self::description_for_post( $post );
		$url   = get_permalink( $post );
		$icon  = self::logo_url();
		$image = '';
		$thumb = get_post_thumbnail_id( $post );
		if ( $thumb ) {
			$src = wp_get_attachment_image_url( $thumb, 'inos-card' );
			if ( ! $src ) {
				$src = wp_get_attachment_image_url( $thumb, 'large' );
			}
			$image = $src ? $src : '';
		}

		$data = array(
			'title'  => $title,
			'body'   => $body,
			'icon'   => $icon,
			'image'  => $image,
			'badge'  => $icon,
			'url'    => $url,
			'tag'    => 'inos-post-' . $post->ID,
			'postId' => (string) $post->ID,
		);

		return array(
			'topic'   => self::TOPIC,
			'data'    => $data,
			'webpush' => array(
				'headers'     => array(
					'Urgency' => 'high',
					'TTL'     => '86400',
				),
				'fcm_options' => array(
					'link' => $url,
				),
			),
		);
	}

	/**
	 * Short description: dek, then excerpt, then trimmed content.
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function description_for_post( $post ) {
		$dek = get_post_meta( $post->ID, '_inos_dek', true );
		if ( is_string( $dek ) && trim( $dek ) ) {
			return self::trim_text( $dek, 140 );
		}
		$excerpt = $post->post_excerpt;
		if ( is_string( $excerpt ) && trim( $excerpt ) ) {
			return self::trim_text( $excerpt, 140 );
		}
		return self::trim_text( wp_strip_all_tags( $post->post_content ), 140 );
	}

	/**
	 * Trim to a notification-sized sentence.
	 *
	 * @param string $text Text.
	 * @param int    $max  Max chars.
	 * @return string
	 */
	public static function trim_text( $text, $max = 140 ) {
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
		if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $max ) {
			return rtrim( mb_substr( $text, 0, $max - 1 ) ) . '…';
		}
		if ( strlen( $text ) > $max ) {
			return rtrim( substr( $text, 0, $max - 1 ) ) . '…';
		}
		return $text;
	}

	/**
	 * Site logo for the notification icon / badge.
	 *
	 * @return string
	 */
	public static function logo_url() {
		$logo_id = absint( inos_get_option( 'logo_id', 0 ) );
		if ( ! $logo_id ) {
			$logo_id = absint( get_theme_mod( 'custom_logo' ) );
		}
		if ( $logo_id ) {
			$url = wp_get_attachment_image_url( $logo_id, 'medium' );
			if ( $url ) {
				return $url;
			}
		}
		$icon = get_site_icon_url( 192 );
		return $icon ? $icon : '';
	}

	/**
	 * Send via FCM HTTP v1 (topic, then tokens if needed).
	 *
	 * @param array<string, mixed> $message Message body.
	 * @return true|WP_Error
	 */
	public static function send_message( $message ) {
		$project = trim( (string) inos_get_option( 'firebase_project_id', '' ) );
		$access  = self::access_token();
		if ( ! $project || ! $access ) {
			return new WP_Error( 'inos_push_auth', __( 'Firebase is not fully configured.', 'infy-news-os-core' ) );
		}

		$fcm = array( 'message' => self::stringify_data( $message ) );
		$res = self::fcm_post( $project, $access, $fcm );
		if ( ! is_wp_error( $res ) ) {
			return true;
		}

		$tokens = self::all_tokens();
		if ( ! $tokens ) {
			return $res;
		}

		$ok = 0;
		foreach ( $tokens as $token ) {
			$one              = $message;
			unset( $one['topic'] );
			$one['token']     = $token;
			$send             = self::fcm_post( $project, $access, array( 'message' => self::stringify_data( $one ) ) );
			if ( ! is_wp_error( $send ) ) {
				$ok++;
			}
		}
		return $ok ? true : $res;
	}

	/**
	 * FCM requires data values to be strings.
	 *
	 * @param array<string, mixed> $message Message.
	 * @return array<string, mixed>
	 */
	private static function stringify_data( $message ) {
		if ( empty( $message['data'] ) || ! is_array( $message['data'] ) ) {
			return $message;
		}
		foreach ( $message['data'] as $key => $value ) {
			$message['data'][ $key ] = (string) $value;
		}
		return $message;
	}

	/**
	 * POST messages:send.
	 *
	 * @param string               $project Project id.
	 * @param string               $access  Bearer token.
	 * @param array<string, mixed> $body    JSON body.
	 * @return true|WP_Error
	 */
	private static function fcm_post( $project, $access, $body ) {
		$url = 'https://fcm.googleapis.com/v1/projects/' . rawurlencode( $project ) . '/messages:send';
		$res = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'Authorization' => 'Bearer ' . $access,
					'Content-Type'  => 'application/json; charset=UTF-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = (int) wp_remote_retrieve_response_code( $res );
		if ( $code >= 200 && $code < 300 ) {
			return true;
		}
		$raw = (string) wp_remote_retrieve_body( $res );
		return new WP_Error( 'inos_push_fcm', $raw ? $raw : 'FCM HTTP ' . $code );
	}

	/**
	 * All stored tokens.
	 *
	 * @return string[]
	 */
	public static function all_tokens() {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_col( "SELECT token FROM {$table} ORDER BY last_seen DESC LIMIT 2000" );
		return is_array( $rows ) ? array_values( array_filter( array_map( 'strval', $rows ) ) ) : array();
	}

	/**
	 * Subscriber count.
	 *
	 * @return int
	 */
	public static function token_count() {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table}" );
	}

	/**
	 * OAuth token from the uploaded service account JSON.
	 *
	 * @return string
	 */
	public static function access_token() {
		$cached = get_transient( 'inos_fcm_access_token' );
		if ( is_string( $cached ) && $cached ) {
			return $cached;
		}
		$sa = self::service_account();
		if ( ! $sa ) {
			return '';
		}

		$now    = time();
		$header = self::b64url( wp_json_encode( array( 'alg' => 'RS256', 'typ' => 'JWT' ) ) );
		$claims = self::b64url(
			wp_json_encode(
				array(
					'iss'   => $sa['client_email'],
					'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
					'aud'   => 'https://oauth2.googleapis.com/token',
					'iat'   => $now,
					'exp'   => $now + 3600,
				)
			)
		);
		$unsigned = $header . '.' . $claims;
		$key      = openssl_pkey_get_private( $sa['private_key'] );
		if ( ! $key ) {
			return '';
		}
		$sig = '';
		if ( ! openssl_sign( $unsigned, $sig, $key, OPENSSL_ALGO_SHA256 ) ) {
			return '';
		}
		$jwt = $unsigned . '.' . self::b64url( $sig );

		$res = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			array(
				'timeout' => 15,
				'body'    => array(
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'assertion'  => $jwt,
				),
			)
		);
		if ( is_wp_error( $res ) ) {
			return '';
		}
		$body = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( empty( $body['access_token'] ) ) {
			return '';
		}
		$token = (string) $body['access_token'];
		set_transient( 'inos_fcm_access_token', $token, 50 * MINUTE_IN_SECONDS );
		return $token;
	}

	/**
	 * Decoded service account.
	 *
	 * @return array<string, string>|null
	 */
	public static function service_account() {
		$raw = (string) inos_get_option( 'firebase_service_account', '' );
		if ( '' === $raw ) {
			return null;
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) || empty( $data['private_key'] ) || empty( $data['client_email'] ) ) {
			return null;
		}
		return $data;
	}

	/**
	 * Base64url.
	 *
	 * @param string $data Bytes.
	 * @return string
	 */
	private static function b64url( $data ) {
		return rtrim( strtr( base64_encode( (string) $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Admin test send.
	 */
	public static function handle_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You cannot send a test notification.', 'infy-news-os-core' ) );
		}
		check_admin_referer( 'inos_push_test' );

		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'inos_live_blog' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
			)
		);
		if ( empty( $posts[0] ) ) {
			wp_safe_redirect( add_query_arg( 'inos_push_test', 'empty', admin_url( 'admin.php?page=inos-settings&tab=push' ) ) );
			exit;
		}

		$result = self::send_post( $posts[0]->ID );
		$code   = is_wp_error( $result ) ? 'fail' : 'ok';
		wp_safe_redirect( add_query_arg( 'inos_push_test', $code, admin_url( 'admin.php?page=inos-settings&tab=push' ) ) );
		exit;
	}
}
