<?php
	if (!function_exists('add_action')) { // Makes sure we can access WP functions.
		/** 
		 * This file holds all of the users
		 * custom information
		 */
		require_once('../../../wp-config.php');
		/** 
		 * This is the main Voyeur file so we
		 * can create an instance of the class.
		 */
		require_once('voyeurWP.php');
		$vwp = new VoyeurWP();
		// Load pre-existing admin settings for the plugin.
		$vwpOptions = $vwp->vwp_getAdminOptions();
	}
?>

// Define vars for later use.
var pluginURL = '<?php echo VWP_URL; // VWP_URL already defined in voyeurWP.php. ?>';
<?php
	// The URL of the WP site.
	$pageURL = get_bloginfo('url') . '/';
?>

// Load Thickbox animation and close button.
if (typeof tb_pathToImage != 'string') {
	var tb_pathToImage = "<?php echo get_bloginfo('wpurl').'/wp-includes/js/thickbox'; ?>/loadingAnimation.gif";
}
if (typeof tb_closeImage != 'string') {
	var tb_closeImage = "<?php echo get_bloginfo('wpurl').'/wp-includes/js/thickbox'; ?>/tb-close.png";
}

jQuery(document).ready(function($) {
	// $() will work as an alias for jQuery() inside of this function.

	// Create references to these elements for use inside and outside of function($).
	var voyeurTool = '<?php if (isset($vwpOptions['voyeur_tool'])) echo $vwpOptions['voyeur_tool']; else echo 'Cirrus'; ?>';
	var allowAutoReveal = '<?php if (isset($vwpOptions['allow_auto_reveal'])) echo $vwpOptions['allow_auto_reveal']; ?>';
	var allowUser = '<?php if (isset($vwpOptions['allow_user'])) echo $vwpOptions['allow_user']; ?>';
  var removeFuncWords = '<?php if (isset($vwpOptions['remove_func_words'])) echo $vwpOptions['remove_func_words']; ?>';
	var voyeurWindow = $('#voyeurControls');
	var voyeurWindowAjax = $('#voyeurControlsAjax');
	var voyeurLogo = $('#voyeurLogo');
	var voyeurIframe = $('#voyeurIframe');
	var viewSeparate = $('#viewSeparate');
	var ajaxRef = $.ajax; // Create reference to jQuery's AJAX function.
	var ajaxLaunch = false; // Create var to track if AJAX has been executed this session.

	if (allowUser != 1) { // Remove Thickbox attributes if user cannot choose options.
		$('#voyeurReveal').removeClass('thickbox').attr('alt', '');
	}

	if (allowAutoReveal == 1) {
		if (allowUser != 1) { // If allowAutoReveal is on and users cannot choose options, hide the 'Reveal' button.
      $('#voyeurReveal').attr('style', 'display:none;');
    }
		vwp_loadVoyeur(voyeurTool, allowUser, removeFuncWords, voyeurLogo, voyeurIframe, viewSeparate);
	}

  // ===============================
  // ==   Voyeur 'Reveal' Click   ==
  // ===============================

	$('#voyeurReveal').click(function() {
		if (allowUser == '1') {
			if (ajaxLaunch == false) { // Only load content once.
				vwp_loadAjax(voyeurWindowAjax, ajaxRef);
				ajaxLaunch = true;
			}
			// If submit was clicked, call Voyeur with custom params.
			// A NOTE ABOUT URL CONSTRUCTION:
			// 		Commas are used after every param because it forces our params to be read
			//		via RSS as filters if WP is using pretty permalinks.
			$('#voyeurOptionsSubmit').click(function() {
				URLParams = '?feed=voyeur'; // Initialize base parameters for Voyeur.

				// Find authors.
				if ($('#voyeur_authors').find('input:checked').val()) { // If any boxes checked, go thru.
					URLParams += '&author=';
					$('#voyeur_authors').find('input:checked').each(function() { // Finds all of the checkboxes that are clicked.
						URLParams += $(this).val() + ',';
					});
				}

				// Find categories.
				if ($('#voyeur_categories').find('input:checked').val()) { // If any boxes checked, go thru.
					URLParams += '&cat=';
					$('#voyeur_categories').find('input:checked').each(function () { // Finds all of the checkboxes that are clicked.
						URLParams += $(this).val() + ',';
					});
				}

				// Find tags.
				if ($('#voyeur_tags').val()) { // If anything typed, go thru.
					URLParams += '&tag=' + $('#voyeur_tags').val();
				}

				// Find time - day.
				if ($('#voyeur_time_day').val() != '') { // If any value, go thru.
					URLParams += '&day=' + $('#voyeur_time_day').val();
				}

				// Find time - month.
				if ($('#voyeur_time_month').val() != '') { // If any value, go thru.
					URLParams += '&monthnum=' + $('#voyeur_time_month').val();
				}

				// Find time - year.
				if ($('#voyeur_time_year').val() != '') { // If any value, go thru.
					URLParams += '&year=' + $('#voyeur_time_year').val();
				}

				vwp_loadVoyeur($('#voyeur_tool').val(), allowUser, removeFuncWords, voyeurLogo, voyeurIframe, viewSeparate, URLParams);
			});
		} else {
			vwp_loadVoyeur(voyeurTool, allowUser, removeFuncWords, voyeurLogo, voyeurIframe, viewSeparate);
		}
	});

    // =========================================
    // ==    Individual Reveal Post Click     ==
    // =========================================

    $('.voyeurRevealPost').click(function() {
    	URLParams = '?feed=voyeur'; // Initialize base parameters for Voyeur.
    	// Find the values of all of the divs with date information
    	$('#' + $(this).attr('name')).children('div').each(function() {
				if ($(this).attr('title') == 'reveal_author') {
    			URLParams += '&author=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_cat') {
    			URLParams += '&cat=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_second') {
    			URLParams += '&second=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_minute') {
    			URLParams += '&minute=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_hour') {
    			URLParams += '&hour=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_day') {
    			URLParams += '&day=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_month') {
    			URLParams += '&monthnum=' + $(this).html();
    		} else if ($(this).attr('title') == 'reveal_year') {
    			URLParams += '&year=' + $(this).html();
    		}
    	});
    	vwp_loadVoyeur(voyeurTool, allowUser, removeFuncWords, voyeurLogo, voyeurIframe, viewSeparate, URLParams);
    });

}); // end jQuery function($)

