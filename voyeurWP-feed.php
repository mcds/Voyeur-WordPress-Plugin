<?php
/*
Template Name: Voyeur Feed
*/
 
function vwp_rssDate($timestamp = NULL) {
  $timestamp = ($timestamp==NULL) ? time() : $timestamp;
  echo date(DATE_RSS, $timestamp);
}

$postFilter = 'post_status=publish'; // Makes sure we're only retrieving published posts.
if (isset($_GET)) {
	if (isset($_GET['author'])) {
		$postFilter .= '&author=' . wp_kses($_GET['author'], array());
	}
	if (isset($_GET['cat'])) {
		$postFilter .= '&cat=' . wp_kses($_GET['cat'], array());
	}
	if (isset($_GET['tag'])) {
		$postFilter .= '&tag=' . wp_kses($_GET['tag'], array());
	}
	if (isset($_GET['second'])) {
		$postFilter .= '&second=' . (int) wp_kses($_GET['second'], array());
	}
	if (isset($_GET['minute'])) {
		$postFilter .= '&minute=' . (int) wp_kses($_GET['minute'], array());
	}
	if (isset($_GET['hour'])) {
		$postFilter .= '&hour=' . (int) wp_kses($_GET['hour'], array());
	}
	if (isset($_GET['day'])) {
		$postFilter .= '&day=' . (int) wp_kses($_GET['day'], array());
	}
	if (isset($_GET['monthnum'])) {
		$postFilter .= '&monthnum=' . (int) wp_kses($_GET['monthnum'], array());
	}
	if (isset($_GET['year'])) {
		$postFilter .= '&year=' . (int) wp_kses($_GET['year'], array());
	}
}

$posts = query_posts($postFilter);
 
header("Content-Type: application/rss+xml; charset=UTF-8");
echo '<?xml version="1.0"?>';
?><rss version="2.0">
<channel>
  <title>Voyeur Feed</title>
  <link>http://voyeurtools.org/</link>
  <description>The feed created for Voyeur.</description>
  <language>en-us</language>
  <pubDate><?php vwp_rssDate(strtotime($posts[0]->post_date_gmt)); ?></pubDate>
  <lastBuildDate><?php vwp_rssDate(strtotime($posts[0]->post_date_gmt)); ?></lastBuildDate>
<?php foreach ($posts as $post) { ?>
  <item>
    <title><?php echo get_the_title($post->ID); ?></title>
    <link><?php echo get_permalink($post->ID); ?></link>
    <description><?php echo '<![CDATA['. $post->post_content .'<br/>' . ']]>'; ?></description>
    <pubDate><?php vwp_rssDate(strtotime($post->post_date_gmt)); ?></pubDate>
    <guid><?php echo get_permalink($post->ID); ?></guid>
  </item>
<?php } ?>
</channel>
</rss>