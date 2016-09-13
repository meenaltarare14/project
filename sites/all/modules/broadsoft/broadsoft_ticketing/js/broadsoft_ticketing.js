(function ($) {
    Drupal.behaviors.broadsoft_ticketing = {
        attach: function(context, setting) {
            if(typeof Drupal.settings.broadsoft_ticketing.reload_dropzone === 'undefined') {
                Drupal.settings.broadsoft_ticketing.reload_dropzone = true;
                Drupal.settings.broadsoft_ticketing.reload_edropzone = true;
            }
            if (Drupal.settings.broadsoft_ticketing.reload_dropzone ) {
                Drupal.settings.broadsoft_ticketing.reload_dropzone = false;
                var upload_path = Drupal.settings.broadsoft_ticketing.upload_path;
                var apreviewNode = $("#adropzone-preview .wrap-progress");
                var apreviewTemplate = apreviewNode.parent().html();
                $("#adropzone-preview .wrap-progress").remove();

                var MAX_FILES = 10;

                if ($('#adropzone').length) {
                    $('#adropzone').parent().removeClass('col-sm-6').addClass('col-sm-12');
                    var dropzone = new Dropzone(document.querySelector("#adropzone"), {
                        url: upload_path, // hook menu path passed from module
                        paramName: "files[dropzone]", // drupal looks here during file_save_upload
                        previewTemplate: apreviewTemplate,
                        previewsContainer: "#adropzone-preview",
                        acceptedFiles: '.jpg, .png, .log, .pdf, .doc, .txt',
                        clickable: '#adropzone .fileUpload',
                        maxFiles: MAX_FILES
                    });
                    dropzone.on("addedfile", function (file) {
                        if ($('#adropzone').parent().hasClass('col-sm-12')) {
                            $('#adropzone').parent().removeClass('col-sm-12').addClass('col-sm-6');
                        }
                    });
                    dropzone.on("success", function (file, response) {
                        try {
                            obj = JSON.parse(response);
                        }
                        catch (exc) {
                            $(file.previewTemplate).append('<span class="error-message">An error occurred during file upload!</span>');
                            throw exc;
                        }
                        if (obj.fcount > MAX_FILES) $(file.previewTemplate).append('<span class="error-message">You can not upload any more files.</span>');
                        else $(file.previewTemplate).append('<span class="fid element-invisible">' + obj.file + '</span>');
                        if (obj.file == null)
                            $(file.previewTemplate).append('<span class="error-message">An error occurred during file upload!</span>');
                    });
                    dropzone.on("removedfile", function (file) {
                        var server_file = $(file.previewTemplate).children('.fid').text();
                        // Do a post request and pass fid to delete the file
                        $.post("ticketing/ticket/upload", {delete_file: server_file}).done(function (response) {
                            obj = JSON.parse(response);
                            if (obj.fcount === 0) {
                                $('#adropzone').parent().removeClass('col-sm-6').addClass('col-sm-12');
                            }
                        });
                    });
                }
            }
                if (Drupal.settings.broadsoft_ticketing.reload_edropzone ) {
                    Drupal.settings.broadsoft_ticketing.reload_edropzone = false;
                    var epreviewNode = $("#edropzone-preview .wrap-progress");
                    var epreviewTemplate = epreviewNode.parent().html();
                    $("#edropzone-preview .wrap-progress").remove();
                if ($('#edropzone').length) {
                    $('#edropzone').parent().removeClass('col-sm-6').addClass('col-sm-12');
                    var dropzone = new Dropzone(document.querySelector("#edropzone"), {
                        url: upload_path, // hook menu path passed from module
                        paramName: "files[edropzone]", // drupal looks here during file_save_upload
                        previewTemplate: epreviewTemplate,
                        previewsContainer: "#edropzone-preview",
                        acceptedFiles: '.jpg, .png, .log, .pdf, .doc, .txt',
                        clickable: '#edropzone .fileUpload',
                        maxFiles: MAX_FILES
                    });
                    dropzone.on("addedfile", function (file) {
                        if ($('#edropzone').parent().hasClass('col-sm-12')) {
                            $('#edropzone').parent().removeClass('col-sm-12').addClass('col-sm-6');
                        }
                    });
                    dropzone.on("success", function (file, response) {
                        try {
                            obj = JSON.parse(response);
                        }
                        catch (exc) {
                            $(file.previewTemplate).append('<span class="error-message">An error occurred during file upload!</span>');
                            throw exc;
                        }
                        if (obj.fcount > MAX_FILES) $(file.previewTemplate).append('<span class="error-message">You can not upload any more files.</span>');
                        else $(file.previewTemplate).append('<span class="fid element-invisible">' + obj.file + '</span>');
                        if (obj.file == null)
                            $(file.previewTemplate).append('<span class="error-message">An error occurred during file upload!</span>');
                    });
                    dropzone.on("removedfile", function (file) {
                        var server_file = $(file.previewTemplate).children('.fid').text();
                        // Do a post request and pass fid to delete the file
                        $.post("ticketing/ticket/upload", {delete_file: server_file}).done(function (response) {
                            obj = JSON.parse(response);
                            if (obj.fcount === 0) {
                                $('#edropzone').parent().removeClass('col-sm-6').addClass('col-sm-12');
                            }
                        });
                    });
                }
            }

    // When grayed submit is clicked
    $('#submitAvailability .ticket__submit_disabled input[type=submit]').submit(function() {
        e.preventDefault();
        show_incomplete();
    });

    // Ticketing modal form submit button visibility
    $('.new-ticket-form select').bind('change', function() {
       if(form_complete()) {
           $('#submitAvailability .ticket__submit').removeClass("ticket__submit_disabled");
       }
        else if(!$('#submitAvailability .ticket__submit').hasClass('ticket__submit_disabled')) {
           $('#submitAvailability .ticket__submit').addClass('ticket__submit_disabled');
       }
    });
    $('.new-ticket-form input').on('change focus keyup', function() {
        if(form_complete()) {
            $('#submitAvailability .ticket__submit').removeClass("ticket__submit_disabled");
        }
        else if(!$('#submitAvailability .ticket__submit').hasClass('ticket__submit_disabled')) {
            $('#submitAvailability .ticket__submit').addClass('ticket__submit_disabled');
        }
    });

    // Add new product solution
    if($('#add-solution').length) {
        $('#add-product-solution').hide();
    }
    $('#add-solution').click(function() {
        $('#add-product-solution').show('fast');
        $(this).remove();
    });

    // Product information validate
    $('#add-product-solution .ticket__submit input').click(function(e) {
        e.preventDefault();
        if(!$('#info-solution  select').val()) {
            $('#info-solution  select').addClass('error');
        }
        if(!$('#info-product-main  select').val()) {
            $('#info-product-main  select').addClass('error');
        }
        if(!$('#info-version  select').val()) {
            $('#info-version  select').addClass('error');
        }
    });

    function form_complete() {
        if($('input.bsInputText').val() === "" ||
            $('textarea.bsTextarea').val() === "" ||
            !$('#product-version  select').val() ||
            $('#cus-name').val() === "" ||
            $('#cus-contact').val() === "") {
            return false;
        }
        else {
            return true;
        }
    }

    function show_incomplete() {
        if($('.modal-dialog .ticket input.bsInputText').val() === "") {
            $('.modal-dialog .ticket .bsInputTextWrapper_hasRemain').addClass('error');
        }
        if($('.modal-dialog .ticket textarea.bsTextarea').val() === "") {
            $('.modal-dialog .ticket .bsTextareaWrapper').addClass('error');
        }
        if(!$('#product-version  select').val()) {
            $('.product-infor-block2-dropdowngroup:first').addClass('error');
        }
        if(!$('#system-type  select').val()) {
            $('.product-infor-block2-dropdowngroup:last').addClass('error');
        }
        if($('#cus-name').val() === "") {
            $('#cus-name').addClass('error');
            $('#moreInfoAvailability > div > div:nth-child(3)').addClass('error');
        }
        if($('#cus-contact').val() === "") {
            $('#cus-contact').addClass('error');
            $('#moreInfoAvailability > div > div:nth-child(3)').addClass('error');
        }
    }
            // textbox wordcount
            var textMax = 255,
                textLength = 0,
                textRemaining = 0,
                bsInputText = $('.bsInputText'),
            //bsInputTextRemains = $('.bsInputTextRemains'),
                bsLabelTextRemains = $('.bsInputTextRemains');
            bsLabelTextRemains.html(textMax + ' left');

            function updateRemain() {
                textLength = bsInputText.val().length;
                textRemaining = textMax - textLength;
                bsLabelTextRemains.html(textRemaining + ' left');

                if (textLength > 0) {
                    //bsInputTextRemains.removeClass('hide');
                    $('html.js .bsInputTextWrapper input.form-autocomplete').attr('background-size', 'initial');
                } else {
                    //bsInputTextRemains.addClass('hide');
                    $('html.js .bsInputTextWrapper input.form-autocomplete').attr('background-size', '0px');
                }
                if (textRemaining < 0) {
                    bsLabelTextRemains.addClass('red');
                } else {
                    bsLabelTextRemains.removeClass('red');
                }
            }

            if(bsInputText.val()) {
                updateRemain();
            }
            bsInputText.keyup(function () {
                updateRemain();
            });

            var base_path = Drupal.settings.basePath;
            $( "#start-date" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 3,
                showOn: "button",
                buttonImage: base_path+"sites/all/themes/mantis/img/icon-calendar.png",
                buttonImageOnly: true,
                onClose: function( selectedDate ) {
                    $( "#to" ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( "#end-date" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 3,
                showOn: "button",
                buttonImage: base_path+"sites/all/themes/mantis/img/icon-calendar.png",
                buttonImageOnly: true,
                onClose: function( selectedDate ) {
                    $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                }
            });

        }
    };
    // outside behaviours
    Drupal.ajax.prototype.commands.autoselectSingles = function(ajax, response, status) {
        var coptions = 0;
        var voptions = 0;
        var soptions = 0;
        var poptions = 0;
        switch(ajax.callback) {
            case 'product_solution_select_callback':
                coptions = $('#component select option').length;
                voptions = $('#product-version select option').length;
                soptions = $('#system-type select option').length;
                break;
            case 'component_select_callback':
                voptions = $('#product-version select option').length;
                break;
            case 'product_version_select_callback':
                poptions = $('#platform select option').length;
                break;
        }
        if(coptions > 0 && coptions <= 2) {
            $('#component select').find('option:eq(1)').prop('selected', true);
        }
        else if(coptions > 0) {
            $('#component select').find('option:eq(0)').prop('selected', true);
        }
        if(voptions > 0 && voptions <= 2) {
            $('#product-version select').find('option:eq(1)').prop('selected', true);
        }
        else if(voptions > 0) {
            $('#product-version select').find('option:eq(0)').prop('selected', true);
        }
        if(soptions > 0 && soptions <= 2) {
            $('#system-type select').find('option:eq(1)').prop('selected', true);
        }
        if(poptions > 0 && poptions <= 2) {
            $('#platform select').find('option:eq(1)').prop('selected', true);
        }
    }

    Drupal.ajax.prototype.commands.resize_pcategory = function(ajax, response, status) {
        var left1 = $('#productInformation .col-sm-6 .product-infor-block2-dropdowngroup').first().outerHeight();
        var left2 = $('#productInformation .col-sm-6 .product-infor-block2-dropdowngroup').last().outerHeight();
        $('#productInformation .col-sm-6 select').last().height(left1 + left2 - 10);
    }

    Drupal.ajax.prototype.commands.reset_product_main = function(ajax, response, status) {
        $('#info-product-main select').find('option:eq(0)').prop('selected', true);
    }

    Drupal.ajax.prototype.commands.product_info_ss = function(ajax, response, status) {
        var coptions = 0;
        var voptions = 0;
        switch(ajax.callback) {
            case 'info_product_solution_select_callback':
                coptions = $('#info-component select option').length;
                voptions = $('#info-version select option').length;
                break;
            case 'info_component_select_callback':
                voptions = $('#info-version select option').length;
                break;
        }
        if(coptions > 0 && coptions <= 2) {
            $('#info-component select').find('option:eq(1)').prop('selected', true);
        }
        else if(coptions > 0) {
            $('#info-component select').find('option:eq(0)').prop('selected', true);
        }
        if(voptions > 0 && voptions <= 2) {
            $('#info-version select').find('option:eq(1)').prop('selected', true);
        }
        else if(voptions > 0) {
            $('#info-version select').find('option:eq(0)').prop('selected', true);
        }
    }

    Drupal.ajax.prototype.commands.redirectUsers = function(ajax, response) {
        window.location.replace(response.path);
    }
}(jQuery));