/**
 * Handles the load request to voyeurWP-ajax.php.
 *
 * Retrieves the user Thickbox content so they can choose options.
 *
 * @param object voyeurWindowAjax References jQuery where the AJAX content will be generated.
 * @param object ajaxRef References a jQuery AJAX instance.
 */
function vwp_loadAjax(voyeurWindowAjax, ajaxRef) { // Launch the Thickbox via AJAX.
		ajaxRef({
			type: 'POST',
			url: pluginURL + '/voyeurWP-ajax.php',
			data: 'action=loadVoyeur',
			success: function(msg) { // Run the function that shows our dialog if 'Reveal' clicked, and AJAX worked properly.
				voyeurWindowAjax.html(msg); // Generate content within the AJAX div.
				vwp_optionsListener(voyeurWindowAjax, ajaxRef); // Bind a listener to listen for user clicks.
			}
		});
} // end vwp_loadAjax()

/**
 * The final loading of Voyeur by loading a URL into the iFrame pointing to voyeurtools.org.
 *
 * If users cannot choose options, we find admin options to construct the URL.
 *
 * @param object voyeurTool References jQuery where the Voyeur tool selection is. (Within the Thickbox.)
 * @param int allowUser Whether or not users are allowed to choose Voyeur options/settings.
 * @param object voyeurLogo References jQuery where the Voyeur logo div is.
 * @param object voyeurIframe References jQuery where the IFrame is.
 * @param object viewSeparate References jQuery where the viewSeparate div is.
 * @param string URLParams The set user URL Params. (This may not always be set if users cannot choose settings.)
 */
