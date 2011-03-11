<?php
/**
 * @package Wordpress
 * @version 0.1
 */
/*
Plugin Name: Voyeur
Plugin URI: http://voyeur.hermeneuti.ca/
Description: Allows Voyeur to reveal text trends within your Wordpress posts.
Version: 0.1
Author: Corey Slavnik and Stéfan Sinclair
Author URI: http://stefansinclair.name/
License: GPL2
*/
/*  Copyright 2010  Corey Slavnik and Stéfan Sinclair (email: corey@coreyslavnik.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
	Some methods and functions taken from Jonathan Kemp's
	'wp-users' plugin. Thank you!
*/

define('VWP_URL', WP_PLUGIN_URL . '/voyeurWP'); // Plugin URL.
define('VWP_DIR', WP_PLUGIN_DIR . '/voyeurWP'); // Plugin directory.
if (!class_exists('VoyeurWP')) {
/**
 * Voyeur WordPress Class
 *
 * The Voyeur WordPress Class is used to generate content and handle 
 * the construction of the Voyeur plugin and widget. It is referenced
 * by voyeurWP-ajax.php and voyeurWP.js.php for use of a few methods.
 *
 * It is the core of the Voyeur WordPress plugin.
 */
	class VoyeurWP {
		var $widgetOptionsName = "voyeur_widget_options"; // The universal name for the widget's options.

		function VoyeurWP() { // Constructor.

		}

		/**
		 * Loads external libraries into the head of the admin panel.
		 *
		 * Note that this only happens if our location IS an adminstrative page.
		 */
		function vwp_addAdminHeaderCode() { // Load our external libraries.
      if (is_admin() && is_active_widget(FALSE, FALSE, 'voyeur')) {
        //wp_enqueue_script('jquery');
        wp_enqueue_script('vwp-admin-js', VWP_URL . '/voyeurWP-admin.js', array('jquery'), '1.0'); // Loads our javascript ONLY if jquery has loaded.
      }
		} // end vwp_addHeaderCode()
    
		/**
		 * Loads external libraries into the head of the page.
		 *
		 * Note that this only happens if our location is NOT an adminstrative page.
		 */
		function vwp_addHeaderCode() { // Load our external libraries.
			if (!is_admin() && is_active_widget(FALSE, FALSE, 'voyeur')) { // Do not load if we're in the admin area or if widget not active.
				// Load Javascript.
				wp_enqueue_script('jquery');
				wp_enqueue_script('thickbox');
				wp_enqueue_script('vwp-js', VWP_URL . '/voyeurWP.js.php', array('jquery'), '1.0'); // Loads our javascript ONLY if jquery has loaded.
				// Load CSS.
				wp_enqueue_style('thickbox');
				wp_enqueue_style('vwp-css', VWP_URL . '/css/voyeurWP.css', '', '1.0');
			}
		} // end vwp_addHeaderCode()

		/**
		 * Establishes our widget and its content on-screen.
		 *
		 * @param string $args Specific theme information to incorporate.
		 */
		function vwp_establishWidgetContent($args) { // Displays the title of the widget.
			extract($args); // Extracts the necessary theme information.
			echo $before_widget; // Echo necessary theme tags before echo widget title.
			echo $before_title;
			echo 'Voyeur';
			echo $after_title; // Echo closing tags after echo widget title.
			echo '<div style="text-align:center; margin:0 auto;">'; // Create a div to center content in.
			$this->vwp_displayContent(); // Output content of plugin.
			echo '</div>';
		} // end vwp_establishWidgetContent()

		/**
		 * Prints the widget controls within the admin panel. (Appearance > Widgets)
		 *
		 * Also takes admin-defined options and saves them to the database.
		 */
		function vwp_widgetPanelPrint() {
			global $wpdb; // Declare global database object for queries.
			$vwpOptions = $this->vwp_getAdminOptions(); // Load pre-existing admin settings for the plugin.

			// Generate authors for multiple later use.
			$query = "SELECT ID, user_nicename FROM $wpdb->users WHERE ID IN (SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post')";
			$authors = $wpdb->get_results($wpdb->prepare($query));

			// Generate categories for multiple later use.
			$categories = get_categories(array('orderby' => 'name', 'order' => 'ASC'));

			// If nonce valid, admin 'submit' has been clicked, and user able to edit plugins, update the settings.
			if (isset($_POST['voyeur_submit']) && is_admin() && current_user_can('edit_plugins')) {
				check_admin_referer('voyeur-update-options', 'voyeur-update-options_nonce'); // Check if the nonces match to prevent hacking.

				if (isset($_POST['voyeur_width'])) {
					// Sanitize input with forching int and make sure not negative.
					$vwpOptions['voyeur_width'] = (int) absint($_POST['voyeur_width']);
				}
				if (isset($_POST['voyeur_height'])) {
					$vwpOptions['voyeur_height'] = (int) absint($_POST['voyeur_height']);
				}
				if (isset($_POST['voyeur_tool'])) {
					// Sanitize input by making sure tool matches defined tools.
					$possibleTools = array('Bubbles', 'Cirrus', 'CorpusTypeFrequenciesGrid', 'Links', 'Reader', 'CorpusSummary', 'WordCountFountain');
					if (in_array($_POST['voyeur_tool'], $possibleTools)) {
						$vwpOptions['voyeur_tool'] = $_POST['voyeur_tool'];
					} else {
						$vwpOptions['voyeur_tool'] = 'Cirrus';
					}
				}
				if (isset($_POST['allow_current_page']) && $_POST['allow_current_page'] == 'on') {
					$vwpOptions['allow_current_page'] = 1;
				} else {
					$vwpOptions['allow_current_page'] = NULL;
				}
				if (isset($_POST['allow_auto_reveal']) && $_POST['allow_auto_reveal'] == 'on') {
					$vwpOptions['allow_auto_reveal'] = 1;
				} else {
					$vwpOptions['allow_auto_reveal'] = NULL;
				}
				if (isset($_POST['allow_user']) && $_POST['allow_user'] == 'on') {
					$vwpOptions['allow_user'] = 1;
				} else {
					$vwpOptions['allow_user'] = NULL;
				}
				if (isset($_POST['allow_post_reveal']) && $_POST['allow_post_reveal'] == 'on') {
					$vwpOptions['allow_post_reveal'] = 1;
				} else {
					$vwpOptions['allow_post_reveal'] = NULL;
				}
        // Save value if it applies to the tool selected.
				if (isset($_POST['remove_func_words']) && $_POST['remove_func_words'] == 'on' && in_array($vwpOptions['voyeur_tool'], array('Bubbles', 'Cirrus', 'CorpusTypeFrequenciesGrid', 'Links', 'CorpusSummary'))) {
					$vwpOptions['remove_func_words'] = 1;
				} else {
					$vwpOptions['remove_func_words'] = NULL;
				}
				if (is_numeric($_POST['voyeur_limit_input']) && $vwpOptions['voyeur_tool'] == 'Cirrus') {
					$vwpOptions['voyeur_limit_input'] = (int) absint($_POST['voyeur_limit_input']);
				} else {
					$vwpOptions['voyeur_limit_input'] = NULL;
				}
				if (isset($_POST['voyeur_query_input']) && $vwpOptions['voyeur_tool'] == 'CorpusTypeFrequenciesGrid') {
					$vwpOptions['voyeur_query_input'] = wp_kses($_POST['voyeur_query_input'], array());
				} else {
					$vwpOptions['voyeur_query_input'] = NULL;
				}

				$vwpOptions['voyeur_authors'] = NULL; // Reset voyeur_authors to read in new selected authors.
				// Store author data.
				foreach ($authors as $author) {
					// Find out which available categories are filtered.
					if (isset($_POST['voyeur_author_' . $author->ID]) && $_POST['voyeur_author_' . $author->ID] == 'on') {
						$vwpOptions['voyeur_author_' . $author->ID] = 1;
						// Create 'voyeur_authors' to be passed as URL params later in voyeurWP.js.php AND for unix timestamp.
						if (isset($vwpOptions['voyeur_authors'])) {
							$vwpOptions['voyeur_authors'] .= ',' . $author->ID;
						} else {
							$vwpOptions['voyeur_authors'] = $author->ID;
						}
					} else {
						$vwpOptions['voyeur_author_' . $author->ID] = NULL;
					}
				}

				$vwpOptions['voyeur_categories'] = NULL; // Reset voyeur_categories to read in new selected authors.
				// Store category data.
				foreach ($categories as $category) {
					// Find out which available categories are filtered.
					if (isset($_POST['voyeur_cat_' . $category->term_id]) && $_POST['voyeur_cat_' . $category->term_id] == 'on') {
						$vwpOptions['voyeur_cat_' . $category->term_id] = 1;
						// Create 'voyeur_categories' to be passed as URL params later in voyeurWP.js.php AND for unix timestamp.
						if (isset($vwpOptions['voyeur_categories'])) {
							$vwpOptions['voyeur_categories'] .= ',' . $category->term_id;
						} else {
							$vwpOptions['voyeur_categories'] = $category->term_id;
						}
					} else {
						$vwpOptions['voyeur_cat_' . $category->term_id] = NULL;
					}
				}
				if (isset($_POST['voyeur_tags']) && $_POST['voyeur_tags'] != '') {
					$vwpOptions['voyeur_tags'] = wp_kses($_POST['voyeur_tags'], array());
				} else {
					$vwpOptions['voyeur_tags'] = '';
				}
				if (isset($_POST['voyeur_time_day'])) {
					$vwpOptions['voyeur_time_day'] = (int) absint($_POST['voyeur_time_day']);
					// Check to see if the user value is a valid day.
					if ($vwpOptions['voyeur_time_day'] < 1 || $vwpOptions['voyeur_time_day'] > 31) {
						$vwpOptions['voyeur_time_day'] = '';
					}
				}
				if (isset($_POST['voyeur_time_month'])) {
					$vwpOptions['voyeur_time_month'] = (int) absint($_POST['voyeur_time_month']);
					// Check to see if the user value is a valid month.
					if ($vwpOptions['voyeur_time_month'] < 1 || $vwpOptions['voyeur_time_month'] > 12) {
						$vwpOptions['voyeur_time_month'] = '';
					}
				}
				if (isset($_POST['voyeur_time_year'])) {
					$vwpOptions['voyeur_time_year'] = (int) absint($_POST['voyeur_time_year']);
					// Check to see if the user value is a valid year. (Within the bounds of UNIX timestamps.)
					if ($vwpOptions['voyeur_time_year'] < 1970 || $vwpOptions['voyeur_time_year'] > 2038) {
						$vwpOptions['voyeur_time_year'] = '';
					}
				}
        $vwpOptions['voyeur_unix_timestamp'] = $this->vwp_findUnixTimestamp($vwpOptions['voyeur_authors'], $vwpOptions['voyeur_categories'], $vwpOptions['voyeur_tags'], $vwpOptions['voyeur_time_day'], $vwpOptions['voyeur_time_month'], $vwpOptions['voyeur_time_year']);
				update_option($this->widgetOptionsName, $vwpOptions);
			 }
			 ?>
			<!-- BEGIN ADMIN PAGE GENERATION -->

			<div class=wrap>
				<?php
					if (function_exists('wp_nonce_field')) { // Create a nonce for this form to be validated when submit pressed.
						wp_nonce_field('voyeur-update-options', 'voyeur-update-options_nonce');
					}
				?>
				<h3><?php echo __('General settings'); ?></h3>
				<table width="100%">
					<tr>
						<td width="30%" style="vertical-align: middle;">
							<label for="voyeur_width">
                <h5><?php echo __('Width:'); ?></h5>
							</label>
						</td>
						<td width="70%" style="vertical-align: middle;">
							<input type="text" name="voyeur_width" size="5" maxlength="4" autocomplete="off"
								value="<?php if (isset($vwpOptions['voyeur_width'])) echo $vwpOptions['voyeur_width']; ?>"
							/>&nbsp;%
						</td>
					</tr>
          <tr>
						<td width="30%" style="vertical-align: middle;">
							<label for="voyeur_height">
                <h5><?php echo __('Height:'); ?></h5>
							</label>
						</td>
						<td width="70%" style="vertical-align: middle;">
							<input type="text" name="voyeur_height" size="5" maxlength="4" autocomplete="off"
								value="<?php if (isset($vwpOptions['voyeur_height'])) echo $vwpOptions['voyeur_height']; ?>"
							/>&nbsp;px
						</td>
					</tr>
				</table>
				<h4><strong><?php echo __('Tool:'); ?></strong></h4>
				<select id="voyeur_tool" name="voyeur_tool" title="<?php echo 'Voyeur '; echo __('tool selection.'); ?>">
					<!-- <option value="Bubbles"<?php if ($vwpOptions['voyeur_tool'] == 'Bubbles') echo 'selected="selected"'; echo '>' . __('Bubbles'); ?></option> -->
					<option value="Cirrus"<?php if ($vwpOptions['voyeur_tool'] == 'Cirrus' || !isset($vwpOptions['voyeur_tool'])) echo 'selected="selected"'; ?>>Cirrus</option>
					<!-- <option value="CorpusTypeFrequenciesGrid"<?php if ($vwpOptions['voyeur_tool'] == 'CorpusTypeFrequenciesGrid') echo 'selected="selected"'; echo '>' . __('Frequency Grid'); ?></option> -->
					<!-- <option value="Links"<?php if ($vwpOptions['voyeur_tool'] == 'Links') echo 'selected="selected"'; echo '>' . __('Links'); ?></option> -->
					<!-- <option value="Reader"<?php if ($vwpOptions['voyeur_tool'] == 'Reader') echo 'selected="selected"'; echo '>' . __('Reader'); ?></option> -->
					<!-- <option value="CorpusSummary"<?php if ($vwpOptions['voyeur_tool'] == 'CorpusSummary') echo 'selected="selected"'; echo '>' . __('Summary'); ?></option> -->
					<!-- <option value="WordCountFountain"<?php if ($vwpOptions['voyeur_tool'] == 'WordCountFountain') echo 'selected="selected"'; echo '>' . __('Word Count Fountain'); ?></option> -->
				</select>
				<br />
				<br />
				<p>
					<input type="checkbox" name="allow_current_page"<?php if ($vwpOptions['allow_current_page'] == 1) echo ' checked="checked" '; ?>/>
					<label for="allow_current_page"><?php echo __('Reveal items associated with current viewed page.'); ?></label>
				</p>
				<p>
					<input type="checkbox" name="allow_auto_reveal"<?php if ($vwpOptions['allow_auto_reveal'] == 1) echo ' checked="checked" '; ?>/>
					<label for="allow_auto_reveal"><?php echo __('Voyeur launches automatically on page load.'); ?></label>
				</p>
				<p>
					<input type="checkbox" name="allow_user" <?php if ($vwpOptions['allow_user'] == 1) echo ' checked="checked" '; ?>/>
					<label for="allow_user"><?php echo __('Allow users to choose Voyeur options.'); ?></label>
				</p>
				<p>
					<input type="checkbox" name="allow_post_reveal" <?php if ($vwpOptions['allow_post_reveal'] == 1) echo ' checked="checked" '; ?>/>
					<label for="allow_post_reveal"><?php echo __('Generate links to reveal individual posts.'); ?></label>
				</p>
        <div id='remove_func_words' style='display:none'>
          <p>
            <input type="checkbox" name="remove_func_words" <?php if ($vwpOptions['remove_func_words'] == 1) echo ' checked="checked" '; ?>/>
            <label for="remove_func_words"><?php echo __('Remove function words like "the".'); ?></label>
          </p>
        </div>
        <div id='voyeur_limit' style='display:none'>
          <p>
            <h4><strong><?php echo __('Number of words to display:'); ?></strong></h4>
            <input id="voyeur_limit_input" class="widefat" type="text" name="voyeur_limit_input" value="<?php if (isset($vwpOptions['voyeur_limit_input'])) echo $vwpOptions['voyeur_limit_input']; ?>" />
          </p>
				</div>
        <div id='voyeur_query' style='display:none'>
          <p>
            <h4><strong><?php echo __('Search term:'); ?></strong></h4>
            <input id="voyeur_query_input" class="widefat" type="text" name="voyeur_query_input" value="<?php if (isset($vwpOptions['voyeur_query_input'])) echo $vwpOptions['voyeur_query_input']; ?>" />
          </p>
				</div>
				<hr />
				<h3><?php echo __('Filter settings'); ?></h3>
				<small><?php echo __('These settings determine which posts Voyeur will analyze or "reveal".'); ?></small>
				<?php
					// ===========================
					// ==   AUTHOR GENERATION   ==
					// ===========================

					if (count($authors) > 1) { // Only display author options if WP has used more than one author.
						echo '<h4><strong>' . __('Filter by Author:') . '</strong></h4>';
						foreach($authors as $author) {
							echo '<input type="checkbox" ';
							if (isset($vwpOptions['voyeur_author_' . $author->ID]) && $vwpOptions['voyeur_author_' . $author->ID] == 1) echo 'checked="checked"';
							echo ' name="voyeur_author_' . $author->ID . '" />' . "\n";
							echo '<label for="voyeur_author_' . $author->ID . '">' . $author->user_nicename . '</label>';
							echo "\n" . '<br />';
						}
					}
				?>
				<?php
					// =============================
					// ==   CATEGORY GENERATION   ==
					// =============================
					if (count($categories) > 1) { // Only display category options if WP has used more than one category.
						echo '<h4><strong>' . __('Filter by Category:') . '</strong></h4>';
						foreach($categories as $cat) {
							echo '<input type="checkbox" ';
							if (isset($vwpOptions['voyeur_cat_' . $cat->cat_ID]) && $vwpOptions['voyeur_cat_' . $cat->cat_ID] == 1) echo 'checked="checked"';
							echo ' name="voyeur_cat_' . $cat->cat_ID . '" />' . "\n";
							echo '<label for="voyeur_cat_' . $cat->cat_ID . '">' . $cat->cat_name . '</label>';
							echo "\n" . '<br />';
						}
					}

					// Output the fields of tags and time.
					echo $this->vwp_addTagsAndTimeFields('admin');
				?>
				<input type="hidden" id="voyeur_submit" name="voyeur_submit" value="1" />
			</div>				

			<!-- END ADMIN PAGE GENERATION -->
		<?php
		} // end function vwp_widgetPanelPrint()

		/**
		 * Finds the UNIX timestamp for the current operation.
		 *
		 * This function is accessed when an admin saves their settings to obtain the timestamp.
     * It's also used to dynamically retrieve the timestamp when a user chooses custom
     * filtering settings for Voyeur.
		 *
		 * @param string $authors, $categories, $tags, $day, $month, $year
     *    All of the defined params about filtering Voyeur content.
		 */    
    function vwp_findUnixTimestamp($authors, $categories, $tags, $day, $month, $year) {
      $postSorting = 'post_status=publish';
      if (isset($authors) && $authors != '') {
        $postSorting .= '&author=' . $authors;
      }
      if (isset($categories) && $categories != '') {
        $postSorting .= '&cat=' . $categories;
      }
      if (isset($tags) && $tags != '') {
        $postSorting .= '&tag=' . $tags;
      }
      if (isset($day) && $day != '') {
        $postSorting .= '&day=' . $day;
      }
      if (isset($month) && $month != '') {
        $postSorting .= '&monthnum=' . $month;
      }
      if (isset($year) && $year != '') {
        $postSorting .= '&year=' . $year;
      }

      $postList = query_posts($postSorting);
      foreach ($postList as $post) {
        $unixTimestamp = strtotime($post->post_modified); // Use post_modified to always get up-to-date timestamp.
      }
      return $unixTimestamp;
    }

		/**
		 * Prints the tag and date input forms for filtering.
		 *
		 * This function exists as the tag and date forms are exactly the same
		 * on the administrative side and within the user Thickbox controls.
		 *
		 * @param string $type
		 * 					Whether or not we're generating content for the admin screen. ('admin')
		 * 					If 'user', we're generating content for the user Thickbox.
		 */
		function vwp_addTagsAndTimeFields($type = 'user') {
			global $wpdb; // Declare global database object for queries.
			$vwpOptions = $this->vwp_getAdminOptions(); // Load pre-existing admin settings for the plugin.
			$finalOutput = '';

			// =============================
			// ==        TAGS FIELD       ==
			// =============================

			if (get_tags()) { // Only provide filtering by tags if tags exist.
				$finalOutput .= '<h4><strong>' . __('Filter by Tags:') . '</strong></h4>';
				$finalOutput .= '<input id="voyeur_tags" class="widefat" type="text" name="voyeur_tags" ';
				// Acquire tags value.
				if (isset($vwpOptions['voyeur_tags']) && $vwpOptions['voyeur_tags'] != '' && $type == 'admin') {
					$finalOutput .= 'value="' . $vwpOptions['voyeur_tags'] . '" ';
				} else {
					$finalOutput .= 'value="" ';
				}
				$finalOutput .= ' maxlength="128" />';
				$finalOutput .= '<br />';
				$finalOutput .= '<small>' . __('Separate tags by commas. ') . '","' . '</small>';
				$finalOutput .= '<br /><br />';
			}

			// =============================
			// ==        TIME FIELDS      ==
			// =============================
			
			$finalOutput .= '<h4><strong>' . __('Filter by Time:') . '</strong></h4>';
			$finalOutput .= '<select id="voyeur_time_day" name="voyeur_time_day">';

			// Create day select with blank value at top.
			$finalOutput .= "<option value=''";
			// If there's not day option or we're generating for user.
			if (!isset($vwpOptions['voyeur_time_day']) || $type == 'user') {
				$finalOutput .= " selected='selected'";
			}
			$finalOutput .= '>--</option>' . "\n";
			for ($i = 1; $i <= 31; $i++) {
				$finalOutput .= "<option value='$i'";
				if ($vwpOptions['voyeur_time_day'] == $i && $type == 'admin') {
					$finalOutput .= " selected='selected'";
				}
				$finalOutput .= ">$i</option>" . "\n";
			}
			$finalOutput .= '</select>';
			$finalOutput .= '<select id="voyeur_time_month" name="voyeur_time_month">';

			// Create month select with blank value at top.
			$finalOutput .= "<option value=''";
			// If there's not month option or we're generating for user.
			if (!isset($vwpOptions['voyeur_time_month']) || $type == 'user') {
				$finalOutput .= " selected='selected'";
			}
			$finalOutput .= '>--</option>' . "\n";
			$monthUnix = 1314871; // Create timestamp halfway through January to begin with.
			for ($i = 1; $i <= 12; $i++) {
				$finalOutput .= "<option value='$i'";
				if ($vwpOptions['voyeur_time_month'] == $i && $type == 'admin') {
					$finalOutput .= " selected='selected'";
				}
				$finalOutput .= ">" . date('F', $monthUnix) . "</option>" . "\n";
				$monthUnix += 2629743; // Add a whole month in seconds.
			}
			$finalOutput .= '</select>';
			$finalOutput .= '<select id="voyeur_time_year" name="voyeur_time_year">';

			// Find oldest year so we know which years to put as filtering options.
			$query = "SELECT post_date FROM $wpdb->posts WHERE post_type = 'post' ORDER BY post_date ASC LIMIT 0, 1";
			$oldestPostDate = strtotime($wpdb->get_var($wpdb->prepare($query)));
			$oldestYear = date('Y', $oldestPostDate);
			if ($oldestYear != date('Y')) { // If oldest year doesn't match current year, create years in between.
				for ($year = $oldestYear; $year <= date('Y'); $year++) {
					// Assigns a variable that is whether the selected attribute should be included.
					if ($vwpOptions['voyeur_time_year'] == $year && $type == 'admin') { // Find out which year is selected.
						$isSelected = " selected='selected'";
					} else {
						$isSelected = '';
					}
					$yearOutput[] = "<option value='$year'$isSelected>$year</option>" . "\n";
				}
				unset($isSelected);
				unset($year);
				$yearOutput = array_reverse($yearOutput); // Reverse the array so current year is displayed.
				} else {
					if ($vwpOptions['voyeur_time_year'] == $oldestYear && $type == 'admin') { // Find out if this year is selected.
						$yearOutput[] = "<option value='$oldestYear' selected='selected'>$oldestYear</option>" . "\n";
					} else {
						$yearOutput[] = "<option value='$oldestYear'>$oldestYear</option>" . "\n";
					}
				}
				// Create year select with blank value at top.
				$finalOutput .= '<option value=""';
				// If there's not year option or we're generating for user.
				if (!isset($vwpOptions['voyeur_time_year']) || $type == 'user') {
					$finalOutput .= ' selected="selected"';
				}
				$finalOutput .= '>--</option>' . "\n";

				foreach ($yearOutput as $year) { // Cycle through years and output them to the <select>.
					$finalOutput .= $year;
				}
			$finalOutput .= '</select>';
			
			return $finalOutput;
		} // end vwp_addTagsAndTimeFields()

		/**
		 * Displays the actual content within the frontend widget.
		 */
		function vwp_displayContent() { // Displays the actual iframe and Voyeur info.
			$vwpOptions = $this->vwp_getAdminOptions(); // Get stats like width & height.
			// Echo the iframe with custom admin paramaters (width-% and height-px).
			echo '<div id="voyeurContainer">' . "\n";
			//echo '<br />';
			echo '<img id="voyeurLogo" src="' . WP_PLUGIN_URL . '/voyeurWP/voyeur.png" />';
			echo '<iframe id="voyeurIframe" style="display:none;">';
			echo '<p>' . __('Your browser does not support iframes - Voyeur will not run.') . '</p></iframe>' . "\n";
			//echo '<div id="viewSeparate"><!-- "View separate" link placed here. --></div>' . "\n";
			echo '</div>' . "\n";
			?>

			<input alt="#TB_inline?height=400&width=400&inlineId=voyeurControls" title="Voyeur - <?php echo __('Reveal your texts'); ?>" class="thickbox" type="button" value="Reveal" id="voyeurReveal" />
			<div id="voyeurControls">
				<br />
				<h3><?php echo __('What should Voyeur reveal?'); ?></h3>
				<small><?php echo __('These settings determine which articles '); echo '<a href="http://voyeurtools.org" target="_new">Voyeur</a>'; echo __(' will analyze or "reveal".'); ?></small>
				<br /><br />
				<h4><strong><?php echo __('Tool:'); ?></strong></h4>
					<select id="voyeur_tool" name="voyeur_tool" title="<?php echo __('Tool:'); ?>">
						<!-- <option value="Bubbles"><?php echo __('Bubbles'); ?></option> -->
						<option value="Cirrus" selected="selected">Cirrus</option>
						<!-- <option value="CorpusTypeFrequenciesGrid"><?php echo __('Frequency Grid'); ?></option> -->
						<!-- <option value="Links"><?php echo __('Links'); ?></option> -->
						<!-- <option value="Reader"><?php echo __('Reader'); ?></option> -->
						<!-- <option value="CorpusSummary"><?php echo __('Summary'); ?></option>-->
						<!-- <option value="WordCountFountain"><?php echo __('Word Count Fountain'); ?></option> -->
					</select>
				<br />
				<div id="voyeurControlsAjax"><!-- AJAX content generated HERE! --></div>
				<br />
				<input type="button" id="voyeurOptionsSubmit" value="Submit" onclick="parent.tb_remove();" />
			</div>
			<?php
		}

		/**
		 * Retrieves all of the administrative settings for the Voyeur widget.
		 *
		 * Also, this function saves/updates settings specified by the admin.
		 *
		 * @param array $adminOptions The set of administrative settings/options for use within the widget settings screen.
		 */
		function vwp_getAdminOptions() {
			$adminOptions = array( 'voyeur_width' => '100',
				'voyeur_height' => '250', 'voyeur_tool' => 'Cirrus', 'allow_current_page' => 1, 'allow_auto_reveal' => 1,
				'allow_user' => NULL, 'remove_func_words' => 1, 'allow_post_reveal' => NULL, 'voyeur_limit_input' => '',
        'voyeur_query_input' => ''
				);
			$vwpOptions = get_option($this->widgetOptionsName);
			// If options previously set, retrieves them and overwrites our defaults set
			if (!empty($vwpOptions)) {
				foreach ($vwpOptions as $key => $option) {
					$adminOptions[$key] = $option;
				}
			}
			update_option($this->widgetOptionsName, $adminOptions); // Update the admin options before page displayed.
			return $adminOptions;
		}

		/**
		 * Sets up the 'Reveal' link after posts.
		 *
		 * This method is a workaround as narrowing RSS paramaters ends up displaying
		 * comments for a post rather than post content. Therefore, we narrow our
		 * post by specific time to reveal only posts at that time.
		 *
		 * @param string $content The content to be added to the end of a post.
		 */
		function vwp_addRevealLink($content = '') {
			global $post;
			if (is_home() || is_single()) { // Only display 'Reveal' link if user is on homepage or single post page.
				$content .= '<small><a class="voyeurRevealPost" name="reveal_' . get_the_ID() . '" title="' . __('Analyzes the current post with Voyeur.') . '" ';
				$content .= 'href="' . get_bloginfo('url') . '" onClick="return false;">(' . __('Reveal with Voyeur') . ')</a></small>';
				$content .= '<div id="reveal_' . get_the_ID() . '" style="display:none;">' . "\n";
				$content .= '<div title="reveal_author">' . $post->post_author . '</div>';
				$content .= '<div title="reveal_cat">';
				foreach(get_the_category() as $category) { // As posts may have multiple categories, make string of cats.
					$content .= $category->cat_ID . ',';
				}
				$content = rtrim($content, ',');
				$content .= '</div>';
				$content .= '<div title="reveal_second">' . get_the_date('s') . '</div>';
				$content .= '<div title="reveal_minute">' . get_the_date('i') . '</div>';
				$content .= '<div title="reveal_hour">' . get_the_date('H') . '</div>';
				$content .= '<div title="reveal_day">' . get_the_date('d') . '</div>';
				$content .= '<div title="reveal_month">' . get_the_date('m') . '</div>';
				$content .= '<div title="reveal_year">' . get_the_date('Y') . '</div>';
        $content .= '<div title="reveal_unix_timestamp">' . strtotime(get_the_date('Y-m-d G:i:s')) . '</div>';
				$content .= '</div>';
			}
			return $content;
		} // end vwp_addRevealLink()
	} // end class VoyeurWP
}

