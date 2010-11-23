<?php
/**
 * This file holds all of the users
 * custom information
 */
require_once('../../../wp-config.php'); // Makes sure we can access WP functions.
/** 
 * This is the main Voyeur file so we
 * can create an instance of the class.
 */
require_once('voyeurWP.php'); // Allows us to access our other Voyeur functions.
global $wpdb;
$vwp = new VoyeurWP(); // Create new object based on the Voyeur class.

////////////////////////////////
////												////
////				LOAD VOYEUR			////
////												////
////////////////////////////////

if ($_POST['action'] == 'loadVoyeur') {

	//////////////////////////////////////////
	//// GENERATE ALL OF THE USER OPTIONS ////
	//// USED WITHIN #VoyeurControlsAjax  ////
	//////////////////////////////////////////
	
	//////////////////////
	//// AUTHOR SELECT ///
	//////////////////////
		
	// Find all users who can post and HAVE posted something
	$query = "SELECT ID, user_nicename FROM $wpdb->users WHERE ID IN (SELECT post_author FROM $wpdb->posts) ORDER BY user_nicename";
	$author_ids = $wpdb->get_results($wpdb->prepare($query));
	// In case we want to limit by user TYPE, use code below...:
	// (Although we already limit by user type with saying that we only take users who have POSTED something... See query above.)
	/*
	foreach ($author_ids as &$author) {
		$authorUserMeta = get_user_meta($author->ID, 'wp_capabilities');
		// If the author does not have any posting capabilities, remove them.
		if (!$authorUserMeta['administrator'] && !$authorUserMeta['author'] && !$authorUserMeta['editor']) {
			unset($author);
		}
	}
	*/

	echo '<br />';
	
	if (count($author_ids) > 1) { // Only display author options if WP has used more than one author.
		echo '<h4><strong>' . __('Filter by Author:') . '</strong></h4>';
		echo '<form id="voyeur_authors" name="voyeur_authors">';
		echo '<table class="voyeurOptionSelect"><tr><td width="50%">';
		$currentCol = 1;
		
		// Loop through each author.
		foreach($author_ids as $author) {
			echo '<label><input type="checkbox" value="' . $author->ID . '" ';
			echo 'name="author"';
			echo ' /> ' .  $author->user_nicename;
			echo "\n" . '</label></td>';
			if ($currentCol == 2) {
				echo "\n" . '</tr>' . "\n" . '<tr><td width="50%">';
				$currentCol = 1;
			} else {
				echo "\n" . '<td width="50%">';
				$currentCol++;
			}
		}
		echo '</td></tr></table>' . "\n" . '</form>' . "\n" . '<br />';
	}

	////////////////////////
	//// CATEGORY SELECT ///
	////////////////////////

	$args=array(
		'orderby' => 'name',
		'order' => 'ASC'
	);
	$categories = get_categories($args);	
	if (count($categories) > 1) { // Only display category options if WP has used more than one category.
		echo '<h4><strong>' . __('Filter by Category:') . '</strong></h4>';
		echo '<form id="voyeur_categories" name="voyeur_categories">';
		echo '<table class="voyeurOptionSelect"><tr><td width="50%">';
		$currentCol = 1;
		// Loop through each category.
		foreach($categories as $cat) {
			echo '<label><input type="checkbox" value="' . $cat->cat_ID . '" ';
			echo 'name="category" /> ' .  $cat->cat_name;
			echo "\n" . '</label></td>';
			if ($currentCol == 2) {
				echo "\n" . '</tr>' . "\n" . '<tr><td width="50%">';
				$currentCol = 1;
			} else {
				echo "\n" . '<td width="50%">';
				$currentCol++;
			}
		}
		echo '</td></tr></table>' . "\n" . '</form>' . "\n" . '<br />';
	}

	// Add tags and date fields for user Thickbox.
	echo $vwp->vwp_addTagsAndTimeFields('user');
}
	
////////////////////////////////
////												////
////			LOAD VALUES				////
////												////
////////////////////////////////
	
