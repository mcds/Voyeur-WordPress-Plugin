jQuery(document).ready(function($) {
  // Run vwp_updateOptions() initially to have proper options initially available
  vwp_updateOptions($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
  $('#primary-widget-area').find('#voyeur_tool').change(function() {
    vwp_updateOptions($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
  });
	
	// Run vwp_currentPageChangeListener() initially to have proper options displayed
	vwp_currentPageChangeListener($('#primary-widget-area'), $('#primary-widget-area').find('input[name="allow_current_page"]'));

  $('#primary-widget-area').find('input[name="allow_current_page"]').change(function() {
    vwp_currentPageChangeListener($('#primary-widget-area'), $('#primary-widget-area').find('input[name="allow_current_page"]'));
  });

  // If submit is clicked, refresh all the options.
  $('#primary-widget-area input[name="savewidget"]').ajaxSuccess(function() {
  	vwp_currentPageChangeListener($('#primary-widget-area'), $('#primary-widget-area').find('input[name="allow_current_page"]'));
  	vwp_currentPageChangeListenerAfterAjax($('#primary-widget-area'), $('#primary-widget-area').find('input[name="allow_current_page"]'));
    vwp_updateOptions($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
    vwp_toolChangeListener($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
  });
});

/**
 * Handles changing options available when a user changes the tool OR when admin area loaded.
 *
 * @param object widgetArea References jQuery widget area.
 * @param string currentTool The current tool that was just selected.
 */
function vwp_updateOptions(widgetArea, currentTool) {
  var fadeTime = 250;
  if (currentTool == 'Bubbles' || currentTool == 'Links' || currentTool == 'CorpusSummary') {
    widgetArea.find('#remove_func_words').fadeIn(fadeTime);
    widgetArea.find('#voyeur_limit').fadeOut(fadeTime);
    widgetArea.find('#voyeur_query').fadeOut(fadeTime);
  }
  if (currentTool == 'Cirrus') {
    widgetArea.find('#remove_func_words').fadeIn(fadeTime);
    widgetArea.find('#voyeur_limit').fadeIn(fadeTime);
    widgetArea.find('#voyeur_query').fadeOut(fadeTime);
  }
  if (currentTool == 'CorpusTypeFrequenciesGrid') {
    widgetArea.find('#remove_func_words').fadeIn(fadeTime);
    widgetArea.find('#voyeur_limit').fadeOut(fadeTime);
    widgetArea.find('#voyeur_query').fadeIn(fadeTime);
  }
  if (currentTool == 'Reader' || currentTool == 'WordCountFountain') {
    widgetArea.find('#remove_func_words').fadeOut(fadeTime);
    widgetArea.find('#voyeur_limit').fadeOut(fadeTime);
    widgetArea.find('#voyeur_query').fadeOut(fadeTime);
  }
}

/**
 * Listens for a user changing the value of the tool select box.
 *
 * @param object widgetArea References jQuery widget area.
 * @param string currentTool The current tool that was just selected.
 */
function vwp_toolChangeListener(widgetArea, currentTool) {
  widgetArea.find('#voyeur_tool').change(function() {
    vwp_updateOptions(widgetArea, widgetArea.find('#voyeur_tool').val());
  });
}

/**
 * Handles changing options available when a user clicks/unclicks the 'allow_current_page' checkbox
 *
 * @param object widgetArea References jQuery widget area.
 * @param string allowCurrentPage The checkbox with name 'allow_current_page'.
 */
function vwp_currentPageChangeListener(widgetArea, allowCurrentPage) {
		if(allowCurrentPage.is(':checked')) {
			widgetArea.find('#filter_settings').hide();
		} else {
			widgetArea.find('#filter_settings').show();
		}
}

/**
 * Listens for clicks on the 'allow_current_page' checkbox after an ajax update
 *
 * @param object widgetArea References jQuery widget area.
 * @param string allowCurrentPage The checkbox with name 'allow_current_page'.
 */
function vwp_currentPageChangeListenerAfterAjax(widgetArea, allowCurrentPage) {
		allowCurrentPage.click(function() {
			if(allowCurrentPage.is(':checked')) {
				widgetArea.find('#filter_settings').hide();
			} else {
				widgetArea.find('#filter_settings').show();
			}
		});
}