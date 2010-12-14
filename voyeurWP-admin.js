jQuery(document).ready(function($) {
  // Run vwp_updateOptions initially to have proper options initially available.
  vwp_updateOptions($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
  $('#primary-widget-area').find('#voyeur_tool').change(function() {
    vwp_updateOptions($('#primary-widget-area'), $('#primary-widget-area').find('#voyeur_tool').val());
  });
  // If submit is clicked, refresh all the options.
  $('#primary-widget-area input[name="savewidget"]').ajaxSuccess(function() {
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

function vwp_toolChangeListener(widgetArea, currentTool) {
  widgetArea.find('#voyeur_tool').change(function() {
    vwp_updateOptions(widgetArea, widgetArea.find('#voyeur_tool').val());
  });
}