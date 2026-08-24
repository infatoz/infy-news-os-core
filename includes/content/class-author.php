<?php
/**
 * Author E-E-A-T profile fields.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * User meta for bylines and Person schema.
 */
class INOS_Author {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'show_user_profile', array( __CLASS__, 'fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'fields' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'author_query' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'profile_assets' ) );
		add_filter( 'pre_get_avatar_data', array( __CLASS__, 'avatar_data' ), 10, 2 );
	}

	/**
	 * Include live blogs on author archives.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function author_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() ) {
			return;
		}
		$query->set( 'post_type', function_exists( 'inos_story_post_types' ) ? inos_story_post_types() : array( 'post', 'inos_live_blog' ) );
	}

	/**
	 * Media picker on user profile screens.
	 *
	 * @param string $hook Hook.
	 */
	public static function profile_assets( $hook ) {
		if ( ! in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'inos-admin',
			INOS_CORE_URL . 'admin/js/inos-admin.js',
			array(),
			INOS_CORE_VERSION,
			true
		);
	}

	/**
	 * Prefer a locally uploaded author photo over Gravatar.
	 *
	 * @param array             $args        Avatar args.
	 * @param mixed             $id_or_email User id, email, or object.
	 * @return array
	 */
	public static function avatar_data( $args, $id_or_email ) {
		$user_id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$user_id = absint( $id_or_email );
		} elseif ( $id_or_email instanceof WP_User ) {
			$user_id = (int) $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Post ) {
			$user_id = (int) $id_or_email->post_author;
		} elseif ( $id_or_email instanceof WP_Comment ) {
			$user_id = (int) $id_or_email->user_id;
		} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			$user_id = $user ? (int) $user->ID : 0;
		}

		if ( ! $user_id ) {
			return $args;
		}

		$photo_id = absint( get_user_meta( $user_id, 'inos_avatar_id', true ) );
		if ( ! $photo_id ) {
			return $args;
		}

		$size = isset( $args['size'] ) ? absint( $args['size'] ) : 96;
		$url  = wp_get_attachment_image_url( $photo_id, array( $size, $size ) );
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( $photo_id, 'medium' );
		}
		if ( $url ) {
			$args['url']          = $url;
			$args['found_avatar'] = true;
		}

		return $args;
	}

	/**
	 * Profile fields.
	 *
	 * @param WP_User $user User.
	 */
	public static function fields( $user ) {
		$job         = get_user_meta( $user->ID, 'inos_job_title', true );
		$expertise   = get_user_meta( $user->ID, 'inos_expertise', true );
		$sameas      = get_user_meta( $user->ID, 'inos_sameas', true );
		$location    = get_user_meta( $user->ID, 'inos_location', true );
		$credentials = get_user_meta( $user->ID, 'inos_credentials', true );
		$awards      = get_user_meta( $user->ID, 'inos_awards', true );
		$languages   = get_user_meta( $user->ID, 'inos_languages', true );
		$twitter     = get_user_meta( $user->ID, 'inos_twitter', true );
		$linkedin    = get_user_meta( $user->ID, 'inos_linkedin', true );
		$short_bio   = get_user_meta( $user->ID, 'inos_short_bio', true );
		$started     = get_user_meta( $user->ID, 'inos_started_year', true );
		$show_email  = get_user_meta( $user->ID, 'inos_show_email', true );
		$avatar_id   = absint( get_user_meta( $user->ID, 'inos_avatar_id', true ) );
		$seo_title   = get_user_meta( $user->ID, 'inos_seo_title', true );
		$seo_desc    = get_user_meta( $user->ID, 'inos_seo_description', true );
		$robots      = get_user_meta( $user->ID, 'inos_robots', true );
		?>
		<h2><?php esc_html_e( 'Infy News OS — author E-E-A-T', 'infy-news-os-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'These details appear on the byline, the article author box, the author archive, and Person JSON-LD.', 'infy-news-os-core' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="inos_avatar_id"><?php esc_html_e( 'Profile photo', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="hidden" name="inos_avatar_id" id="inos_avatar_id" value="<?php echo esc_attr( (string) $avatar_id ); ?>" />
					<p id="inos-avatar-preview" style="margin:0 0 0.6rem;">
						<?php
						if ( $avatar_id ) {
							echo wp_get_attachment_image( $avatar_id, array( 96, 96 ), false, array( 'style' => 'width:96px;height:96px;object-fit:cover;border-radius:50%;' ) );
						}
						?>
					</p>
					<button type="button" class="button" id="inos-avatar-select"><?php esc_html_e( 'Select photo', 'infy-news-os-core' ); ?></button>
					<button type="button" class="button-link" id="inos-avatar-remove"<?php echo $avatar_id ? '' : ' style="display:none"'; ?>><?php esc_html_e( 'Remove', 'infy-news-os-core' ); ?></button>
					<p class="description"><?php esc_html_e( 'Square crop, at least 400×400. Stored on this site (not Gravatar). Used on the author archive, byline, and author box.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_job_title"><?php esc_html_e( 'Job title', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_job_title" id="inos_job_title" value="<?php echo esc_attr( $job ); ?>" />
					<p class="description"><?php esc_html_e( 'Example: Senior correspondent, Science editor.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_short_bio"><?php esc_html_e( 'Short bio', 'infy-news-os-core' ); ?></label></th>
				<td>
					<textarea name="inos_short_bio" id="inos_short_bio" class="large-text" rows="2"><?php echo esc_textarea( $short_bio ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One or two sentences for the article author box. The WordPress Biographical Info field is the full archive bio.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_location"><?php esc_html_e( 'Based in', 'infy-news-os-core' ); ?></label></th>
				<td><input type="text" class="regular-text" name="inos_location" id="inos_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'Bengaluru', 'infy-news-os-core' ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="inos_expertise"><?php esc_html_e( 'Expertise / beats', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_expertise" id="inos_expertise" value="<?php echo esc_attr( $expertise ); ?>" />
					<p class="description"><?php esc_html_e( 'Comma-separated topics this reporter covers (knowsAbout).', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_credentials"><?php esc_html_e( 'Credentials', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_credentials" id="inos_credentials" value="<?php echo esc_attr( $credentials ); ?>" />
					<p class="description"><?php esc_html_e( 'Degrees, fellowships, or other qualifications shown on the archive.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_awards"><?php esc_html_e( 'Awards', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_awards" id="inos_awards" value="<?php echo esc_attr( $awards ); ?>" />
					<p class="description"><?php esc_html_e( 'Comma-separated awards or honours.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_languages"><?php esc_html_e( 'Languages', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_languages" id="inos_languages" value="<?php echo esc_attr( $languages ); ?>" />
					<p class="description"><?php esc_html_e( 'Comma-separated languages the reporter works in.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_started_year"><?php esc_html_e( 'Reporting since', 'infy-news-os-core' ); ?></label></th>
				<td><input type="number" class="small-text" min="1950" max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" name="inos_started_year" id="inos_started_year" value="<?php echo esc_attr( $started ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="inos_twitter"><?php esc_html_e( 'X / Twitter handle', 'infy-news-os-core' ); ?></label></th>
				<td><input type="text" class="regular-text" name="inos_twitter" id="inos_twitter" value="<?php echo esc_attr( $twitter ); ?>" placeholder="@handle" /></td>
			</tr>
			<tr>
				<th><label for="inos_linkedin"><?php esc_html_e( 'LinkedIn URL', 'infy-news-os-core' ); ?></label></th>
				<td><input type="url" class="regular-text" name="inos_linkedin" id="inos_linkedin" value="<?php echo esc_attr( $linkedin ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="inos_sameas"><?php esc_html_e( 'sameAs profile URLs', 'infy-news-os-core' ); ?></label></th>
				<td>
					<textarea name="inos_sameas" id="inos_sameas" class="large-text" rows="4"><?php echo esc_textarea( $sameas ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One URL per line: Wikipedia, Mastodon, Google Scholar, staff page, etc. LinkedIn and X are added automatically when those fields are set. Website is the WordPress “Website” field.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Public email', 'infy-news-os-core' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="inos_show_email" value="1" <?php checked( $show_email, '1' ); ?> />
						<?php esc_html_e( 'Show this account’s email on the author archive and author box (tips / corrections).', 'infy-news-os-core' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="inos_seo_title"><?php esc_html_e( 'SEO title', 'infy-news-os-core' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" name="inos_seo_title" id="inos_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" />
					<p class="description"><?php esc_html_e( 'Overrides the author archive document title.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_seo_description"><?php esc_html_e( 'SEO / meta description', 'infy-news-os-core' ); ?></label></th>
				<td>
					<textarea name="inos_seo_description" id="inos_seo_description" class="large-text" rows="2"><?php echo esc_textarea( $seo_desc ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Leave empty to use the biographical info.', 'infy-news-os-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="inos_robots"><?php esc_html_e( 'Archive robots', 'infy-news-os-core' ); ?></label></th>
				<td>
					<select name="inos_robots" id="inos_robots">
						<option value="" <?php selected( $robots, '' ); ?>><?php esc_html_e( 'Default (index, follow)', 'infy-news-os-core' ); ?></option>
						<option value="noindex,follow" <?php selected( $robots, 'noindex,follow' ); ?>><?php esc_html_e( 'noindex, follow', 'infy-news-os-core' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save profile fields.
	 *
	 * @param int $user_id User ID.
	 */
	public static function save( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		check_admin_referer( 'update-user_' . $user_id );

		$text = array(
			'inos_job_title',
			'inos_expertise',
			'inos_location',
			'inos_credentials',
			'inos_awards',
			'inos_languages',
			'inos_twitter',
			'inos_linkedin',
			'inos_started_year',
		);
		foreach ( $text as $key ) {
			$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			if ( 'inos_twitter' === $key ) {
				$value = ltrim( $value, '@' );
				$value = preg_replace( '#^https?://(www\.)?(twitter|x)\.com/#i', '', $value );
				$value = sanitize_user( $value, true );
			}
			if ( 'inos_linkedin' === $key ) {
				$value = esc_url_raw( $value );
			}
			if ( 'inos_started_year' === $key ) {
				$year = absint( $value );
				$value = ( $year >= 1950 && $year <= (int) gmdate( 'Y' ) ) ? (string) $year : '';
			}
			update_user_meta( $user_id, $key, $value );
		}

		$avatar_id = isset( $_POST['inos_avatar_id'] ) ? absint( wp_unslash( $_POST['inos_avatar_id'] ) ) : 0;
		if ( $avatar_id && ! wp_attachment_is_image( $avatar_id ) ) {
			$avatar_id = 0;
		}
		update_user_meta( $user_id, 'inos_avatar_id', $avatar_id );

		$sameas = isset( $_POST['inos_sameas'] ) ? sanitize_textarea_field( wp_unslash( $_POST['inos_sameas'] ) ) : '';
		update_user_meta( $user_id, 'inos_sameas', $sameas );

		$short = isset( $_POST['inos_short_bio'] ) ? sanitize_textarea_field( wp_unslash( $_POST['inos_short_bio'] ) ) : '';
		update_user_meta( $user_id, 'inos_short_bio', $short );

		$seo_title = isset( $_POST['inos_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['inos_seo_title'] ) ) : '';
		update_user_meta( $user_id, 'inos_seo_title', $seo_title );
		$seo_desc = isset( $_POST['inos_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['inos_seo_description'] ) ) : '';
		update_user_meta( $user_id, 'inos_seo_description', $seo_desc );
		$robots = isset( $_POST['inos_robots'] ) ? sanitize_text_field( wp_unslash( $_POST['inos_robots'] ) ) : '';
		if ( ! in_array( $robots, array( '', 'noindex,follow' ), true ) ) {
			$robots = '';
		}
		update_user_meta( $user_id, 'inos_robots', $robots );

		update_user_meta( $user_id, 'inos_show_email', empty( $_POST['inos_show_email'] ) ? '0' : '1' );
	}

	/**
	 * Structured profile for theme + schema.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public static function profile( $user_id ) {
		$user_id = absint( $user_id );
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}

		$bio       = (string) $user->description;
		$short     = (string) get_user_meta( $user_id, 'inos_short_bio', true );
		$expertise = self::list_from_csv( (string) get_user_meta( $user_id, 'inos_expertise', true ) );
		$awards    = self::list_from_csv( (string) get_user_meta( $user_id, 'inos_awards', true ) );
		$languages = self::list_from_csv( (string) get_user_meta( $user_id, 'inos_languages', true ) );
		$twitter   = ltrim( (string) get_user_meta( $user_id, 'inos_twitter', true ), '@' );
		$linkedin  = (string) get_user_meta( $user_id, 'inos_linkedin', true );
		$website   = (string) $user->user_url;
		$sameas    = self::sameas_urls( $user_id, $twitter, $linkedin, $website );
		$show_mail = '1' === (string) get_user_meta( $user_id, 'inos_show_email', true );
		$started   = absint( get_user_meta( $user_id, 'inos_started_year', true ) );
		$avatar_id = absint( get_user_meta( $user_id, 'inos_avatar_id', true ) );
		$avatar    = '';
		if ( $avatar_id ) {
			$avatar = wp_get_attachment_image_url( $avatar_id, 'medium' );
		}
		if ( ! $avatar ) {
			$avatar = get_avatar_url( $user_id, array( 'size' => 256 ) );
		}

		if ( ! $short && $bio ) {
			$short = wp_trim_words( wp_strip_all_tags( $bio ), 28, '…' );
		}

		return array(
			'id'           => $user_id,
			'name'         => $user->display_name,
			'given_name'   => $user->first_name,
			'family_name'  => $user->last_name,
			'url'          => get_author_posts_url( $user_id ),
			'job_title'    => (string) get_user_meta( $user_id, 'inos_job_title', true ),
			'location'     => (string) get_user_meta( $user_id, 'inos_location', true ),
			'credentials'  => (string) get_user_meta( $user_id, 'inos_credentials', true ),
			'expertise'    => $expertise,
			'awards'       => $awards,
			'languages'    => $languages,
			'bio'          => $bio,
			'short_bio'    => $short,
			'twitter'      => $twitter,
			'linkedin'     => $linkedin,
			'website'      => $website,
			'sameas'       => $sameas,
			'social'       => self::social_links( $sameas, $twitter, $show_mail ? $user->user_email : '' ),
			'email'        => $show_mail ? $user->user_email : '',
			'started_year' => $started,
			'story_count'  => (int) count_user_posts( $user_id, array( 'post', 'inos_live_blog' ), true ),
			'works_for'    => (string) inos_get_option( 'publication_name', get_bloginfo( 'name' ) ),
			'avatar_id'    => $avatar_id,
			'avatar_url'   => $avatar ? $avatar : '',
		);
	}

	/**
	 * Person schema node.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public static function person_schema( $user_id ) {
		$profile = self::profile( $user_id );
		if ( empty( $profile ) ) {
			return array();
		}

		$person = array(
			'@type'    => 'Person',
			'@id'      => $profile['url'] . '#person',
			'name'     => $profile['name'],
			'url'      => $profile['url'],
			'worksFor' => array( '@id' => home_url( '/#organization' ) ),
		);

		if ( $profile['given_name'] ) {
			$person['givenName'] = $profile['given_name'];
		}
		if ( $profile['family_name'] ) {
			$person['familyName'] = $profile['family_name'];
		}
		if ( $profile['job_title'] ) {
			$person['jobTitle'] = $profile['job_title'];
		}
		if ( $profile['bio'] ) {
			$person['description'] = wp_strip_all_tags( $profile['bio'] );
		}
		if ( $profile['avatar_url'] ) {
			$person['image'] = $profile['avatar_url'];
		}
		if ( $profile['expertise'] ) {
			$person['knowsAbout'] = $profile['expertise'];
		}
		if ( $profile['sameas'] ) {
			$person['sameAs'] = $profile['sameas'];
		}
		if ( $profile['email'] ) {
			$person['email'] = $profile['email'];
		}
		if ( $profile['location'] ) {
			$person['homeLocation'] = array(
				'@type' => 'Place',
				'name'  => $profile['location'],
			);
		}
		if ( $profile['awards'] ) {
			$person['award'] = $profile['awards'];
		}
		if ( $profile['languages'] ) {
			$person['knowsLanguage'] = $profile['languages'];
		}
		if ( $profile['credentials'] ) {
			$person['hasCredential'] = $profile['credentials'];
		}

		return $person;
	}

	/**
	 * ProfilePage schema for the author archive.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public static function profile_page_schema( $user_id ) {
		$profile = self::profile( $user_id );
		if ( empty( $profile ) ) {
			return array();
		}

		$page = array(
			'@type'      => 'ProfilePage',
			'@id'        => $profile['url'] . '#profile',
			'url'        => $profile['url'],
			'name'       => $profile['name'],
			'mainEntity' => array( '@id' => $profile['url'] . '#person' ),
			'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
			'publisher'  => array( '@id' => home_url( '/#organization' ) ),
		);
		if ( $profile['bio'] ) {
			$page['description'] = wp_strip_all_tags( $profile['bio'] );
		}
		return $page;
	}

	/**
	 * sameAs list including dedicated social fields.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $twitter  Handle.
	 * @param string $linkedin URL.
	 * @param string $website  URL.
	 * @return string[]
	 */
	private static function sameas_urls( $user_id, $twitter, $linkedin, $website ) {
		$urls = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) get_user_meta( $user_id, 'inos_sameas', true ) ) as $line ) {
			$url = esc_url_raw( trim( $line ) );
			if ( $url ) {
				$urls[] = $url;
			}
		}
		if ( $twitter ) {
			$urls[] = 'https://x.com/' . rawurlencode( $twitter );
		}
		if ( $linkedin ) {
			$urls[] = esc_url_raw( $linkedin );
		}
		if ( $website ) {
			$urls[] = esc_url_raw( $website );
		}
		return array_values( array_unique( array_filter( $urls ) ) );
	}

	/**
	 * Front-end social links with network slugs.
	 *
	 * @param string[] $urls     sameAs URLs.
	 * @param string   $twitter  Handle.
	 * @param string   $email    Public email.
	 * @return array<int, array<string, string>>
	 */
	private static function social_links( $urls, $twitter, $email ) {
		$out  = array();
		$seen = array();

		if ( $twitter ) {
			$out[] = array(
				'network' => 'twitter',
				'label'   => __( 'X', 'infy-news-os-core' ),
				'url'     => 'https://x.com/' . rawurlencode( $twitter ),
			);
			$seen[] = 'twitter';
		}
		if ( $email ) {
			$out[] = array(
				'network' => 'email',
				'label'   => __( 'Email', 'infy-news-os-core' ),
				'url'     => 'mailto:' . $email,
			);
		}

		foreach ( $urls as $url ) {
			$host = (string) wp_parse_url( $url, PHP_URL_HOST );
			$host = strtolower( preg_replace( '/^www\./', '', $host ) );
			$pair = self::network_from_host( $host );
			if ( in_array( $pair['network'], $seen, true ) ) {
				continue;
			}
			$seen[] = $pair['network'] . ':' . $url;
			$out[]  = array(
				'network' => $pair['network'],
				'label'   => $pair['label'],
				'url'     => $url,
			);
		}

		return $out;
	}

	/**
	 * Map a host to a network slug.
	 *
	 * @param string $host Host.
	 * @return array{network:string,label:string}
	 */
	private static function network_from_host( $host ) {
		$map = array(
			'x.com'              => array( 'twitter', __( 'X', 'infy-news-os-core' ) ),
			'twitter.com'        => array( 'twitter', __( 'X', 'infy-news-os-core' ) ),
			'linkedin.com'       => array( 'linkedin', __( 'LinkedIn', 'infy-news-os-core' ) ),
			'facebook.com'       => array( 'facebook', __( 'Facebook', 'infy-news-os-core' ) ),
			'instagram.com'      => array( 'instagram', __( 'Instagram', 'infy-news-os-core' ) ),
			'youtube.com'        => array( 'youtube', __( 'YouTube', 'infy-news-os-core' ) ),
			'wikipedia.org'      => array( 'wikipedia', __( 'Wikipedia', 'infy-news-os-core' ) ),
			'en.wikipedia.org'   => array( 'wikipedia', __( 'Wikipedia', 'infy-news-os-core' ) ),
			'scholar.google.com' => array( 'scholar', __( 'Google Scholar', 'infy-news-os-core' ) ),
			'github.com'         => array( 'github', __( 'GitHub', 'infy-news-os-core' ) ),
		);
		if ( isset( $map[ $host ] ) ) {
			return array(
				'network' => $map[ $host ][0],
				'label'   => $map[ $host ][1],
			);
		}
		return array(
			'network' => 'website',
			'label'   => __( 'Website', 'infy-news-os-core' ),
		);
	}

	/**
	 * Split a comma list.
	 *
	 * @param string $raw Raw.
	 * @return string[]
	 */
	private static function list_from_csv( $raw ) {
		$parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		return array_values( $parts );
	}
}
