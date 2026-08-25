<?php
/**
 * Overridable front-end chrome strings for multilingual news sites.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Label catalog stored in option `inos_labels`.
 *
 * Empty values fall back to translated English defaults so WordPress
 * language packs still work when a publisher does not override a string.
 */
class INOS_Labels {

	const OPTION = 'inos_labels';

	/**
	 * RTL language codes (ISO 639-1).
	 *
	 * @var string[]
	 */
	private static $rtl_langs = array( 'ar', 'he', 'fa', 'ur', 'ps', 'sd', 'yi', 'dv', 'ku' );

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'language_attributes', array( __CLASS__, 'language_attributes' ) );
	}

	/**
	 * Set html lang/dir from publication language and text-direction setting.
	 *
	 * @param string $output language_attributes() output.
	 * @return string
	 */
	public static function language_attributes( $output ) {
		if ( is_admin() ) {
			return $output;
		}

		$lang = sanitize_text_field( (string) inos_get_option( 'publication_language', '' ) );
		$lang = preg_replace( '/[^a-zA-Z\-]/', '', $lang );
		$dir  = (string) inos_get_option( 'text_direction', 'auto' );
		if ( ! in_array( $dir, array( 'auto', 'ltr', 'rtl' ), true ) ) {
			$dir = 'auto';
		}

		if ( $lang ) {
			if ( preg_match( '/\blang="/', $output ) ) {
				$output = preg_replace( '/\blang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output );
			} else {
				$output .= ' lang="' . esc_attr( $lang ) . '"';
			}
		}

		$want_rtl = false;
		if ( 'rtl' === $dir ) {
			$want_rtl = true;
		} elseif ( 'ltr' === $dir ) {
			$want_rtl = false;
		} else {
			$short = strtolower( substr( $lang, 0, 2 ) );
			$want_rtl = is_rtl() || ( $short && in_array( $short, self::$rtl_langs, true ) );
		}

		$dir_attr = $want_rtl ? 'rtl' : 'ltr';
		if ( preg_match( '/\bdir="/', $output ) ) {
			$output = preg_replace( '/\bdir="[^"]*"/', 'dir="' . $dir_attr . '"', $output );
		} else {
			$output .= ' dir="' . $dir_attr . '"';
		}

		return $output;
	}

	/**
	 * Grouped catalog: key => default string (already run through __()).
	 *
	 * @return array<string, array{title:string, keys: array<string, string>}>
	 */
	public static function groups() {
		return array(
			'header'     => array(
				'title' => __( 'Header & navigation', 'infy-news-os-core' ),
				'keys'  => array(
					'skip_content'        => __( 'Skip to content', 'infy-news-os-core' ),
					'menu'                => __( 'Menu', 'infy-news-os-core' ),
					'close'               => __( 'Close', 'infy-news-os-core' ),
					'search'              => __( 'Search', 'infy-news-os-core' ),
					'search_placeholder'  => __( 'Search stories', 'infy-news-os-core' ),
					'subscribe'           => __( 'Subscribe', 'infy-news-os-core' ),
					'site_nav'            => __( 'Site', 'infy-news-os-core' ),
					'sections'            => __( 'Sections', 'infy-news-os-core' ),
					'breaking'            => __( 'Breaking', 'infy-news-os-core' ),
					'latest'              => __( 'Latest', 'infy-news-os-core' ),
					'more'                => __( 'More', 'infy-news-os-core' ),
					'all_stories'         => __( 'All stories', 'infy-news-os-core' ),
					'follow_us'           => __( 'Follow', 'infy-news-os-core' ),
				),
			),
			'article'    => array(
				'title' => __( 'Articles & live coverage', 'infy-news-os-core' ),
				'keys'  => array(
					'paid_content'    => __( 'Paid content', 'infy-news-os-core' ),
					'exclusive'       => __( 'Exclusive', 'infy-news-os-core' ),
					'live'            => __( 'Live', 'infy-news-os-core' ),
					'live_now'        => __( 'Live now', 'infy-news-os-core' ),
					'live_updates'    => __( 'Live updates', 'infy-news-os-core' ),
					'n_updates'       => __( '%s updates', 'infy-news-os-core' ),
					'show_new_updates'    => __( 'Show new updates', 'infy-news-os-core' ),
					'waiting_for_updates' => __( 'Updates appear here as they happen.', 'infy-news-os-core' ),
					'share_this_update'   => __( 'Share this update', 'infy-news-os-core' ),
					'new_update'      => __( 'New', 'infy-news-os-core' ),
					'background'      => __( 'Background', 'infy-news-os-core' ),
					'coverage_ended'  => __( 'Coverage ended', 'infy-news-os-core' ),
					'source'          => __( 'Source: %s', 'infy-news-os-core' ),
					'updated'         => __( 'Updated', 'infy-news-os-core' ),
					'related'         => __( 'Related', 'infy-news-os-core' ),
					'related_stories' => __( 'Related stories', 'infy-news-os-core' ),
					'more_in'         => __( 'More in %s', 'infy-news-os-core' ),
					'see_all_in'      => __( 'See all in %s', 'infy-news-os-core' ),
					'about_author'    => __( 'About the author', 'infy-news-os-core' ),
					'more_from'       => __( 'More from %s', 'infy-news-os-core' ),
					'min_read'        => __( '%s min read', 'infy-news-os-core' ),
					'view_one'        => __( '%s view', 'infy-news-os-core' ),
					'view_many'       => __( '%s views', 'infy-news-os-core' ),
					'home'            => __( 'Home', 'infy-news-os-core' ),
					'breadcrumb'      => __( 'Breadcrumb', 'infy-news-os-core' ),
					'previous_story'  => __( 'Previous story', 'infy-news-os-core' ),
					'next_story'      => __( 'Next story', 'infy-news-os-core' ),
					'print_story'     => __( 'Print', 'infy-news-os-core' ),
					'text_size'       => __( 'Text size', 'infy-news-os-core' ),
					'text_smaller'    => __( 'Smaller text', 'infy-news-os-core' ),
					'text_default'    => __( 'Default text size', 'infy-news-os-core' ),
					'text_larger'     => __( 'Larger text', 'infy-news-os-core' ),
					'dark_mode'       => __( 'Dark mode', 'infy-news-os-core' ),
					'light_mode'      => __( 'Light mode', 'infy-news-os-core' ),
					'also_read_inline'=> __( 'Also read', 'infy-news-os-core' ),
					'reading_progress'=> __( 'Reading progress', 'infy-news-os-core' ),
				),
			),
			'share'      => array(
				'title' => __( 'Share', 'infy-news-os-core' ),
				'keys'  => array(
					'share'            => __( 'Share', 'infy-news-os-core' ),
					'share_via_device' => __( 'Share via device', 'infy-news-os-core' ),
					'share_on'         => __( 'Share on %s', 'infy-news-os-core' ),
					'share_x'          => __( 'X', 'infy-news-os-core' ),
					'share_facebook'   => __( 'Facebook', 'infy-news-os-core' ),
					'share_linkedin'   => __( 'LinkedIn', 'infy-news-os-core' ),
					'share_whatsapp'   => __( 'WhatsApp', 'infy-news-os-core' ),
					'share_telegram'   => __( 'Telegram', 'infy-news-os-core' ),
					'share_email'      => __( 'Email', 'infy-news-os-core' ),
					'copy_link'        => __( 'Copy link', 'infy-news-os-core' ),
					'link_copied'      => __( 'Link copied', 'infy-news-os-core' ),
					'copy_failed'      => __( 'Could not copy link', 'infy-news-os-core' ),
					'shared'           => __( 'Shared', 'infy-news-os-core' ),
					'preferred_source' => __( 'Add as a Google preferred source', 'infy-news-os-core' ),
				),
			),
			'comments'   => array(
				'title' => __( 'Comments', 'infy-news-os-core' ),
				'keys'  => array(
					'conversation'         => __( 'Conversation', 'infy-news-os-core' ),
					'comments'             => __( 'Comments', 'infy-news-os-core' ),
					'comment_one'          => __( '%s comment', 'infy-news-os-core' ),
					'comment_many'         => __( '%s comments', 'infy-news-os-core' ),
					'write_comment'        => __( 'Write a comment', 'infy-news-os-core' ),
					'join_discussion'      => __( 'Join the discussion', 'infy-news-os-core' ),
					'reply_to'             => __( 'Reply to %s', 'infy-news-os-core' ),
					'reply'                => __( 'Reply', 'infy-news-os-core' ),
					'cancel_reply'         => __( 'Cancel reply', 'infy-news-os-core' ),
					'post_comment'         => __( 'Post comment', 'infy-news-os-core' ),
					'comment_name'         => __( 'Name', 'infy-news-os-core' ),
					'comment_email'        => __( 'Email', 'infy-news-os-core' ),
					'comment_website'      => __( 'Website', 'infy-news-os-core' ),
					'comment'              => __( 'Comment', 'infy-news-os-core' ),
					'comment_placeholder'  => __( 'Share your perspective…', 'infy-news-os-core' ),
					'comment_note'         => __( 'Your email stays private. Comments may be edited for clarity or held for review.', 'infy-news-os-core' ),
					'comment_cookies'      => __( 'Save my name and email in this browser for the next time I comment.', 'infy-news-os-core' ),
					'commenting_as'        => __( 'Commenting as %s.', 'infy-news-os-core' ),
					'edit_profile'         => __( 'Edit profile', 'infy-news-os-core' ),
					'log_out'              => __( 'Log out', 'infy-news-os-core' ),
					'sign_in'              => __( 'Sign in', 'infy-news-os-core' ),
					'must_log_in'          => __( '%s to join the discussion.', 'infy-news-os-core' ),
					'be_first'             => __( 'Be the first to comment', 'infy-news-os-core' ),
					'be_first_text'        => __( 'Add context, a correction, or a question — keep it civil and on the story.', 'infy-news-os-core' ),
					'comments_closed'      => __( 'Comments are closed on this story.', 'infy-news-os-core' ),
					'older_comments'       => __( 'Older comments', 'infy-news-os-core' ),
					'newer_comments'       => __( 'Newer comments', 'infy-news-os-core' ),
					'comment_pending'      => __( 'Your comment is awaiting moderation. It will appear once an editor approves it.', 'infy-news-os-core' ),
					'comment_author'       => __( 'Author', 'infy-news-os-core' ),
					'comment_staff'        => __( 'Staff', 'infy-news-os-core' ),
					'comment_edit'         => __( 'Edit', 'infy-news-os-core' ),
					'comment_time'         => __( '%1$s at %2$s', 'infy-news-os-core' ),
					'pingback'             => __( 'Pingback: %s', 'infy-news-os-core' ),
					'avatar_for'           => __( 'Avatar for %s', 'infy-news-os-core' ),
				),
			),
			'archives'   => array(
				'title' => __( 'Archives & empty states', 'infy-news-os-core' ),
				'keys'  => array(
					'stories'              => __( 'Stories', 'infy-news-os-core' ),
					'latest_stories'       => __( 'Latest stories', 'infy-news-os-core' ),
					'nothing_found'        => __( 'Nothing found.', 'infy-news-os-core' ),
					'no_stories'           => __( 'No stories yet.', 'infy-news-os-core' ),
					'no_matching'          => __( 'No matching stories.', 'infy-news-os-core' ),
					'no_section_stories'   => __( 'No stories in this section yet.', 'infy-news-os-core' ),
					'search_heading'       => __( 'Search: %s', 'infy-news-os-core' ),
					'stories_by'           => __( 'Stories by %s', 'infy-news-os-core' ),
					'no_author_stories'    => __( 'No stories from this reporter yet.', 'infy-news-os-core' ),
					'page_not_found'       => __( 'Page not found', 'infy-news-os-core' ),
					'page_not_found_text'  => __( 'The story you are looking for is missing or has moved.', 'infy-news-os-core' ),
					'story_one'            => __( '%s story', 'infy-news-os-core' ),
					'story_many'           => __( '%s stories', 'infy-news-os-core' ),
					'authors'              => __( 'Authors', 'infy-news-os-core' ),
					'page_n'               => __( 'Page %s', 'infy-news-os-core' ),
					'latest_in'            => __( 'Latest %1$s stories from %2$s.', 'infy-news-os-core' ),
					'stories_tagged'       => __( 'Stories tagged %1$s, from %2$s.', 'infy-news-os-core' ),
					'type_archive_desc'    => __( 'All %1$s coverage from %2$s.', 'infy-news-os-core' ),
					'author_archive_desc'  => __( 'Stories by %1$s at %2$s.', 'infy-news-os-core' ),
					'author_archive_desc_job' => __( 'Stories by %1$s, %2$s at %3$s.', 'infy-news-os-core' ),
					'search_archive_desc'  => __( 'Search results for “%s”.', 'infy-news-os-core' ),
					'date_archive_desc'    => __( 'Stories published %s.', 'infy-news-os-core' ),
					'blog_archive_desc'    => __( 'The latest stories from %s.', 'infy-news-os-core' ),
					'home_meta_desc'       => __( 'The latest news from %s.', 'infy-news-os-core' ),
					'reporting_since'      => __( 'Reporting since %s', 'infy-news-os-core' ),
					'languages_list'       => __( 'Languages: %s', 'infy-news-os-core' ),
					'awards'               => __( 'Awards', 'infy-news-os-core' ),
					'author'               => __( 'Author', 'infy-news-os-core' ),
				),
			),
			'pages'      => array(
				'title' => __( 'Static pages & footer', 'infy-news-os-core' ),
				'keys'  => array(
					'about_kicker'     => __( 'About the publication', 'infy-news-os-core' ),
					'standards_kicker' => __( 'Standards', 'infy-news-os-core' ),
					'accountability'   => __( 'Accountability', 'infy-news-os-core' ),
					'copyright'        => __( '© %1$s %2$s', 'infy-news-os-core' ),
					'theme_credit'     => __( 'Theme by Infatoz Technologies LLP', 'infy-news-os-core' ),
					'back_to_top'      => __( 'Back to top', 'infy-news-os-core' ),
					'pagination_prev'  => __( 'Previous', 'infy-news-os-core' ),
					'pagination_next'  => __( 'Next', 'infy-news-os-core' ),
					'load_more'        => __( 'Load more stories', 'infy-news-os-core' ),
					'loading'          => __( 'Loading…', 'infy-news-os-core' ),
					'load_more_end'    => __( 'No more stories', 'infy-news-os-core' ),
					'load_more_error'  => __( 'Couldn’t load more stories. Try again.', 'infy-news-os-core' ),
					'time_ago'         => __( '%s ago', 'infy-news-os-core' ),
				),
			),
			'newsletter' => array(
				'title' => __( 'Newsletter messages', 'infy-news-os-core' ),
				'keys'  => array(
					'nl_already'       => __( 'You are already subscribed.', 'infy-news-os-core' ),
					'nl_invalid'       => __( 'Please enter a valid email address.', 'infy-news-os-core' ),
					'nl_email'         => __( 'Email address', 'infy-news-os-core' ),
					'nl_placeholder'   => __( 'you@example.com', 'infy-news-os-core' ),
				),
			),
		);
	}

	/**
	 * Placeholder hints for sprintf strings.
	 *
	 * @return array<string, string>
	 */
	public static function hints() {
		return array(
			'source'         => __( 'Use %s for the wire or source name.', 'infy-news-os-core' ),
			'more_in'        => __( 'Use %s for the section name.', 'infy-news-os-core' ),
			'see_all_in'     => __( 'Use %s for the section name.', 'infy-news-os-core' ),
			'more_from'      => __( 'Use %s for the author name.', 'infy-news-os-core' ),
			'share_on'       => __( 'Use %s for the network name.', 'infy-news-os-core' ),
			'comment_one'    => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'comment_many'   => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'reply_to'       => __( 'Use %s for the commenter name.', 'infy-news-os-core' ),
			'commenting_as'  => __( 'Use %s for the signed-in name.', 'infy-news-os-core' ),
			'must_log_in'    => __( 'Use %s for the Sign in link.', 'infy-news-os-core' ),
			'comment_time'   => __( 'Use %1$s for the date and %2$s for the time.', 'infy-news-os-core' ),
			'pingback'       => __( 'Use %s for the pingback URL.', 'infy-news-os-core' ),
			'avatar_for'     => __( 'Use %s for the commenter name.', 'infy-news-os-core' ),
			'min_read'       => __( 'Use %s for the minute count.', 'infy-news-os-core' ),
			'view_one'       => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'view_many'      => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'search_heading' => __( 'Use %s for the search query.', 'infy-news-os-core' ),
			'stories_by'     => __( 'Use %s for the author name.', 'infy-news-os-core' ),
			'story_one'      => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'story_many'     => __( 'Use %s for the number.', 'infy-news-os-core' ),
			'page_n'         => __( 'Use %s for the page number.', 'infy-news-os-core' ),
			'latest_in'      => __( 'Use %1$s for the section and %2$s for the publication.', 'infy-news-os-core' ),
			'stories_tagged' => __( 'Use %1$s for the tag and %2$s for the publication.', 'infy-news-os-core' ),
			'type_archive_desc' => __( 'Use %1$s for the type and %2$s for the publication.', 'infy-news-os-core' ),
			'author_archive_desc' => __( 'Use %1$s for the author and %2$s for the publication.', 'infy-news-os-core' ),
			'author_archive_desc_job' => __( 'Use %1$s for the author, %2$s for the job title, and %3$s for the publication.', 'infy-news-os-core' ),
			'search_archive_desc' => __( 'Use %s for the search query.', 'infy-news-os-core' ),
			'date_archive_desc' => __( 'Use %s for the date label.', 'infy-news-os-core' ),
			'blog_archive_desc' => __( 'Use %s for the publication name.', 'infy-news-os-core' ),
			'home_meta_desc'    => __( 'Use %s for the publication name.', 'infy-news-os-core' ),
			'reporting_since'=> __( 'Use %s for the year.', 'infy-news-os-core' ),
			'languages_list' => __( 'Use %s for the language list.', 'infy-news-os-core' ),
			'copyright'      => __( 'Use %1$s for the year and %2$s for the site name.', 'infy-news-os-core' ),
			'time_ago'       => __( 'Use %s for the relative time, for example 3 hours.', 'infy-news-os-core' ),
		);
	}

	/**
	 * Flat defaults keyed by string id.
	 *
	 * @return array<string, string>
	 */
	public static function defaults() {
		$out = array();
		foreach ( self::groups() as $group ) {
			foreach ( $group['keys'] as $key => $default ) {
				$out[ $key ] = $default;
			}
		}
		return $out;
	}

	/**
	 * Known keys.
	 *
	 * @return string[]
	 */
	public static function keys() {
		return array_keys( self::defaults() );
	}

	/**
	 * Stored overrides (non-empty only).
	 *
	 * @return array<string, string>
	 */
	public static function stored() {
		$stored = get_option( self::OPTION, array() );
		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Resolve a chrome string.
	 *
	 * @param string               $key  Catalog key.
	 * @param array<int, string>   $args vsprintf replacements.
	 * @return string
	 */
	public static function get( $key, $args = array() ) {
		$defaults = self::defaults();
		$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $key;
		$stored   = self::stored();
		$custom   = isset( $stored[ $key ] ) ? trim( (string) $stored[ $key ] ) : '';
		$text     = '' !== $custom ? $custom : $default;

		/**
		 * Filter a resolved front-end label.
		 *
		 * @param string $text Resolved text.
		 * @param string $key  Catalog key.
		 */
		$text = (string) apply_filters( 'inos_label', $text, $key );

		if ( ! empty( $args ) ) {
			$text = vsprintf( $text, array_values( (array) $args ) );
		}

		return $text;
	}

	/**
	 * Plural helper.
	 *
	 * @param string $one    Singular key.
	 * @param string $many   Plural key.
	 * @param int    $number Count.
	 * @return string
	 */
	public static function get_n( $one, $many, $number ) {
		$n = (int) $number;
		return self::get( 1 === $n ? $one : $many, array( number_format_i18n( $n ) ) );
	}

	/**
	 * Persist posted overrides.
	 *
	 * @param array<string, mixed> $posted Raw POST.
	 */
	public static function save( $posted ) {
		if ( ! is_array( $posted ) ) {
			return;
		}
		$clean = array();
		foreach ( self::keys() as $key ) {
			if ( ! isset( $posted[ $key ] ) ) {
				continue;
			}
			$val = sanitize_text_field( wp_unslash( $posted[ $key ] ) );
			if ( '' !== $val ) {
				$clean[ $key ] = $val;
			}
		}
		update_option( self::OPTION, $clean, false );
	}
}
