//drupal 6 attach
Drupal.behaviors.broadsoft_ticketing_pagination_throbbe =  function(context) {
  $(document).bind("ajaxStart", function() {
    var throbbe = $('.views-throbbing').clone();
	$('.views-throbbing').remove();
	$("#display_results").append(throbbe);
  });
};

//drupal 7 attach
(function ($) {
  $(document).ajaxSend(function( event, request, settings ) {
    var throbbe = $('.ajax-progress-throbber').clone().wrap('<p>').parent().html();
	$('.ajax-progress-throbber').remove();
	$("#display_results").append(throbbe);
  });
})(jQuery);