function vwp_loadVoyeur(voyeurTool, allowUser, removeFuncWords, voyeurLogo, voyeurIframe, viewSeparate, URLParams) {
	if (typeof URLParams == 'undefined') { // If user did not set params, set params to admin-defined options (with commas after value.)
		URLParams = '?feed=voyeur';
		URLParams += '&author=' + '<?php if (isset($vwpOptions['voyeur_authors'])) echo $vwpOptions['voyeur_authors']; ?>';
		URLParams += '&cat=' + '<?php if (isset($vwpOptions['voyeur_categories'])) echo $vwpOptions['voyeur_categories']; ?>';
		URLParams += '&tag=' + '<?php if (isset($vwpOptions['voyeur_tags']) && $vwpOptions['voyeur_tags'] != '') echo $vwpOptions['voyeur_tags']; ?>';
		URLParams += '&day=' + '<?php if (isset($vwpOptions['voyeur_time_day'])) echo $vwpOptions['voyeur_time_day']; ?>';
		URLParams += '&monthnum=' + '<?php if (isset($vwpOptions['voyeur_time_month'])) echo $vwpOptions['voyeur_time_month']; ?>';
		URLParams += '&year=' + '<?php if (isset($vwpOptions['voyeur_time_year'])) echo $vwpOptions['voyeur_time_year']; ?>';
	}

	URLParams = rawurlencode(URLParams); // Encode params for Voyeur submission.
	var pageURL = '<?php echo $pageURL; ?>' + URLParams;
	var pageURLStrip = '<?php echo preg_replace('/[\W]/', '', $pageURL); ?>' + URLParams.replace(/[^a-zA-Z0-9]+/g, ''); //str.replace(/^[\s]+|[\s]+$/g, '');
	voyeurLogo.attr('style', 'display:none;'); // Hide the Voyeur logo when user chooses options.
	var fullVoyeurURL = 'http://voyeurtools.org/tool/' + voyeurTool + '/?inputFormat=RSS2&splitDocuments=true';
  if (removeFuncWords == '1') {
    fullVoyeurURL += '&stopList=stop.en.taporware.txt';
  }
  fullVoyeurURL += '&corpus=' + pageURLStrip + '&archive=' + pageURL;
	// Change the iFrame link to the custom URL for Voyeur, and remove the iFrame from being hidden.
	voyeurIframe.attr({
		// This is the URL to be sent to retrieve Voyeur information.
		src: fullVoyeurURL,
		width: "<?php if (isset($vwpOptions['voyeur_width'])) echo $vwpOptions['voyeur_width']; else echo '100'; ?>%",
		height: "<?php if (isset($vwpOptions['voyeur_height'])) echo $vwpOptions['voyeur_height']; else echo '250'; ?>"
	}).removeAttr('style'); // Remove 'display:none'.
	//viewSeparate.html('<small><a href="'+ fullVoyeurURL +'" target="_blank"><?php echo __('View in separate window.'); ?></a></small>');
} // end vwp_loadVoyeur()

/**
 * Listens for any clicks on any inputs in the Thickbox.
 *
 * If anything clicked, we update the box via AJAX, graying out options no longer applicable.
 *
 * @param object voyeurWindowAjax References jQuery where the AJAX window is. (Thickbox window.)
 * @param object ajaxRef References a jQuery AJAX instance.
 */
function vwp_optionsListener(voyeurWindowAjax, ajaxRef) { // Listens for user clicks within the Voyeur options window.
	voyeurWindowAjax.find(':checkbox').click(function() { // If any checkbox clicked, find new checkboxes / info.
		var authorValues = '&author=';
		var categoryValues = '&category=';
		// Create arrays to store clicked box data in.
		boxValues = []; // Stores actual value (Like 1 for author, 63 for category, etc.)
		boxNames = []; // Stores name/type of clicked box (Like author, category, etc.)

		voyeurWindowAjax.find(':checkbox:checked').each(function(index, value) {
			boxValues.push(value.value);
			boxNames.push(value.name);
		});

		for (var i=0; i < boxValues.length; i++) { // Construct strings to pass thru AJAX URL.
			if (boxNames[i] == 'author') {
				authorValues += boxValues[i] + ',';
			} else if (boxNames[i] == 'category') {
				categoryValues += boxValues[i] + ',';
			}
		}

		// If no boxes clicked, do not pass these into URL.
		if (authorValues == '&author=')
			authorValues = '';
		if (categoryValues == '&category=')
			categoryValues = '';
		
  // ===========================
  // ==   Update checkboxes   ==
  // ===========================	
		
		ajaxRef({
			type: 'POST',
			url: pluginURL + '/voyeurWP-ajax.php',
			data: 'action=loadVals' + authorValues + categoryValues,
			success: function(msg) { // Run the function that shows our dialog if any checkboxes checked.
				validBoxes = eval('(' + msg + ')'); // Create an object with all checkboxes that should NOT be grayed.
				voyeurWindowAjax.find(':checkbox').each(function(index, value) {
					if (value.name == 'author') {
						// Check if box value NOT in authors or if authors even exists.
						if ((validBoxes.authors && !(value.value in validBoxes.authors)) || !validBoxes.authors) {
							value.disabled = true;
							value.checked = '';
						} else if (value.disabled = true) {
							value.disabled = false;
						}
					} else if (value.name == 'category') {
						// Check if box value NOT in categories or if categories even exists.
						if ((validBoxes.categories && !(value.value in validBoxes.categories)) || !validBoxes.categories) {
							value.disabled = true;
							value.checked = '';
						} else if (value.disabled = true) {
							value.disabled = false;
						}
					}
				});
			}
		});
	});
} // end vwp_optionsListener()

/**
 * Encodes URLs exactly the same as PHP.
 *
 * @param string str The text/URL to be encoded.
 */
function rawurlencode (str) {
    // http://kevin.vanzonneveld.net
    str = (str+'').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
                                                                    replace(/\)/g, '%29').replace(/\*/g, '%2A');
}