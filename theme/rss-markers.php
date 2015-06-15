<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true);
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	xmlns:atom="http://www.w3.org/2005/Atom"
>
<?php
$args = array(
	'post_type'=> 'marker',
	'post_status'    => 'publish',
	'order'    => 'DESC',
        'posts_per_page' => 10,
        'orderby' => 'date'
);
query_posts( $args );
$lang = get_option( 'rss_language' );

?>
<channel xml:lang="<?php echo $lang; ?>">
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml"/>
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( "description" ); ?></description>
	<language><?php echo $lang; ?></language>
	<itunes:image href="<?php echo WP_PLUGIN_URL ?>/soundmap/img/soinumapa.jpg" />
	<image>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<url>
			<?php echo WP_PLUGIN_URL . '/soundmap/img/soinumapa_small.jpg' ?>
		</url>
		<link>http://www.soinumapa.net</link>
		<width>140</width>
		<height>140</height>
	</image>
	<?php do_action( 'rss2_head' ); ?>
	<?php while( have_posts() ) : the_post(); ?>
	<item xml:lang="<?php echo $lang; ?>">
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>

		<?php the_category_rss( 'rss2' ); ?>

		<guid isPermaLink="true"><?php the_guid(); ?></guid>
<?php if ( get_option( 'rss_use_excerpt') ) : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
	<?php if ( strlen( $post->post_content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php the_content_feed('rss2') ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
	<?php endif; ?>
<?php endif; ?>

<?php soundmap_rss_enclosure(); ?>
	<?php do_action( 'rss2_item' ); ?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
