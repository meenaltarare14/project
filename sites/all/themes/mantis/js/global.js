/*!
 * Custom css for Pleezpay admin Version:1.1
 */
(function ( $ ) {
$(document).ready(function() {
    $(window).load(function() {
        
        $("[data-toggle=tooltip]").tooltip();
        
        $('.actions-block').popover({
            placement: 'bottom',
            container: 'body',
            html: 'true',
            content: '<div class="popover-actions"><ul><li><a href="#" title="" class="btn btn-orange">Training</a></li><li><a href="#" title="" class="btn btn-orange">Carrier</a></li><li><a href="#" title="" class="btn btn-orange">Call Center</a></li></ul></div>',
            trigger: 'manual',
        }).click(function(e) {
            e.preventDefault();
            var popover = $(".popover-content");
            $(this).popover((popover.length != 0) ? 'hide' : 'show');

            popover = $(".popover-content");
            if (popover && popover.parent() && popover.parent().parent()) {
                popover = popover.parent().parent();
            }

            var left = e.pageX;
            var top = e.pageY;
            var height = popover.height();
            var width = popover.width();

            popover.css({
                top: top - height,
                left: left - width / 2 + 'px'
            });
        });

        var $contextDropdown = $('#context-dropdown');
        var $contextToggle = $contextDropdown.find('.js-toggle');
        var showContextDropdown = false;

        $contextToggle.bind('click', function (e) {
            e.stopPropagation();

            if (showContextDropdown) {
                
                showContextDropdown = false;
                $contextDropdown.removeClass('showing');

            } else {

                showContextDropdown = true;
                $contextDropdown.addClass('showing');

            }

        });

        $(document).mouseup(function (e) {
            if (!$contextDropdown.is(e.target) && $contextDropdown.has(e.target).length === 0) {
                showContextDropdown = false;
                $contextDropdown.removeClass('showing');
            }
        });

        // ADD SLIDEDOWN ANIMATION TO DROPDOWN //
        $('.current-sub-nav .dropdown').on('show.bs.dropdown', function(e){
            $(this).find('.dropdown-menu').first().stop(true, true).slideDown();
        });

        // ADD SLIDEUP ANIMATION TO DROPDOWN //
        $('.current-sub-nav .dropdown').on('hide.bs.dropdown', function(e){
            $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
        });

        $(".bsTab_item").click(function(){
            $(".bsTab_item").removeClass('bsTab_actived');
            $(this).addClass('bsTab_actived');

            $(".bsBox").hide();
            $("#" + $(this).attr('for')).show();
        });

        $("#priorityAvailability .bsTab").click(function(){
            $("#priorityAvailability .bsTab").removeClass('bsTab_actived');
            $(this).addClass('bsTab_actived');
        });
    
    });

    $(".filter-block .main-option > h4").click(function(e) {
        $(this).next("ul").slideToggle(1000);
        $(this).parent().toggleClass("open");
        $(".filter-block .main-option > h4.active").removeClass("active");
        $(this).addClass("active");
        e.preventdefault();
        e.stopPropagation();
    });

    $('body').on('click', function(e) {
        $('[data-toggle="popover"]').each(function() {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    $(".rating-group i").click(function(e) {
        $(".rating-group i").removeClass("isActive");
        $(this).addClass("isActive");

        e.preventdefault();
        e.stopPropagation();
    });

    $('.upcoming-events').css('height', $('.recently-viewed').css('height') );

    $(".btn-ticket-help").popover({
        placement: 'right',
        html: true,
        content: '<div class="popover-help" role="tooltip">' +
                    '<ul>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-critical"></i>'+ 
                                '<span>Critical</span>' +
                            '</span>' +
                            '<p>Critical call outage, if not an outage please use "Major".</p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-major"></i>'+ 
                                '<span>Major</span>' +
                            '</span>' +
                            '<p>High impact issue or problem that greatly impacts the customer.</p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-minor"></i>'+ 
                                '<span>Minor</span>' +
                            '</span>' +
                            '<p>High impact issue or problem that greatly impacts the customer.</p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-information"></i>'+ 
                                '<span>Informational</span>' +
                            '</span>' +
                            '<p>Trivial issue or request for more information. May be faster to ask the community.</p>' +
                        '</li>' +
                    '</ul>' +
                    '</div>'
    });



    $(".btn-priority-popover").popover({
        placement: 'right',
        html: true,
        content: '<div class="popover-help" role="tooltip">' +
                    '<ul>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-urgent"></i>'+ 
                                '<span>Urgent</span>' +
                            '</span>' +
                            '<p>Immediate response or solution is absolutely needed.</p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-high"></i>'+ 
                                '<span>High</span>' +
                            '</span>' +
                            '<p>Fast response is requested.</p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-normal"></i>'+ 
                                '<span>Normal</span>' +
                            '</span>' +
                            '<p>Regular response time, recommended for most issues. </p>' +
                        '</li>' +
                        '<li>' +
                            '<span class="title">' +
                                '<i class="bsIcon bsIcon-low"></i>'+ 
                                '<span>Low</span>' +
                            '</span>' +
                            '<p>Not time sensitive for issues that can wait.</p>' +
                        '</li>' +
                    '</ul>' +
                    '</div>'
    });

    // add hover text for menu links
    $('.current-sub-nav a.col-md-1').each(function() {
        $(this).attr('data-hover', $(this).find('img').attr('alt'))
    });

    $('#preferences .form-radios input[type=radio]').change(function() {
        $('.preferences.ticket__submit').removeClass('ticket__submit_disabled');
    });

});
}( jQuery ));
(function ( $ ) {
    $(document).ready(function() {

        jQuery('#contentRating').on('shown.bs.modal', function() {
            removeProductInforSelect();
        });

        function removeProductInforSelect() {
            var tagRemove = jQuery('.ticket__tag-remove'),
                ticketTag, that;
            tagRemove.on('click', function() {
                that = jQuery(this);
                ticketTag = that.closest('.ticket__tag');
                if (ticketTag) {
                    ticketTag.remove();
                }
            });
        }

        $('.show-latest .show').on('click', function(e) {
            e.preventDefault();
            if($('.show-latest .news').hasClass('hidden')) {
                $(this).text('Show Less');
                $('.show-latest .news').removeClass('hidden');
                $('.show-latest .news').show("fast");
            }
            else {
                $(this).text('Show All');
                $('.show-latest .news').addClass('hidden');
                $('.show-latest .news').hide();
                $('.show-latest .news').first().removeClass('hidden');
                $('.show-latest .news').first().show();
            }
        });

        // Make ticketing table rows clickable
        $('.pane-ticketing-tabs table tr').click(function() {
            window.document.location = $(this).find('td a').attr("href");
        });

        var filter = {
            filterbar: $('.filterbar'),
            button: $('.filterbar__button'),
            applyFilter: $('.filterbar__action .apply'),
            expand: $('.filterbar__expand'),

            init: function() {
                var that = this;
                that.button = $('.filterbar__button');
            },

            open: function() {
                var that = this;
                that.button.on('click', function() {
                    that.button.toggleClass('open');
                    that.expand.toggleClass('open');
                });

            },
            apply: function () {
                var result, that = this,
                    _html;

                that.applyFilter.on('click', function(evt) {
                    result = [$("input[name='chkStatus']:checked").val(), $("input[name='chkSeverity']:checked").val(), $("input[name='chkProduct']:checked").val()].join(' + ');
                    if (result) {
                        _html = $('<div class="filterbar__result"></div>');
                        _html.html(result);
                        that.filterbar.append(_html);
                        that.button.addClass('hide');
                        that.expand.addClass('hide');
                    }
                });
            }
        };

        filter.open();
        filter.apply();

        var ss = {
            id: $('#solution-dropdown'),
            clickEl: null,
            contentEl: null,

            init: function() {
                var that = this;
                that.clickEl = that.id.find('.title');
                that.contentEl = that.id.find('.wrap-content');
            },

            toogle: function() {
                var that = this;
                that.clickEl.on('click', function(evt) {
                    that.contentEl.slideToggle('slow');
                    that.id.toggleClass('open');
                });
            }
        };

        ss.init();
        ss.toogle();

        $(".dropdown-menu li a").click(function(){
            $(this).parents(".dropdown").find('.btn').html($(this).text() + ' <span class="caret"></span>');
            $(this).parents(".dropdown").find('.btn').val($(this).data('value'));
        });

        Drupal.ajax.prototype.commands.reinitProductInfo = function(ajax, response, status) {
            ss.init();
            ss.toogle();
        }

        Drupal.ajax.prototype.commands.reinitTicketFilter = function(ajax, response, status) {
            filter.init();
            filter.open();
        }

        // loading animation
        // Drupal's core beforeSend function
        var beforeSend = Drupal.ajax.prototype.beforeSend;
        var success = Drupal.ajax.prototype.success;
        // Add a trigger when beforeSend fires.
        Drupal.ajax.prototype.beforeSend = function(xmlhttprequest, options) {
            beforeSend.call(this, xmlhttprequest, options);
            $(document).trigger('beforeSend', this);
        }
        Drupal.ajax.prototype.success = function(xmlhttprequest, options) {
            success.call(this, xmlhttprequest, options);
            $(document).trigger('success');
        }
        $(document).bind('beforeSend', function(e, element) {
            if(element.callback == "ticket_search_results_callback") {
                $('#ticket-search-form .load-ov').removeClass('hide');
                $('.no-results').remove();
            }
            else if(element.callback == "new_ticket_callback") {
                $('<div class="loadinga"><div class="load-img"></div></div>"').hide().insertAfter(element.selector).fadeIn("medium");
            }
            else {
                $('<div class="loadinga"><div class="load-img"></div></div>"').hide().prependTo('body').fadeIn("medium");
            }
        });
        $(document).bind('success', function() {
            $('.loadinga').fadeOut(200, function() {
                $(this).remove();
            });
        });

        /**
         * overridden from autocomplete.js to disable selecting item on click
         */
        if(typeof Drupal.jsAC !== "undefined") {
            Drupal.jsAC.prototype.hidePopup = function (keycode) {
                var classes = this.input.classList;
                if (jQuery.inArray("form-control", classes) === -1) {
                    if (this.selected && ((keycode && keycode != 46 && keycode != 8 && keycode != 27) || !keycode)) {
                        this.select(this.selected);
                    }
                }
                // Hide popup.
                var popup = this.popup;
                if (popup) {
                    this.popup = null;
                    $(popup).fadeOut('fast', function () {
                        $(popup).remove();
                    });
                }
                this.selected = false;
                $(this.ariaLive).empty();
            };
        }

    });

}( jQuery ));

// (function($) {
//   $(document).ready(function() {
//     var mainContent = $('.main');
//     var contentLeft = mainContent.find('.contents-left');
//     var contentRight = mainContent.find('.contents-right');
//     var heightContentLeft = contentLeft.outerHeight();
//     var heightContentRight = contentRight.outerHeight();
//     var maxHeight = heightContentRight;
//     console.log(heightContentRight);
//     if(maxHeight <= heightContentLeft){
//       maxHeight = heightContentLeft;
//       console.log(1);
//     }else{
//       maxHeight = maxHeight;
//       console.log(maxHeight);
//     }
//     mainContent.css({
//       'height': maxHeight
//     });
//   });
// })(jQuery);