/**
 * Sets up the Voyeur widget by calling vwp_establishWidget() in the class.
 */
function vwp_establishWidget() {
	global $vwp; // Use 'global' because defined outside of this function.
	wp_register_sidebar_widget('voyeur', 'Voyeur', array(&$vwp, 'vwp_establishWidgetContent'), array('description' => 'Performs text analysis on any number of posts'));
	wp_register_widget_control('voyeur', 'Voyeur', array(&$vwp, 'vwp_widgetPanelPrint'));
} // end function vwp_establishWidget()

/**
 * Establishes our custom RSS feed to be read by Voyeur Tools.
 */
function vwp_createFeed() {
	load_template(VWP_DIR . '/voyeurWP-feed.php'); // Find the Voyeur feed template and execute it.
} // end function vwp_createFeed()

/**
 * Rewrites WP rules for URL construction so we can access /feed/voyeur as
 * well as /?feed=voyeur.
 *
 * @param object $wp_rewrite The rewrite object to add new rules to.
 */
function vwp_feedRewrite($wp_rewrite) {
  $new_rules = array(
    'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)
  );
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
} // end vwp_feedRewrite()

/**
 * Simply adds our custom Voyeur feed to Wordpress.
 */
function vwp_addFeed() {
	global $wp_rewrite;
	add_feed('voyeur', 'vwp_createFeed');
	add_filter('generate_rewrite_rules', 'vwp_feedRewrite');
	$wp_rewrite->flush_rules();
} // end vwp_addFeed()

// Check for the required plugin functions - this will prevent fatal
// errors occurring when you deactivate the dynamic-sidebar plugin.
if (function_exists('wp_register_sidebar_widget')) {
	// Create instance of main class 'VoyeurWP'.
	$vwp = new VoyeurWP();

	if (isset($vwp)) {
		$vwpOptions = $vwp->vwp_getAdminOptions();
		add_action('init', 'vwp_addFeed'); // Add our custom feed for Voyeur when WP starts.
		add_action('wp_head', array(&$vwp, 'vwp_addHeaderCode'), 1); // Call vwp_addHeaderCode to link to external libraries.
    add_action('admin_init', array(&$vwp, 'vwp_addAdminHeaderCode')); // Call vwp_addAdminHeaderCode to link to external libraries.
		add_action('plugins_loaded', 'vwp_establishWidget'); // Once plugins loaded, call the establishment function.

		// If admin has allowed revealing by post and Voyeur widget exists, call function to add 'Reveal' link.
		if ($vwpOptions['allow_post_reveal'] == 1 && is_active_widget(FALSE, FALSE, 'voyeur')) {
			add_action('the_content', array(&$vwp, 'vwp_addRevealLink'));
		}
	}
}
?>