else if ($_POST['action'] == 'loadVals') {
	// Prepare checkbox inputs for use! //

	// Get rid of commas to put into array cleanly.
	if (isset($_POST['author'])) {
		$authors = vwp_sanitizeNumerical($_POST['author']);
	}
	if (isset($_POST['category'])) {
		$categories = vwp_sanitizeNumerical($_POST['category']);
	}
	//////////////////////////////////////
	
	$checkedIds = array(); // Create an array to store checkboxes that are NOT grayed out.
	
	////////////////////////
	////   FIND AUTHORS  ///
	////////////////////////
	
	$query = "SELECT ID FROM $wpdb->users WHERE ID IN (SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status = 'publish' ";
	if (isset($authors)) {
		$query .= "AND ("; //ID IN (SELECT object_id FROM $wpdb->term_relationships WHERE ";
		for ($i = 0; $i < count($authors); $i++) {
			$query .= 'post_author = ' . $authors[$i];
			if ($i != (count($authors) - 1) && count($authors) > 1) { // If we're not at the last author AND there's more than one, add 'OR' to our statement.
					$query .= ' || ';
			}
		}
		$query .= ") ";
	}
	if (isset($categories)) {
		$query .= "AND ID IN (SELECT object_id FROM $wpdb->term_relationships WHERE ";
			for ($i = 0; $i < count($categories); $i++) {
				$query .= 'term_taxonomy_id = ' . $categories[$i];
				if ($i != (count($categories) - 1) && count($categories) > 1)  // If we're not at the last category AND there's more than one, add 'OR' to our statement.
					$query .= ' || ';
			}
		$query .= ')';
	}
	$query .= ')';

	$result = $wpdb->get_results($wpdb->prepare($query));
	for ($i = 0; $i < count($result); $i++) { // Create array of authors that correspond to checked boxes.
		$checkedIds['authors'][$result[$i]->ID] = 1; // This strange array syntax is so we can use the Javascript 'in' operator later.
	}

	///////////////////////////
	////   FIND CATEGORIES  ///
	///////////////////////////
	
	$query = "SELECT DISTINCT term_taxonomy_id FROM $wpdb->term_relationships WHERE ";
	if (isset($authors)) {
		$query .= "object_id IN (SELECT DISTINCT ID FROM $wpdb->posts WHERE post_status = 'publish' AND ";
		for ($i = 0; $i < count($authors); $i++) {
			$query .= 'post_author = ' . $authors[$i];
			if ($i != (count($authors) - 1) && count($authors) > 1)  // If we're not at the last author AND there's more than one, add 'OR' to our statement.
				$query .= ' || ';
		}
		$query .= ') ';
	}
	if (isset($categories)) {
		if (isset($authors)) {  // If already added author filters, need to say AND for next statement.
			$query .= 'AND ';
		}
		$query .= "object_id IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships WHERE ";
		for ($i = 0; $i < count($categories); $i++) {
			$query .= 'term_taxonomy_id = ' . $categories[$i];
			if ($i != (count($categories) - 1) && count($categories) > 1)  // If we're not at the last category AND there's more than one, add 'OR' to our statement.
				$query .= ' || ';
		}
		$query .= ') ';
	}
	if (isset($authors) || isset($categories)) {
		$query .= 'AND ';
	}
	$query .= "term_taxonomy_id IN (SELECT DISTINCT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'category')";
	
	$result = $wpdb->get_results($wpdb->prepare($query));
	for ($i = 0; $i < count($result); $i++) { // create array of authors that correspond to checked boxes
		$checkedIds['categories'][$result[$i]->term_taxonomy_id] = 1; 
	}

	echo json_encode($checkedIds); // Final output to voyeurWP.js.php.
}

////////////////////////////////
////												////
////  FIND UNIX TIMESTAMP		////
////												////
////////////////////////////////
	
else if ($_POST['action'] == 'findUnixTimestamp') {

	if (isset($_POST['author'])) {
		$unixAuthors = wp_kses($_POST['author'], array());
	}
	if (isset($_POST['category'])) {
		$unixCategories = wp_kses($_POST['category'], array());
	}
	if (isset($_POST['tag'])) {
		$unixTags = wp_kses($_POST['tag'], array());
	}
	if (isset($_POST['day'])) {
		$unixDay = (int) absint($_POST['day']);
	}
	if (isset($_POST['monthnum'])) {
		$unixMonth = (int) absint($_POST['monthnum']);
	}
	if (isset($_POST['year'])) {
		$unixYear = (int) absint($_POST['year']);
	}
  
	// Find the unix timestamp from user-defined filters.
	echo $vwp->vwp_findUnixTimestamp($unixAuthors, $unixCategories, $unixTags, $unixDay, $unixMonth, $unixYear);
}

/**
 * Sanitizes user integer $_GET input.
 */
function vwp_sanitizeNumerical($data) {
	$sData = explode(',', rtrim(wp_kses($data, array()), ',')); // Explode into array while trimming and sanitizing.
	for ($i = 0; $i < count($sData); $i++) {
		$sData[$i] = (int) absint($sData[$i]); // Make sure data is int AND is not negative.
	}
	return $sData;
}
?>