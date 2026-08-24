<?php
/**
 * One-click demo content importer.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Imports a full newsroom demo: sections, authors, stories, live blog, menus.
 */
class INOS_Demo {

	const OPTION = 'inos_demo_state';
	const BATCH  = 4;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_ajax_inos_demo_import', array( __CLASS__, 'ajax_import' ) );
		add_action( 'admin_post_inos_demo_import', array( __CLASS__, 'handle_import_form' ) );
		add_action( 'admin_post_inos_demo_remove', array( __CLASS__, 'handle_remove' ) );
	}

	/**
	 * Stored import state.
	 *
	 * @return array<string, mixed>
	 */
	public static function state() {
		$state = get_option( self::OPTION, array() );
		$base  = array(
			'version'      => 1,
			'imported_at'  => '',
			'posts'        => array(),
			'attachments'  => array(),
			'users'        => array(),
			'terms'        => array(),
			'menus'        => array(),
			'live_blogs'   => array(),
			'live_updates' => array(),
			'web_stories'  => array(),
			'subscribers'  => array(),
			'cats'         => array(),
			'authors'      => array(),
			'settings_bak' => array(),
			'menus_bak'    => array(),
			'modules_bak'  => array(),
			'front_bak'    => '',
			'comments'     => array(),
		);
		return wp_parse_args( is_array( $state ) ? $state : array(), $base );
	}

	/**
	 * Whether a completed import exists.
	 *
	 * @return bool
	 */
	public static function is_imported() {
		$state = self::state();
		return ! empty( $state['imported_at'] ) && ! empty( $state['posts'] );
	}

	/**
	 * Persist state.
	 *
	 * @param array<string, mixed> $state State.
	 */
	private static function save_state( $state ) {
		update_option( self::OPTION, $state, false );
	}

	/**
	 * AJAX stepper.
	 */
	public static function ajax_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You cannot import demo content.', 'infy-news-os-core' ) ), 403 );
		}
		check_ajax_referer( 'inos_demo', 'nonce' );

		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		wp_raise_memory_limit( 'admin' );

		$step   = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : 'prepare';
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		try {
			if ( 'prepare' === $step ) {
				$result = self::step_prepare();
			} elseif ( 'posts' === $step ) {
				$result = self::step_posts( $offset );
			} elseif ( 'live' === $step ) {
				$result = self::step_live();
			} elseif ( 'stories' === $step ) {
				$result = self::step_stories();
			} elseif ( 'finish' === $step ) {
				$result = self::step_finish();
			} else {
				wp_send_json_error( array( 'message' => __( 'Unknown import step.', 'infy-news-os-core' ) ) );
			}
		} catch ( Throwable $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Run every import step in one request (CLI / fallback form).
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public static function import_all() {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		wp_raise_memory_limit( 'admin' );

		$prep = self::step_prepare();
		if ( is_wp_error( $prep ) ) {
			return $prep;
		}

		$offset = 0;
		$guard  = 0;
		do {
			$batch = self::step_posts( $offset );
			if ( is_wp_error( $batch ) ) {
				return $batch;
			}
			$offset = isset( $batch['offset'] ) ? (int) $batch['offset'] : 0;
			$next   = isset( $batch['next'] ) ? $batch['next'] : 'done';
			$guard++;
		} while ( 'posts' === $next && $guard < 40 );

		$live = self::step_live();
		if ( is_wp_error( $live ) ) {
			return $live;
		}

		$stories = self::step_stories();
		if ( is_wp_error( $stories ) ) {
			return $stories;
		}

		return self::step_finish();
	}

	/**
	 * Categories, tags, authors, policy pages.
	 *
	 * @return array<string, mixed>
	 */
	private static function step_prepare() {
		require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/user.php';

		$state = self::state();
		if ( empty( $state['settings_bak'] ) ) {
			$state['settings_bak'] = INOS_Settings::all();
			$state['menus_bak']    = get_theme_mod( 'nav_menu_locations', array() );
			$state['front_bak']    = (string) get_option( 'show_on_front', 'posts' );
			$state['modules_bak']  = get_option( INOS_Home_Builder::OPTION, array() );
		}

		INOS_Pages::seed();
		INOS_Taxonomies::register();

		$cats = array();
		foreach ( INOS_Demo_Catalog::categories() as $slug => $cat ) {
			$existing = get_term_by( 'slug', $slug, 'category' );
			if ( $existing ) {
				$cats[ $slug ] = (int) $existing->term_id;
				continue;
			}
			$inserted = wp_insert_term(
				$cat['name'],
				'category',
				array(
					'slug'        => $slug,
					'description' => $cat['description'],
				)
			);
			if ( is_wp_error( $inserted ) ) {
				return $inserted;
			}
			$cats[ $slug ]     = (int) $inserted['term_id'];
			$state['terms'][]  = (int) $inserted['term_id'];
		}
		$state['cats'] = $cats;

		foreach ( INOS_Demo_Catalog::tags() as $slug => $name ) {
			if ( get_term_by( 'slug', $slug, 'post_tag' ) ) {
				continue;
			}
			$inserted = wp_insert_term( $name, 'post_tag', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $inserted ) ) {
				$state['terms'][] = (int) $inserted['term_id'];
			}
		}

		$authors = array();
		foreach ( INOS_Demo_Catalog::authors() as $key => $author ) {
			$user = get_user_by( 'login', $author['login'] );
			if ( ! $user ) {
				$id = wp_insert_user(
					array(
						'user_login'   => $author['login'],
						'user_pass'    => wp_generate_password( 24, true, true ),
						'user_email'   => $author['email'],
						'display_name' => $author['name'],
						'first_name'   => explode( ' ', $author['name'] )[0],
						'last_name'    => implode( ' ', array_slice( explode( ' ', $author['name'] ), 1 ) ),
						'role'         => 'author',
						'description'  => $author['bio'],
						'user_url'     => ! empty( $author['sameas'] ) ? strtok( $author['sameas'], "\n" ) : '',
					)
				);
				if ( is_wp_error( $id ) ) {
					$user = get_user_by( 'email', $author['email'] );
					if ( ! $user ) {
						return $id;
					}
				} else {
					$state['users'][] = (int) $id;
					$user             = get_user_by( 'id', $id );
				}
			}
			if ( $user ) {
				update_user_meta( $user->ID, 'inos_job_title', $author['job'] );
				update_user_meta( $user->ID, 'inos_expertise', $author['expertise'] );
				update_user_meta( $user->ID, 'inos_short_bio', isset( $author['short_bio'] ) ? $author['short_bio'] : '' );
				update_user_meta( $user->ID, 'inos_location', isset( $author['location'] ) ? $author['location'] : '' );
				update_user_meta( $user->ID, 'inos_credentials', isset( $author['credentials'] ) ? $author['credentials'] : '' );
				update_user_meta( $user->ID, 'inos_awards', isset( $author['awards'] ) ? $author['awards'] : '' );
				update_user_meta( $user->ID, 'inos_languages', isset( $author['languages'] ) ? $author['languages'] : '' );
				update_user_meta( $user->ID, 'inos_twitter', isset( $author['twitter'] ) ? $author['twitter'] : '' );
				update_user_meta( $user->ID, 'inos_linkedin', isset( $author['linkedin'] ) ? $author['linkedin'] : '' );
				update_user_meta( $user->ID, 'inos_sameas', isset( $author['sameas'] ) ? $author['sameas'] : '' );
				update_user_meta( $user->ID, 'inos_started_year', isset( $author['started_year'] ) ? $author['started_year'] : '' );
				update_user_meta( $user->ID, 'inos_show_email', ! empty( $author['show_email'] ) ? '1' : '0' );
				update_user_meta( $user->ID, 'inos_demo_user', '1' );
				$authors[ $key ] = (int) $user->ID;
			}
		}
		$state['authors'] = $authors;
		self::save_state( $state );

		$total = count( INOS_Demo_Catalog::posts() );
		return array(
			'next'    => 'posts',
			'offset'  => 0,
			'total'   => $total,
			'label'   => __( 'Newsroom, sections, and authors are ready. Importing stories…', 'infy-news-os-core' ),
			'percent' => 12,
		);
	}

	/**
	 * Import a batch of posts.
	 *
	 * @param int $offset Offset.
	 * @return array<string, mixed>
	 */
	private static function step_posts( $offset ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$posts = INOS_Demo_Catalog::posts();
		$total = count( $posts );
		$state = self::state();
		$slice = array_slice( $posts, $offset, self::BATCH );

		if ( empty( $slice ) ) {
			return array(
				'next'    => 'live',
				'offset'  => 0,
				'label'   => __( 'Stories are in. Adding live coverage…', 'infy-news-os-core' ),
				'percent' => 64,
			);
		}

		foreach ( $slice as $item ) {
			$existing = get_page_by_path( $item['slug'], OBJECT, 'post' );
			if ( $existing ) {
				$state['posts'][] = (int) $existing->ID;
				self::save_state( $state );
				continue;
			}

			$author_id = ! empty( $state['authors'][ $item['author'] ] ) ? (int) $state['authors'][ $item['author'] ] : get_current_user_id();
			$cat_id    = ! empty( $state['cats'][ $item['category'] ] ) ? (int) $state['cats'][ $item['category'] ] : 0;
			$hours     = isset( $item['hours_ago'] ) ? (int) $item['hours_ago'] : 6;
			$gmt       = gmdate( 'Y-m-d H:i:s', time() - ( $hours * HOUR_IN_SECONDS ) );
			if ( ! empty( $item['blocks'] ) && is_array( $item['blocks'] ) ) {
				$content = self::blocks( $item['blocks'] );
			} else {
				$content = self::blocks( isset( $item['paragraphs'] ) ? $item['paragraphs'] : array() );
			}

			$id = wp_insert_post(
				array(
					'post_title'    => $item['title'],
					'post_name'     => $item['slug'],
					'post_status'   => 'publish',
					'post_type'     => 'post',
					'post_author'   => $author_id,
					'post_content'  => $content,
					'post_excerpt'  => isset( $item['dek'] ) ? $item['dek'] : '',
					'post_date'     => get_date_from_gmt( $gmt ),
					'post_date_gmt' => $gmt,
				),
				true
			);
			if ( is_wp_error( $id ) || ! $id ) {
				$message = is_wp_error( $id ) ? $id->get_error_message() : __( 'Could not insert a demo story.', 'infy-news-os-core' );
				return new WP_Error( 'inos_demo_post', $message );
			}

			$state['posts'][] = (int) $id;
			if ( $cat_id ) {
				wp_set_object_terms( $id, array( $cat_id ), 'category' );
			}
			if ( ! empty( $item['tags'] ) ) {
				wp_set_object_terms( $id, $item['tags'], 'post_tag' );
			}
			if ( ! empty( $item['type'] ) ) {
				wp_set_object_terms( $id, $item['type'], 'inos_article_type' );
			}

			update_post_meta( $id, '_inos_kicker', isset( $item['kicker'] ) ? $item['kicker'] : '' );
			update_post_meta( $id, '_inos_dek', isset( $item['dek'] ) ? $item['dek'] : '' );
			update_post_meta( $id, '_inos_dateline', isset( $item['dateline'] ) ? $item['dateline'] : '' );
			update_post_meta( $id, '_inos_primary_section', $cat_id );
			update_post_meta( $id, '_inos_seo_title', $item['title'] );
			update_post_meta( $id, '_inos_seo_description', isset( $item['dek'] ) ? $item['dek'] : '' );
			update_post_meta( $id, '_inos_views', isset( $item['views'] ) ? absint( $item['views'] ) : wp_rand( 200, 1500 ) );
			update_post_meta( $id, '_inos_demo', '1' );

			if ( ! empty( $item['breaking'] ) ) {
				update_post_meta( $id, '_inos_breaking', '1' );
				update_post_meta( $id, '_inos_breaking_until', time() + ( DAY_IN_SECONDS ) );
			}
			if ( ! empty( $item['exclusive'] ) ) {
				update_post_meta( $id, '_inos_exclusive', '1' );
			}
			if ( ! empty( $item['homepage_pin'] ) ) {
				update_post_meta( $id, '_inos_homepage_pin', '1' );
			}
			if ( ! empty( $item['trending_pin'] ) ) {
				update_post_meta( $id, '_inos_trending_pin', '1' );
			}
			if ( ! empty( $item['sponsored'] ) ) {
				update_post_meta( $id, '_inos_sponsored', '1' );
				update_post_meta( $id, '_inos_sponsored_label', isset( $item['sponsored_label'] ) ? $item['sponsored_label'] : __( 'Sponsored', 'infy-news-os-core' ) );
			}
			if ( ! empty( $item['correction'] ) ) {
				update_post_meta( $id, '_inos_correction', $item['correction'] );
				update_post_meta( $id, '_inos_correction_time', gmdate( 'c' ) );
			}

			$color = '#0b3d5c';
			if ( isset( $item['category'] ) && isset( INOS_Demo_Catalog::categories()[ $item['category'] ]['color'] ) ) {
				$color = INOS_Demo_Catalog::categories()[ $item['category'] ]['color'];
			}
			try {
				$att = self::attach_image( $id, $item['title'], $color );
			} catch ( Throwable $e ) {
				$att = 0;
			}
			if ( $att ) {
				$state['attachments'][] = $att;
				update_post_meta( $id, '_inos_image_credit', __( 'Demo illustration', 'infy-news-os-core' ) );
			}

			if ( ! empty( $item['inline_image'] ) ) {
				$inline = self::attach_image( (int) $id, $item['title'] . ' — in story', $color, 1200, 675, false );
				if ( $inline ) {
					$state['attachments'][] = $inline;
					$src                    = wp_get_attachment_image_url( $inline, 'large' );
					if ( $src ) {
						$content .= "\n\n<!-- wp:image {\"id\":{$inline},\"sizeSlug\":\"large\"} --><figure class=\"wp-block-image size-large\"><img src=\"" . esc_url( $src ) . '" alt="' . esc_attr( $item['title'] ) . "\" class=\"wp-image-{$inline}\"/></figure><!-- /wp:image -->";
						wp_update_post(
							array(
								'ID'           => (int) $id,
								'post_content' => $content,
							)
						);
					}
				}
			}

			self::save_state( $state );
		}

		$next_offset = $offset + self::BATCH;
		$done        = min( $next_offset, $total );
		$percent     = 10 + (int) floor( ( $done / max( 1, $total ) ) * 52 );

		if ( $next_offset < $total ) {
			return array(
				'next'    => 'posts',
				'offset'  => $next_offset,
				'total'   => $total,
				'label'   => sprintf(
					/* translators: 1: imported count, 2: total */
					__( 'Imported %1$d of %2$d stories…', 'infy-news-os-core' ),
					$done,
					$total
				),
				'percent' => $percent,
			);
		}

		return array(
			'next'    => 'live',
			'offset'  => 0,
			'total'   => $total,
			'label'   => __( 'Stories are in. Adding live coverage…', 'infy-news-os-core' ),
			'percent' => 64,
		);
	}

	/**
	 * Live blog.
	 *
	 * @return array<string, mixed>
	 */
	private static function step_live() {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$state = self::state();
		foreach ( INOS_Demo_Catalog::live_blogs() as $live ) {
			$exists = get_page_by_path( $live['slug'], OBJECT, 'inos_live_blog' );
			$key    = ! empty( $live['author'] ) ? $live['author'] : 'priya';
			$author = ! empty( $state['authors'][ $key ] ) ? (int) $state['authors'][ $key ] : get_current_user_id();

			if ( $exists ) {
				$parent = (int) $exists->ID;
				if ( ! in_array( $parent, $state['live_blogs'], true ) ) {
					$state['live_blogs'][] = $parent;
				}
			} else {
				$parent = wp_insert_post(
					array(
						'post_title'   => $live['title'],
						'post_name'    => $live['slug'],
						'post_status'  => 'publish',
						'post_type'    => 'inos_live_blog',
						'post_author'  => $author,
						'post_content' => self::blocks(
							array(
								$live['dek'],
								__( 'This is demo live coverage. Updates appear newest first and poll automatically on the public page.', 'infy-news-os-core' ),
							)
						),
						'post_excerpt' => $live['dek'],
					),
					true
				);
				if ( is_wp_error( $parent ) ) {
					return $parent;
				}
				$state['live_blogs'][] = (int) $parent;
				update_post_meta( $parent, '_inos_kicker', $live['kicker'] );
				update_post_meta( $parent, '_inos_dek', $live['dek'] );
				update_post_meta( $parent, '_inos_dateline', $live['dateline'] );
				update_post_meta( $parent, '_inos_demo', '1' );
				$color = ! empty( $live['color'] ) ? $live['color'] : '#0b3d5c';
				$att   = self::attach_image( (int) $parent, $live['title'], $color );
				if ( $att ) {
					$state['attachments'][] = $att;
				}
			}

			$cat_id = ! empty( $live['category'] ) && ! empty( $state['cats'][ $live['category'] ] ) ? (int) $state['cats'][ $live['category'] ] : 0;
			if ( $cat_id ) {
				wp_set_object_terms( (int) $parent, array( $cat_id ), 'category' );
				update_post_meta( (int) $parent, '_inos_primary_section', $cat_id );
			}
			if ( ! empty( $live['type'] ) ) {
				wp_set_object_terms( (int) $parent, $live['type'], 'inos_article_type' );
			}

			foreach ( $live['updates'] as $update ) {
			$slug = sanitize_title( $live['slug'] . '-' . $update['title'] );
			$have = get_page_by_path( $slug, OBJECT, 'inos_live_update' );
			if ( $have ) {
				continue;
			}
			$gmt = gmdate( 'Y-m-d H:i:s', time() - ( (int) $update['hours_ago'] * HOUR_IN_SECONDS ) );
			$uid = wp_insert_post(
				array(
					'post_title'    => $update['title'],
					'post_name'     => $slug,
					'post_status'   => 'publish',
					'post_type'     => 'inos_live_update',
					'post_parent'   => (int) $parent,
					'post_author'   => $author,
					'post_content'  => self::blocks( array( $update['body'] ) ),
					'post_date'     => get_date_from_gmt( $gmt ),
					'post_date_gmt' => $gmt,
				),
				true
			);
			if ( ! is_wp_error( $uid ) && $uid ) {
				$state['live_updates'][] = (int) $uid;
			}
		}
		}

		self::save_state( $state );

		return array(
			'next'    => 'stories',
			'offset'  => 0,
			'label'   => __( 'Live coverage is in. Adding Web Stories and media…', 'infy-news-os-core' ),
			'percent' => 72,
		);
	}

	/**
	 * Official Web Stories posts, posters, and extra media-library stills.
	 *
	 * @return array<string, mixed>
	 */
	private static function step_stories() {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$state = self::state();

		foreach ( INOS_Demo_Catalog::extra_media() as $still ) {
			$have = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'title'          => $still['title'],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);
			if ( $have ) {
				$state['attachments'][] = (int) $have[0];
				continue;
			}
			$att = self::attach_image( 0, $still['title'], $still['color'], 1600, 900, false );
			if ( $att ) {
				$state['attachments'][] = $att;
			}
		}

		if ( post_type_exists( 'web-story' ) ) {
			$author = ! empty( $state['authors']['priya'] ) ? (int) $state['authors']['priya'] : get_current_user_id();
			foreach ( INOS_Demo_Catalog::web_stories() as $item ) {
				$existing = get_page_by_path( $item['slug'], OBJECT, 'web-story' );
				if ( $existing ) {
					if ( ! in_array( (int) $existing->ID, $state['web_stories'], true ) ) {
						$state['web_stories'][] = (int) $existing->ID;
					}
					continue;
				}

				$hours = isset( $item['hours_ago'] ) ? (int) $item['hours_ago'] : wp_rand( 2, 18 );
				$gmt   = gmdate( 'Y-m-d H:i:s', time() - ( $hours * HOUR_IN_SECONDS ) );
				$kses  = function_exists( 'kses_remove_filters' );
				if ( $kses ) {
					kses_remove_filters();
				}
				$id = wp_insert_post(
					array(
						'post_title'            => $item['title'],
						'post_name'             => $item['slug'],
						'post_status'           => 'publish',
						'post_type'             => 'web-story',
						'post_author'           => $author,
						'post_excerpt'          => isset( $item['dek'] ) ? $item['dek'] : '',
						'post_content'          => '<!-- pending -->',
						'post_content_filtered' => '{}',
						'post_date'             => get_date_from_gmt( $gmt ),
						'post_date_gmt'         => $gmt,
					),
					true
				);
				if ( $kses ) {
					kses_init_filters();
				}
				if ( is_wp_error( $id ) || ! $id ) {
					continue;
				}

				$state['web_stories'][] = (int) $id;
				update_post_meta( $id, '_inos_demo', '1' );

				$poster = self::attach_image( (int) $id, $item['title'], $item['color'], 720, 1280, true );
				$src    = '';
				$pw     = 720;
				$ph     = 1280;
				if ( $poster ) {
					$state['attachments'][] = $poster;
					$src                    = (string) wp_get_attachment_url( $poster );
					$meta                   = wp_get_attachment_metadata( $poster );
					if ( is_array( $meta ) ) {
						$pw = ! empty( $meta['width'] ) ? (int) $meta['width'] : 720;
						$ph = ! empty( $meta['height'] ) ? (int) $meta['height'] : 1280;
					}
					update_post_meta(
						$id,
						'web_stories_poster',
						array(
							'url'        => $src,
							'width'      => $pw,
							'height'     => $ph,
							'needsProxy' => false,
						)
					);
				}

				$html = self::story_markup( $item, (int) $id, $src );
				$json = wp_json_encode( self::story_data( $item ) );
				if ( $kses ) {
					kses_remove_filters();
				}
				wp_update_post(
					array(
						'ID'                    => (int) $id,
						'post_content'          => $html,
						'post_content_filtered' => $json ? $json : '{}',
					)
				);
				if ( $kses ) {
					kses_init_filters();
				}
			}
		}

		self::save_state( $state );

		$label = post_type_exists( 'web-story' )
			? __( 'Web Stories and media are in. Wiring menus and homepage…', 'infy-news-os-core' )
			: __( 'Media library stills are in. Activate Web Stories to import AMP stories next time. Wiring menus…', 'infy-news-os-core' );

		return array(
			'next'    => 'finish',
			'offset'  => 0,
			'label'   => $label,
			'percent' => 88,
		);
	}

	/**
	 * Menus, widgets, homepage, comments, subscribers.
	 *
	 * @return array<string, mixed>
	 */
	private static function step_finish() {
		$state = self::state();
		if ( empty( $state['posts'] ) ) {
			return new WP_Error( 'inos_demo_empty', __( 'No stories were imported. Try Import again.', 'infy-news-os-core' ) );
		}
		self::build_menus( $state );
		self::build_sidebar();
		self::apply_homepage( $state );
		self::seed_comments( $state );
		self::seed_subscribers( $state );

		$state['posts']        = array_values( array_unique( array_map( 'intval', $state['posts'] ) ) );
		$state['attachments']  = array_values( array_unique( array_map( 'intval', $state['attachments'] ) ) );
		$state['web_stories']  = array_values( array_unique( array_map( 'intval', $state['web_stories'] ) ) );
		$state['live_blogs']   = array_values( array_unique( array_map( 'intval', $state['live_blogs'] ) ) );
		$state['imported_at']  = current_time( 'mysql' );
		self::save_state( $state );

		update_option( 'show_on_front', 'posts' );
		flush_rewrite_rules( false );

		return array(
			'next'     => 'done',
			'percent'  => 100,
			'label'    => __( 'Demo newsroom is ready.', 'infy-news-os-core' ),
			'home'     => home_url( '/' ),
			'live'     => ! empty( $state['live_blogs'][0] ) ? get_permalink( $state['live_blogs'][0] ) : home_url( '/live/' ),
			'customize'=> add_query_arg(
				array(
					'autofocus[panel]' => 'inos_homepage',
					'url'              => home_url( '/' ),
				),
				admin_url( 'customize.php' )
			),
		);
	}

	/**
	 * Block markup from paragraphs or structured blocks.
	 *
	 * @param array<int, string|array<string, mixed>> $paragraphs Text or block maps.
	 * @return string
	 */
	private static function blocks( $paragraphs ) {
		$out = '';
		foreach ( $paragraphs as $p ) {
			if ( is_array( $p ) ) {
				$out .= self::block_item( $p );
				continue;
			}
			$p    = wp_kses_post( $p );
			$out .= "<!-- wp:paragraph --><p>{$p}</p><!-- /wp:paragraph -->\n\n";
		}
		return trim( $out );
	}

	/**
	 * One structured demo block.
	 *
	 * @param array<string, mixed> $block Block.
	 * @return string
	 */
	private static function block_item( $block ) {
		$type = isset( $block['type'] ) ? sanitize_key( $block['type'] ) : 'p';
		if ( 'heading' === $type ) {
			$text = wp_kses_post( isset( $block['text'] ) ? $block['text'] : '' );
			return "<!-- wp:heading --><h2 class=\"wp-block-heading\">{$text}</h2><!-- /wp:heading -->\n\n";
		}
		if ( 'quote' === $type ) {
			$text = wp_kses_post( isset( $block['text'] ) ? $block['text'] : '' );
			$cite = isset( $block['cite'] ) ? wp_kses_post( $block['cite'] ) : '';
			$cite_html = $cite ? "<cite>{$cite}</cite>" : '';
			return "<!-- wp:quote --><blockquote class=\"wp-block-quote\"><p>{$text}</p>{$cite_html}</blockquote><!-- /wp:quote -->\n\n";
		}
		if ( 'list' === $type ) {
			$items = isset( $block['items'] ) && is_array( $block['items'] ) ? $block['items'] : array();
			$lis   = '';
			foreach ( $items as $item ) {
				$lis .= '<li>' . wp_kses_post( $item ) . '</li>';
			}
			return "<!-- wp:list --><ul class=\"wp-block-list\">{$lis}</ul><!-- /wp:list -->\n\n";
		}
		$text = wp_kses_post( isset( $block['text'] ) ? $block['text'] : '' );
		return "<!-- wp:paragraph --><p>{$text}</p><!-- /wp:paragraph -->\n\n";
	}

	/**
	 * Generate a featured or library image.
	 *
	 * @param int    $post_id   Post, or 0 for unattached media.
	 * @param string $title     Title.
	 * @param string $hex       Background.
	 * @param int    $w         Width.
	 * @param int    $h         Height.
	 * @param bool   $set_thumb Set as featured image.
	 * @return int Attachment ID.
	 */
	private static function attach_image( $post_id, $title, $hex, $w = 1200, $h = 675, $set_thumb = true ) {
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			return 0;
		}

		$w  = max( 320, absint( $w ) );
		$h  = max( 240, absint( $h ) );
		$im = imagecreatetruecolor( $w, $h );
		if ( ! $im ) {
			return 0;
		}

		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		imagefilledrectangle( $im, 0, 0, $w, $h, imagecolorallocate( $im, $r, $g, $b ) );
		$bar = ( $h > $w ) ? 16 : 28;
		if ( $h > $w ) {
			imagefilledrectangle( $im, 0, 0, $w, $bar, imagecolorallocate( $im, 180, 35, 24 ) );
		} else {
			imagefilledrectangle( $im, 0, 0, $bar, $h, imagecolorallocate( $im, 180, 35, 24 ) );
		}
		$white  = imagecolorallocate( $im, 255, 255, 255 );
		$kicker = imagecolorallocate( $im, 245, 220, 200 );

		$font = '';
		foreach ( array( 'C:\\Windows\\Fonts\\georgia.ttf', 'C:\\Windows\\Fonts\\arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf' ) as $candidate ) {
			if ( is_readable( $candidate ) ) {
				$font = $candidate;
				break;
			}
		}

		$brand     = get_bloginfo( 'name' );
		$wrap      = ( $h > $w ) ? 18 : 42;
		$lines     = self::wrap_title( $title, $wrap );
		$size_head = ( $h > $w ) ? 28 : 36;
		$pad_x     = ( $h > $w ) ? 48 : 64;
		$brand_y   = ( $h > $w ) ? 80 : 80;
		$title_y   = ( $h > $w ) ? (int) ( $h * 0.38 ) : 220;
		$foot_y    = (int) ( $h - 55 );

		if ( $font && function_exists( 'imagettftext' ) ) {
			imagettftext( $im, 16, 0, $pad_x, $brand_y, $kicker, $font, $brand );
			$y = $title_y;
			foreach ( $lines as $line ) {
				imagettftext( $im, $size_head, 0, $pad_x, $y, $white, $font, $line );
				$y += (int) ( $size_head + 16 );
			}
			imagettftext( $im, 14, 0, $pad_x, $foot_y, $kicker, $font, __( 'Demo content', 'infy-news-os-core' ) );
		} else {
			imagestring( $im, 5, $pad_x, 60, $brand, $kicker );
			$y = (int) ( $h * 0.3 );
			foreach ( $lines as $line ) {
				imagestring( $im, 5, $pad_x, $y, $line, $white );
				$y += 28;
			}
		}

		$tmp = wp_tempnam( 'inos-demo.jpg' );
		if ( ! $tmp ) {
			imagedestroy( $im );
			return 0;
		}
		imagejpeg( $im, $tmp, 86 );
		imagedestroy( $im );

		$file_array = array(
			'name'     => sanitize_file_name( $title ) . '.jpg',
			'tmp_name' => $tmp,
		);
		$att_id     = media_handle_sideload( $file_array, $post_id );
		if ( is_wp_error( $att_id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return 0;
		}
		if ( $set_thumb && $post_id ) {
			set_post_thumbnail( $post_id, $att_id );
		}
		return (int) $att_id;
	}

	/**
	 * Wrap a headline.
	 *
	 * @param string $title Title.
	 * @param int    $width Width.
	 * @return string[]
	 */
	private static function wrap_title( $title, $width ) {
		$words = preg_split( '/\s+/', wp_strip_all_tags( $title ) );
		$lines = array();
		$cur   = '';
		foreach ( $words as $word ) {
			$try = $cur ? $cur . ' ' . $word : $word;
			if ( strlen( $try ) > $width && $cur ) {
				$lines[] = $cur;
				$cur     = $word;
			} else {
				$cur = $try;
			}
		}
		if ( $cur ) {
			$lines[] = $cur;
		}
		return array_slice( $lines, 0, 4 );
	}

	/**
	 * Full AMP HTML document for an official Web Story.
	 *
	 * @param array<string, mixed> $item Catalog row.
	 * @param int                  $id   Post ID.
	 * @param string               $poster Poster URL.
	 * @return string
	 */
	private static function story_markup( $item, $id, $poster ) {
		$title     = wp_strip_all_tags( $item['title'] );
		$publisher = wp_strip_all_tags( get_bloginfo( 'name' ) );
		$canonical = get_permalink( $id );
		if ( ! $canonical ) {
			$canonical = home_url( user_trailingslashit( 'web-stories/' . $item['slug'] ) );
		}
		$poster    = $poster ? $poster : '';
		$logo      = $poster;
		$icon      = get_site_icon_url( 96 );
		if ( $icon ) {
			$logo = $icon;
		}
		$lang = get_bloginfo( 'language' );
		$pages = isset( $item['pages'] ) && is_array( $item['pages'] ) ? $item['pages'] : array();
		if ( ! $pages ) {
			$pages = array(
				array(
					'kicker' => '',
					'text'   => isset( $item['dek'] ) ? $item['dek'] : $title,
				),
			);
		}

		$rgb   = self::hex_rgb( isset( $item['color'] ) ? $item['color'] : '#0b3d5c' );
		$bg    = sprintf( 'rgb(%d,%d,%d)', $rgb[0], $rgb[1], $rgb[2] );
		$pages_html = '';
		$i = 0;
		foreach ( $pages as $page ) {
			$i++;
			$pid    = 'page-' . $i;
			$kicker = isset( $page['kicker'] ) ? wp_strip_all_tags( $page['kicker'] ) : '';
			$text   = isset( $page['text'] ) ? wp_strip_all_tags( $page['text'] ) : '';
			$img    = '';
			if ( $poster ) {
				$img = '<amp-story-grid-layer template="fill"><amp-img src="' . esc_url( $poster ) . '" width="720" height="1280" layout="responsive" alt=""></amp-img></amp-story-grid-layer>';
			}
			$pages_html .= '<amp-story-page id="' . esc_attr( $pid ) . '">';
			$pages_html .= $img;
			$pages_html .= '<amp-story-grid-layer template="vertical" style="background:linear-gradient(180deg,rgba(0,0,0,0.15) 0%,rgba(0,0,0,0.72) 100%)">';
			if ( $kicker ) {
				$pages_html .= '<p style="color:#f5dcc8;font-size:14px;letter-spacing:.12em;text-transform:uppercase;margin:48px 32px 12px">' . esc_html( $kicker ) . '</p>';
			}
			if ( 1 === $i ) {
				$pages_html .= '<h1 style="color:#fff;font-size:32px;line-height:1.2;margin:0 32px 16px">' . esc_html( $title ) . '</h1>';
			}
			$pages_html .= '<p style="color:#fff;font-size:22px;line-height:1.35;margin:0 32px">' . esc_html( $text ) . '</p>';
			$pages_html .= '</amp-story-grid-layer></amp-story-page>';
		}

		$boilerplate = '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';

		$html  = '<!doctype html><html amp lang="' . esc_attr( $lang ) . '"><head>';
		$html .= '<meta charset="utf-8">';
		$html .= '<script async src="https://cdn.ampproject.org/v0.js"></script>';
		$html .= '<script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-1.0.js"></script>';
		$html .= '<title>' . esc_html( $title ) . '</title>';
		$html .= '<link rel="canonical" href="' . esc_url( $canonical ) . '">';
		$html .= '<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">';
		$html .= $boilerplate;
		$html .= '<style amp-custom>amp-story-page{background:' . esc_attr( $bg ) . '}</style>';
		$html .= '</head><body>';
		$html .= '<amp-story standalone title="' . esc_attr( $title ) . '" publisher="' . esc_attr( $publisher ) . '" publisher-logo-src="' . esc_url( $logo ) . '" poster-portrait-src="' . esc_url( $poster ) . '">';
		$html .= $pages_html;
		$html .= '</amp-story></body></html>';
		return $html;
	}

	/**
	 * Minimal story_data JSON for the official editor.
	 *
	 * @param array<string, mixed> $item Catalog row.
	 * @return array<string, mixed>
	 */
	private static function story_data( $item ) {
		$rgb   = self::hex_rgb( isset( $item['color'] ) ? $item['color'] : '#0b3d5c' );
		$pages = isset( $item['pages'] ) && is_array( $item['pages'] ) ? $item['pages'] : array();
		$out   = array();
		$n     = 0;
		foreach ( $pages as $page ) {
			$n++;
			$pid  = 'page-' . $n;
			$text = isset( $page['text'] ) ? wp_strip_all_tags( $page['text'] ) : '';
			if ( 1 === $n ) {
				$text = wp_strip_all_tags( $item['title'] ) . ' — ' . $text;
			}
			$out[] = array(
				'id'              => $pid,
				'backgroundColor' => array(
					'color' => array(
						'r' => $rgb[0],
						'g' => $rgb[1],
						'b' => $rgb[2],
					),
				),
				'elements'        => array(
					array(
						'id'                 => $pid . '-bg',
						'type'               => 'shape',
						'x'                  => 0,
						'y'                  => 0,
						'width'              => 1,
						'height'             => 1,
						'rotationAngle'      => 0,
						'lockAspectRatio'    => true,
						'isBackground'       => true,
						'isDefaultBackground'=> true,
						'backgroundColor'    => array(
							'color' => array(
								'r' => $rgb[0],
								'g' => $rgb[1],
								'b' => $rgb[2],
								'a' => 1,
							),
						),
					),
					array(
						'id'                => $pid . '-text',
						'type'              => 'text',
						'x'                 => 32,
						'y'                 => 280,
						'width'             => 328,
						'height'            => 240,
						'rotationAngle'     => 0,
						'content'           => '<span>' . esc_html( $text ) . '</span>',
						'color'             => array(
							'color' => array(
								'r' => 255,
								'g' => 255,
								'b' => 255,
							),
						),
						'font'              => array(
							'family'   => 'Roboto',
							'service'  => 'fonts.google.com',
							'weights'  => array( 400, 700 ),
							'styles'   => array( 'regular' ),
							'variants' => array( array( 0, 400 ), array( 0, 700 ) ),
						),
						'fontSize'          => 28,
						'backgroundTextMode'=> 'NONE',
						'textAlign'         => 'left',
						'padding'           => array(
							'horizontal' => 0,
							'vertical'   => 0,
						),
					),
				),
			);
		}
		return array(
			'version' => 44,
			'pages'   => $out,
		);
	}

	/**
	 * Hex to RGB.
	 *
	 * @param string $hex Color.
	 * @return int[]
	 */
	private static function hex_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return array( 11, 61, 92 );
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Primary + footer menus.
	 *
	 * @param array<string, mixed> $state State (by value, saved inside).
	 */
	private static function build_menus( &$state ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		$primary = wp_get_nav_menu_object( 'Infy Demo Primary' );
		if ( ! $primary ) {
			$mid = wp_create_nav_menu( 'Infy Demo Primary' );
			if ( ! is_wp_error( $mid ) ) {
				$state['menus'][] = (int) $mid;
				wp_update_nav_menu_item(
					$mid,
					0,
					array(
						'menu-item-title'  => __( 'Home', 'infy-news-os-core' ),
						'menu-item-url'    => home_url( '/' ),
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
					)
				);
				$about = get_page_by_path( 'about' );
				if ( $about ) {
					wp_update_nav_menu_item(
						$mid,
						0,
						array(
							'menu-item-title'     => __( 'About', 'infy-news-os-core' ),
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $about->ID,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
				if ( ! empty( $state['live_blogs'] ) ) {
					foreach ( $state['live_blogs'] as $live_id ) {
						wp_update_nav_menu_item(
							$mid,
							0,
							array(
								'menu-item-title'     => get_the_title( (int) $live_id ),
								'menu-item-object'    => 'inos_live_blog',
								'menu-item-object-id' => (int) $live_id,
								'menu-item-type'      => 'post_type',
								'menu-item-status'    => 'publish',
							)
						);
					}
				}
				if ( post_type_exists( 'web-story' ) ) {
					$archive = get_post_type_archive_link( 'web-story' );
					if ( $archive ) {
						wp_update_nav_menu_item(
							$mid,
							0,
							array(
								'menu-item-title'  => __( 'Stories', 'infy-news-os-core' ),
								'menu-item-url'    => $archive,
								'menu-item-status' => 'publish',
								'menu-item-type'   => 'custom',
							)
						);
					}
				}
				$contact = get_page_by_path( 'contact' );
				if ( $contact ) {
					wp_update_nav_menu_item(
						$mid,
						0,
						array(
							'menu-item-title'     => __( 'Contact', 'infy-news-os-core' ),
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $contact->ID,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
				$locations['primary'] = (int) $mid;
			}
		} else {
			$locations['primary'] = (int) $primary->term_id;
		}

		$footer = wp_get_nav_menu_object( 'Infy Demo Footer' );
		if ( ! $footer ) {
			$fid = wp_create_nav_menu( 'Infy Demo Footer' );
			if ( ! is_wp_error( $fid ) ) {
				$state['menus'][] = (int) $fid;
				foreach ( array( 'about', 'editorial-policy', 'corrections', 'contact' ) as $slug ) {
					$page = get_page_by_path( $slug );
					if ( ! $page ) {
						continue;
					}
					wp_update_nav_menu_item(
						$fid,
						0,
						array(
							'menu-item-title'     => get_the_title( $page ),
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $page->ID,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
				$locations['footer'] = (int) $fid;
			}
		} else {
			$locations['footer'] = (int) $footer->term_id;
		}

		$mobile = wp_get_nav_menu_object( 'Infy Demo Mobile' );
		if ( ! $mobile ) {
			$did = wp_create_nav_menu( 'Infy Demo Mobile' );
			if ( ! is_wp_error( $did ) ) {
				$state['menus'][] = (int) $did;
				wp_update_nav_menu_item(
					$did,
					0,
					array(
						'menu-item-title'  => __( 'Home', 'infy-news-os-core' ),
						'menu-item-url'    => home_url( '/' ),
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
					)
				);
				foreach ( array( 'technology', 'business', 'science', 'world' ) as $slug ) {
					if ( empty( $state['cats'][ $slug ] ) ) {
						continue;
					}
					wp_update_nav_menu_item(
						$did,
						0,
						array(
							'menu-item-title'     => INOS_Demo_Catalog::categories()[ $slug ]['name'],
							'menu-item-object'    => 'category',
							'menu-item-object-id' => (int) $state['cats'][ $slug ],
							'menu-item-type'      => 'taxonomy',
							'menu-item-status'    => 'publish',
						)
					);
				}
				$about = get_page_by_path( 'about' );
				if ( $about ) {
					wp_update_nav_menu_item(
						$did,
						0,
						array(
							'menu-item-title'     => get_the_title( $about ),
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $about->ID,
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
				$locations['drawer'] = (int) $did;
			}
		} else {
			$locations['drawer'] = (int) $mobile->term_id;
		}

		$sections_menu = wp_get_nav_menu_object( 'Infy Demo Sections' );
		if ( ! $sections_menu ) {
			$sid = wp_create_nav_menu( 'Infy Demo Sections' );
			if ( ! is_wp_error( $sid ) ) {
				$state['menus'][] = (int) $sid;
				$order            = array( 'technology', 'business', 'science', 'world', 'culture', 'opinion' );
				foreach ( $order as $slug ) {
					if ( empty( $state['cats'][ $slug ] ) ) {
						continue;
					}
					wp_update_nav_menu_item(
						$sid,
						0,
						array(
							'menu-item-title'     => INOS_Demo_Catalog::categories()[ $slug ]['name'],
							'menu-item-object'    => 'category',
							'menu-item-object-id' => (int) $state['cats'][ $slug ],
							'menu-item-type'      => 'taxonomy',
							'menu-item-status'    => 'publish',
						)
					);
				}
				$locations['sections'] = (int) $sid;
			}
		} else {
			$locations['sections'] = (int) $sections_menu->term_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );
		self::save_state( $state );
	}

	/**
	 * Sidebar widgets.
	 */
	private static function build_sidebar() {
		$sidebars = get_option( 'sidebars_widgets', array() );
		if ( ! is_array( $sidebars ) ) {
			$sidebars = array();
		}
		if ( empty( $sidebars['sidebar-1'] ) || ! is_array( $sidebars['sidebar-1'] ) ) {
			$sidebars['sidebar-1'] = array();
		}

		$search = get_option( 'widget_search', array() );
		if ( ! is_array( $search ) ) {
			$search = array();
		}
		if ( empty( $search[21] ) ) {
			$search[21] = array( 'title' => '' );
			update_option( 'widget_search', $search );
		}
		if ( ! in_array( 'search-21', $sidebars['sidebar-1'], true ) ) {
			$sidebars['sidebar-1'][] = 'search-21';
		}

		$recent = get_option( 'widget_recent-posts', array() );
		if ( ! is_array( $recent ) ) {
			$recent = array();
		}
		if ( empty( $recent[21] ) ) {
			$recent[21] = array(
				'title'  => __( 'Latest', 'infy-news-os-core' ),
				'number' => 5,
			);
			update_option( 'widget_recent-posts', $recent );
		}
		if ( ! in_array( 'recent-posts-21', $sidebars['sidebar-1'], true ) ) {
			$sidebars['sidebar-1'][] = 'recent-posts-21';
		}

		update_option( 'sidebars_widgets', $sidebars );
	}

	/**
	 * Homepage customizer values.
	 *
	 * @param array<string, mixed> $state State.
	 */
	private static function apply_homepage( $state ) {
		$order = array( 'technology', 'business', 'science', 'world', 'culture', 'opinion' );
		$all   = INOS_Settings::all();
		$i     = 1;
		foreach ( $order as $slug ) {
			$all[ 'section_' . $i ] = ! empty( $state['cats'][ $slug ] ) ? (int) $state['cats'][ $slug ] : 0;
			$i++;
		}
		$all['section_rows']         = implode( ',', array_filter( array( $all['section_1'], $all['section_2'], $all['section_3'], $all['section_4'], $all['section_5'], $all['section_6'] ) ) );
		$all['section_count']        = 4;
		$all['section_style']        = 'cards';
		$all['show_hero']            = 1;
		$all['hero_layout']          = 'lead-grid';
		$all['secondary_count']      = 4;
		$all['show_latest']          = 1;
		$all['show_trending']        = 1;
		$all['trending_source']      = 'views';
		$all['trending_count']       = 6;
		$all['show_breaking_ticker'] = 1;
		$all['show_subscribe_cta']   = 1;
		$all['show_home_newsletter'] = 1;
		$all['home_kicker']          = __( 'Today’s briefing', 'infy-news-os-core' );
		$all['home_intro']           = __( 'Original reporting across technology, business, science, and culture. This homepage is filled with demo stories so you can judge the layout.', 'infy-news-os-core' );

		$lead = 0;
		foreach ( $state['posts'] as $pid ) {
			if ( '1' === (string) get_post_meta( $pid, '_inos_homepage_pin', true ) ) {
				$lead = (int) $pid;
				break;
			}
		}
		if ( ! $lead && ! empty( $state['posts'][0] ) ) {
			$lead = (int) $state['posts'][0];
		}
		$all['lead_post_id'] = $lead;
		$all['show_home_web_stories'] = 1;
		$all['web_stories_view']      = 'circles';
		$all['web_stories_count']     = 8;
		$all['web_stories_title']     = __( 'Stories', 'infy-news-os-core' );

		update_option( INOS_CORE_OPTION, $all );

		$tab_ids = array();
		foreach ( array( 'technology', 'business', 'science', 'world' ) as $slug ) {
			if ( ! empty( $state['cats'][ $slug ] ) ) {
				$tab_ids[] = (int) $state['cats'][ $slug ];
			}
		}

		$mods   = array();
		$mods[] = INOS_Home_Builder::blank(
			'intro',
			array(
				'title'    => $all['home_kicker'],
				'subtitle' => $all['home_intro'],
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'hero',
			array(
				'layout'       => 'lead-grid',
				'count'        => 4,
				'show_excerpt' => 1,
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'web_stories',
			array(
				'enabled' => ( post_type_exists( 'web-story' ) && ! empty( $state['web_stories'] ) ) ? 1 : 0,
				'title'   => __( 'Stories', 'infy-news-os-core' ),
				'count'   => 8,
				'layout'  => 'circles',
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'live',
			array(
				'enabled'   => ! empty( $state['live_blogs'] ) ? 1 : 0,
				'title'     => __( 'Live coverage', 'infy-news-os-core' ),
				'count'     => 4,
				'layout'    => 'cards',
				'show_more' => 1,
			)
		);
		foreach ( $order as $slug ) {
			$cat_id = ! empty( $state['cats'][ $slug ] ) ? (int) $state['cats'][ $slug ] : 0;
			$term   = $cat_id ? get_term( $cat_id, 'category' ) : null;
			$mods[] = INOS_Home_Builder::blank(
				'category',
				array(
					'title'     => ( $term && ! is_wp_error( $term ) ) ? $term->name : '',
					'category'  => $cat_id,
					'count'     => 4,
					'layout'    => 'cards',
					'show_more' => 1,
				)
			);
		}
		$mods[] = INOS_Home_Builder::blank(
			'tabs',
			array(
				'title'  => __( 'By section', 'infy-news-os-core' ),
				'tabs'   => implode( ',', $tab_ids ),
				'count'  => 4,
				'layout' => 'cards',
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'split',
			array(
				'layout' => 'latest-trending',
				'count'  => 8,
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'authors',
			array(
				'title' => __( 'The newsroom', 'infy-news-os-core' ),
				'count' => 6,
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'topics',
			array(
				'title' => __( 'Topics', 'infy-news-os-core' ),
				'count' => 8,
			)
		);
		$mods[] = INOS_Home_Builder::blank(
			'newsletter',
			array()
		);
		INOS_Home_Builder::save( $mods );
	}

	/**
	 * Two sample comments.
	 *
	 * @param array<string, mixed> $state State.
	 */
	private static function seed_comments( &$state ) {
		foreach ( INOS_Demo_Catalog::comments() as $row ) {
			$post = get_page_by_path( $row['slug'], OBJECT, 'post' );
			if ( ! $post ) {
				continue;
			}
			$pid = (int) $post->ID;
			if ( get_comments( array( 'post_id' => $pid, 'count' => true, 'author_email' => $row['email'] ) ) ) {
				continue;
			}
			$cid = wp_insert_comment(
				array(
					'comment_post_ID'      => $pid,
					'comment_author'       => $row['author'],
					'comment_author_email' => $row['email'],
					'comment_content'      => $row['content'],
					'comment_approved'     => 1,
					'comment_type'         => 'comment',
				)
			);
			if ( $cid ) {
				$state['comments'][] = (int) $cid;
			}
		}
		self::save_state( $state );
	}

	/**
	 * Demo subscribers.
	 *
	 * @param array<string, mixed> $state State.
	 */
	private static function seed_subscribers( &$state ) {
		if ( ! class_exists( 'INOS_Newsletter' ) ) {
			return;
		}
		INOS_Newsletter::maybe_create_table();
		global $wpdb;
		$table = INOS_Newsletter::table();
		foreach ( INOS_Demo_Catalog::subscribers() as $email ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $exists ) {
				$state['subscribers'][] = (int) $exists;
				continue;
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table,
				array(
					'email'      => $email,
					'status'     => 'subscribed',
					'created_at' => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s' )
			);
			if ( $wpdb->insert_id ) {
				$state['subscribers'][] = (int) $wpdb->insert_id;
			}
		}
		self::save_state( $state );
	}

	/**
	 * Fallback non-AJAX import.
	 */
	public static function handle_import_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You cannot import demo content.', 'infy-news-os-core' ) );
		}
		check_admin_referer( 'inos_demo_import' );

		$result = self::import_all();
		$ok     = ! is_wp_error( $result );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'              => 'inos-settings',
					'tab'               => 'demo',
					'inos_demo_imported'=> $ok ? '1' : '0',
					'inos_demo_error'   => $ok ? '' : rawurlencode( $result->get_error_message() ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Remove demo content.
	 */
	public static function handle_remove() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You cannot remove demo content.', 'infy-news-os-core' ) );
		}
		check_admin_referer( 'inos_demo_remove' );

		$state = self::state();

		foreach ( array_merge( $state['live_updates'], $state['live_blogs'], $state['posts'], $state['web_stories'] ) as $pid ) {
			wp_delete_post( (int) $pid, true );
		}
		foreach ( $state['attachments'] as $aid ) {
			wp_delete_attachment( (int) $aid, true );
		}
		foreach ( $state['comments'] as $cid ) {
			wp_delete_comment( (int) $cid, true );
		}
		foreach ( $state['menus'] as $mid ) {
			wp_delete_nav_menu( (int) $mid );
		}
		if ( ! empty( $state['menus_bak'] ) && is_array( $state['menus_bak'] ) ) {
			set_theme_mod( 'nav_menu_locations', $state['menus_bak'] );
		}
		if ( ! empty( $state['settings_bak'] ) && is_array( $state['settings_bak'] ) ) {
			update_option( INOS_CORE_OPTION, $state['settings_bak'] );
		}
		if ( array_key_exists( 'modules_bak', $state ) ) {
			if ( is_array( $state['modules_bak'] ) && ! empty( $state['modules_bak'] ) ) {
				update_option( INOS_Home_Builder::OPTION, $state['modules_bak'], false );
			} else {
				delete_option( INOS_Home_Builder::OPTION );
			}
		}
		if ( $state['front_bak'] ) {
			update_option( 'show_on_front', $state['front_bak'] );
		}

		foreach ( $state['users'] as $uid ) {
			$n  = count_user_posts( (int) $uid, 'post', true );
			$n += count_user_posts( (int) $uid, 'inos_live_blog', true );
			$n += post_type_exists( 'web-story' ) ? count_user_posts( (int) $uid, 'web-story', true ) : 0;
			if ( ! $n ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
				wp_delete_user( (int) $uid );
			}
		}

		if ( $state['subscribers'] && class_exists( 'INOS_Newsletter' ) ) {
			global $wpdb;
			$table = INOS_Newsletter::table();
			foreach ( $state['subscribers'] as $sid ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->delete( $table, array( 'id' => absint( $sid ) ), array( '%d' ) );
			}
		}

		foreach ( $state['terms'] as $tid ) {
			$term = get_term( (int) $tid );
			if ( $term && ! is_wp_error( $term ) && 0 === (int) $term->count ) {
				wp_delete_term( (int) $tid, $term->taxonomy );
			}
		}

		delete_option( self::OPTION );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'            => 'inos-settings',
					'tab'             => 'demo',
					'inos_demo_removed' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
