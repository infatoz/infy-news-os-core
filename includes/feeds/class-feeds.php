<?php
/**
 * Enhanced RSS and Google News feed.
 *
 * @package InfyNewsOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feed enhancements.
 */
class INOS_Feeds {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'rss2_ns', array( __CLASS__, 'rss_ns' ) );
		add_action( 'rss2_item', array( __CLASS__, 'rss_item' ) );
		add_filter( 'the_excerpt_rss', array( __CLASS__, 'full_or_dek' ) );
		add_filter( 'the_content_feed', array( __CLASS__, 'content_feed' ) );
		add_filter( 'option_rss_use_excerpt', array( __CLASS__, 'use_excerpt' ) );
	}

	/**
	 * Register google-news feed.
	 */
	public static function register() {
		if ( inos_get_option( 'enable_google_news_feed', 1 ) ) {
			add_feed( 'google-news', array( __CLASS__, 'google_news' ) );
		}
	}

	/**
	 * Extra RSS namespaces.
	 */
	public static function rss_ns() {
		echo ' xmlns:media="http://search.yahoo.com/mrss/" ';
	}

	/**
	 * Extra RSS item fields.
	 */
	public static function rss_item() {
		$post_id = get_the_ID();
		$img     = INOS_Images::og_image_url( $post_id );
		if ( $img ) {
			echo '<media:content url="' . esc_url( $img ) . '" medium="image" />' . "\n";
			echo '<media:thumbnail url="' . esc_url( $img ) . '" />' . "\n";
		}
		$section = inos_get_primary_section( $post_id );
		if ( $section ) {
			echo '<category>' . esc_html( $section->name ) . '</category>' . "\n";
		}
		$keywords = function_exists( 'inos_get_news_keywords' ) ? inos_get_news_keywords( $post_id ) : '';
		if ( $keywords ) {
			echo '<media:keywords>' . esc_html( $keywords ) . '</media:keywords>' . "\n";
		}
	}

	/**
	 * Prefer dek in excerpt RSS.
	 *
	 * @param string $excerpt Excerpt.
	 * @return string
	 */
	public static function full_or_dek( $excerpt ) {
		$dek = inos_get_dek( get_the_ID() );
		return $dek ? wp_strip_all_tags( $dek ) : $excerpt;
	}

	/**
	 * Keep full content in feeds.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public static function content_feed( $content ) {
		$id = get_the_ID();
		if ( $id && function_exists( 'inos_feed_story_html' ) && 'inos_live_blog' === get_post_type( $id ) ) {
			return inos_feed_story_html( $id );
		}
		return $content;
	}

	/**
	 * Full-content RSS when enabled.
	 *
	 * @param mixed $value Excerpt flag.
	 * @return mixed
	 */
	public static function use_excerpt( $value ) {
		if ( inos_get_option( 'rss_full_content', 1 ) ) {
			return '0';
		}
		return $value;
	}

	/**
	 * Dedicated Google News RSS.
	 */
	public static function google_news() {
		header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
		echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?' . '>' . "\n";
		?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title><?php echo esc_html( (string) inos_get_option( 'news_publication_name', get_bloginfo( 'name' ) ) ); ?></title>
		<link><?php echo esc_url( home_url( '/' ) ); ?></link>
		<description><?php echo esc_html( get_bloginfo( 'description' ) ); ?></description>
		<language><?php echo esc_html( (string) inos_get_option( 'publication_language', 'en' ) ); ?></language>
		<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
		<?php
		$q = new WP_Query(
			array(
				'post_type'           => array( 'post', 'inos_live_blog' ),
				'post_status'         => 'publish',
				'posts_per_page'      => 50,
				'ignore_sticky_posts' => true,
				'date_query'          => array(
					array(
						'after' => '48 hours ago',
					),
				),
			)
		);
		while ( $q->have_posts() ) :
			$q->the_post();
			if ( function_exists( 'inos_is_indexable' ) && ! inos_is_indexable( get_post() ) ) {
				continue;
			}
			$img  = INOS_Images::og_image_url( get_the_ID() );
			$body = function_exists( 'inos_feed_story_html' ) ? inos_feed_story_html( get_the_ID() ) : apply_filters( 'the_content', get_the_content() );
			?>
		<item>
			<title><?php echo esc_html( get_the_title() ); ?></title>
			<link><?php echo esc_url( get_permalink() ); ?></link>
			<guid isPermaLink="true"><?php echo esc_url( get_permalink() ); ?></guid>
			<pubDate><?php echo esc_html( get_post_time( 'D, d M Y H:i:s +0000', true ) ); ?></pubDate>
			<dc:creator><?php echo esc_html( get_the_author() ); ?></dc:creator>
			<description><![CDATA[<?php echo wp_kses_post( inos_get_dek( get_the_ID() ) ); ?>]]></description>
			<?php if ( inos_get_option( 'rss_full_content', 1 ) ) : ?>
			<content:encoded><![CDATA[<?php echo wp_kses_post( $body ); ?>]]></content:encoded>
			<?php endif; ?>
			<?php if ( $img ) : ?>
			<media:content url="<?php echo esc_url( $img ); ?>" medium="image" />
			<?php endif; ?>
		</item>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</channel>
</rss>
		<?php
	}
}
