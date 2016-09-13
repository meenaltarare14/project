/**
 * @file
 * JavaScript functions of Views Refresh module.
 */

(function ($) {
  'use strict';

  function attachEvents(view, dom_id, settings, page) {
    var selector = '.view-dom-id-' + dom_id;
    // Retrieve the path to use for views' ajax.
    var ajax_path = Drupal.settings.views.ajax_path + '/views-refresh';

    // If there are multiple views this might've ended up showing up multiple times.
    if (ajax_path.constructor.toString().indexOf("Array") !== -1) {
      ajax_path = ajax_path[0];
    }

    // Check if there are any GET parameters to send to views.
    var queryString = window.location.search || '';
    if (queryString !== '') {
      // Remove the question mark and Drupal path component if any.
      queryString = queryString.slice(1).replace(/q=[^&]+&?|&?render=[^&]+/, '');
      if (queryString !== '') {
        // If there is a '?' in ajax_path, clean url are on and & should be used to add parameters.
        queryString = ((/\?/.test(ajax_path)) ? '&' : '?') + queryString;
      }
    }

    var pageOptions = $.extend({}, settings, (page ? {page: page} : {}));
    var noScrollOptions = $.extend({}, pageOptions, {views_refresh_noscroll: 1});
    var pageFirstOptions = $.extend({}, settings, {});
    var noScrollFirstOptions = $.extend({}, pageFirstOptions, {views_refresh_noscroll: 1});

    var ajaxSettings = {
      url: ajax_path + queryString,
      setClick: true,
      selector: selector,
      progress: { type: 'throbber' }
    };

    var refreshAjax = new Drupal.ajax(selector, view, $.extend({}, ajaxSettings, {submit: noScrollOptions, event: 'views_refresh'}));
    var refreshScrollAjax = new Drupal.ajax(selector, view, $.extend({}, ajaxSettings, {submit: pageOptions, event: 'views_refresh_scroll'}));
    var refreshFirstAjax = new Drupal.ajax(selector, view, $.extend({}, ajaxSettings, {submit: noScrollFirstOptions, event: 'views_refresh_first'}));
    var refreshScrollFirstAjax = new Drupal.ajax(selector, view, $.extend({}, ajaxSettings, {submit: pageFirstOptions, event: 'views_refresh_scroll_first'}));
  }

  Drupal.ajax.prototype.commands.viewsRefresh = function (ajax, response, status) {
    var view = $(response.selector);
    var event = response.first ? (response.scroll ? 'views_refresh_scroll_first' : 'views_refresh_first') : (response.scroll ? 'views_refresh_scroll' : 'views_refresh');
    view.trigger(event);
  };

  Drupal.behaviors.viewsRefresh = {
    attach: function (context) {
      if (Drupal.settings && Drupal.settings.viewsRefresh && Drupal.settings.views && Drupal.settings.views.ajaxViews) {
        $.each(Drupal.settings.viewsRefresh, function (dom_id, page) {
          var view = $('.view-dom-id-' + dom_id).once('views-refresh-processed');
          if ((view.length > 0) && (Drupal.settings.views.ajaxViews['views_dom_id:' + dom_id])) {
            attachEvents(view, dom_id, Drupal.settings.views.ajaxViews['views_dom_id:' + dom_id], page);
          }
        });
      }
    }
  };

})(jQuery);
