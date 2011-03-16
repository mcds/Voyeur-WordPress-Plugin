<?php
/*
Template Name: Voyeur Feed
*/
 
function vwp_rssDate($timestamp = NULL) {
  $timestamp = ($timestamp==NULL) ? time() : $timestamp;
  echo date(DATE_RSS, $timestamp);
}

global $wp_query; // Set the $wp_query instance so we can find $_GET vars.
$postFilter = 'post_status=publish'; // Makes sure we're only retrieving published posts.

if (isset($wp_query->query_vars)) {
	if (!empty($wp_query->query_vars['p'])) { // Post ID of actual post. (If we're filtering individual post.)
		$postFilter .= '&p=' . (int) wp_kses($wp_query->query_vars['p'], array());
	}
	if (!empty($wp_query->query_vars['name'])) { // Post slug of actual post. (If we're filtering individual post.)
		$postFilter .= '&name=' . wp_kses($wp_query->query_vars['name'], array());
	}
	if (!empty($wp_query->query_vars['author'])) {
		$postFilter .= '&author=' . (int) wp_kses($wp_query->query_vars['author'], array());
	}
	if (!empty($wp_query->query_vars['author_name'])) {
		$postFilter .= '&author_name=' . wp_kses($wp_query->query_vars['author_name'], array());
	}
	if (!empty($wp_query->query_vars['cat'])) {
		$postFilter .= '&cat=' . (int) wp_kses($wp_query->query_vars['cat'], array());
	}
	if (!empty($wp_query->query_vars['category_name'])) {
		$postFilter .= '&category_name=' . wp_kses($wp_query->query_vars['category_name'], array());
	}
	if (!empty($wp_query->query_vars['tag'])) {
		$postFilter .= '&tag=' . wp_kses($wp_query->query_vars['tag'], array());
	}
	if (!empty($wp_query->query_vars['second'])) {
		$postFilter .= '&second=' . (int) wp_kses($wp_query->query_vars['second'], array());
	}
	if (!empty($wp_query->query_vars['minute'])) {
		$postFilter .= '&minute=' . (int) wp_kses($wp_query->query_vars['minute'], array());
	}
	if (!empty($wp_query->query_vars['hour'])) {
		$postFilter .= '&hour=' . (int) wp_kses($wp_query->query_vars['hour'], array());
	}
	if (!empty($wp_query->query_vars['day'])) {
		$postFilter .= '&day=' . (int) wp_kses($wp_query->query_vars['day'], array());
	}
	if (!empty($wp_query->query_vars['monthnum'])) {
		$postFilter .= '&monthnum=' . (int) wp_kses($wp_query->query_vars['monthnum'], array());
	}
	if (!empty($wp_query->query_vars['year'])) {
		$postFilter .= '&year=' . (int) wp_kses($wp_query->query_vars['year'], array());
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
  <pubDate><?php if(isset($posts[0])) { vwp_rssDate(strtotime($posts[0]->post_date_gmt)); } ?></pubDate>
  <lastBuildDate><?php if(isset($posts[0])) { vwp_rssDate(strtotime($posts[0]->post_date_gmt)); } ?></lastBuildDate>
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