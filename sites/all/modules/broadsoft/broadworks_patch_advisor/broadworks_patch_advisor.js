(function ($) {
$(document).ready(function () {

    /** FUNCTIONS **/

    /**
     * Function used to arm the checkbox click event and the row selected event.
     **/
    function armCheckboxesAndRows() {
        // Automatically update the dependencies.
        $(':checkbox.vbo-select').click(function (event, fromSelectAll) {
            // Handle the check/uncheck behavior of a checkbox. Few scenarios to take
            // care of... selectall,selectnone,with or without dependencies already 
            // selected or not...
            var impactedSpanName = '.missing-' + this.id;
            if (this.checked) {
                if (fromSelectAll) {
                    if (hasSelectedDependencies(this.id, false)) {
                        this.disabled = true;
                    }
                }
                $(impactedSpanName).html('<font color="green">selected</font>');
            } else {
                if (!fromSelectAll) {
                    if (hasSelectedDependencies(this.id, true)) {
                        return false;
                    }
                } else {
                    this.disabled = false;
                }
                $(impactedSpanName).html('<font color="red">unselected</font>');
            }

            // Capture/Remove the patch in/from the selected patch list work around
            updateSelectedPatch(this.value, this.checked);

            // Recursively handle the actual dependencies enable/disable capability.
            if (!fromSelectAll) {
                if (this.checked) {
                    $('.aMissinPatch', $(this).parent().parent().parent().parent()).each(function () {
                        var missingPatchName = $($(this).attr('class').split(' '))[1].replace('missing-', '');
                        var checkboxObj = $(':checkbox.vbo-select#' + missingPatchName)[0];
                        if (!checkboxObj.checked) {
                            checkboxObj.checked = false;
                            $(checkboxObj).trigger('click', [false]);
                            checkboxObj.checked = true;
                        }
                        checkboxObj.disabled = true;
                    });
                } else {
                    $('.aMissinPatch', $(this).parent().parent().parent().parent()).each(function () {
                        var missingPatchName = $($(this).attr('class').split(' '))[1].replace('missing-', '');
                        if (!hasSelectedDependencies(missingPatchName, true)) {
                            var checkboxObj = $(':checkbox.vbo-select#' + missingPatchName)[0];
                            checkboxObj.disabled = false;
                        }
                    });
                }
            }
        });

        // Set up the ability to click anywhere on the row to select it.
        $('tr.rowclick').click(function (event) {
            if (event.target.nodeName.toLowerCase() != 'input' && event.target.nodeName.toLowerCase() != 'a') {
                $(':checkbox.vbo-select', this).each(function () {
                    var checked = this.checked;
                    if ((checked && hasSelectedDependencies(this.id, true)) || this.disabled) {
                        return false;
                    }
                    this.checked = !checked;
                    $(this).trigger('click', [false]);
                    this.checked = checked;
                });
            }
        });
    }

    /**
     * Function used to reset all checkbox, ON or OFF.
     **/
    function setSelectAll() {
        $(':checkbox.vbo-select').each(function () {
            var checked = ($("select.pa-select-all").val() == 0);
            this.checked = !checked;
            $(this).trigger('click', [true]);
            this.checked = checked;
        });
    }

    /**
     * Function used to identify if a given patch has a dependency or not.
     * It returns false if no dependencies at all.
     * In case there are dependencies, two options, when reportCheckedOnly==true
     * it returns true as soon as there is one dependency.
     * When reportCheckedOnly==false, it returns true if there is at least one
     * dependency and checked.
     **/
    function hasSelectedDependencies(patchName, reportCheckedOnly) {
        var impactedSpanName = '.missing-' + patchName;
        var rowArray = $('tr.rowclick');
        for (var i = 0; i < rowArray.length; i++) {
            var currentElement = rowArray[i];
            if ($(impactedSpanName, currentElement).length) {
                var res = $(currentElement).find(':checkbox.vbo-select');
                if (reportCheckedOnly) {
                    if ($(res[0]).checked) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Function used to track the "real" list of selected patches, including both
     * the checkbox that are enabled and disabled. The default behavior of html is
     * not to send the disabled checkboxes in the POST even if selected. We use
     * an hidden field in the form to work around the problem.
     * The hidden field is a string with patches separated by semicolumns.
     **/
    function updateSelectedPatch(id, checked) {
        var patchList = $("input[name='selectedPatches']").val().split(";");
        var result = "";
        for (var i = 0; i < patchList.length; i++) {
            if (patchList[i] != "") {
                if (patchList[i] == id) {
                    if (checked) {
                        return;
                    }
                } else {
                    result = result + ";" + patchList[i];
                }
            }
        }
        if (checked) {
            result = result + ";" + id;
        }
        $("input[name='selectedPatches']").val(result)
    }

    /** INITIALIZATION **/

    // Set up the table for select-all functionality
    $('th.select-all').click(function () {
        setSelectAll();
    });


    // Apply the checkbox rules at initial load time
    armCheckboxesAndRows();

    // The following arms and define the row ordering function to the 
    // table of missing patches.
    $('a.link-sort-table').click(function (event) {
        var $table = $('.sort-table');
        var rows = $('.rowclick', $table).get();

        var $ascOrder = $(this).hasClass('asc');
        if ($ascOrder) {
            $(this).removeClass('asc');
        } else {
            $(this).addClass('asc');
        }

        var classList = $(this).attr('class').split(/\s+/);
        var columnClass = "sort-";
        for (var a = 0, b = classList.length; a < b; a++) {
            if (classList[a].indexOf('sort-column-') != -1) {
                columnClass = classList[a].substring('sort-column-'.length);
            }
        }


        var subElements = [];
        for (var a = 0, b = rows.length; a < b; a++) {
            var $toto = $('.' + columnClass, $(rows[a]));
            subElements[a] = {value: $toto.text(), pos: a, asc: ($ascOrder ? false : true)}
        }

        subElements.sort(function (a, b) {
            var nameA = a.value.toLowerCase().replace(/^\s+/, "");
            var nameB = b.value.toLowerCase().replace(/^\s+/, "");
            if (nameA < nameB)
                return (a.asc ? -1 : 1);
            if (nameA > nameB)
                return (a.asc ? 1 : -1);
            return 0 //default return value (no sorting)
        });

        var $tbody = ('tbody', $table);

        for (var a = 0, b = subElements.length; a < b; a++) {
            var rowElem = rows[subElements[a].pos];
            $(rowElem).remove();
            $tbody.append($(rowElem));
            $(rowElem).addClass(a % 2 == 0 ? "odd" : "even");
            $(rowElem).removeClass(a % 2 != 0 ? "odd" : "even");
        }

        armCheckboxesAndRows();
    });

    setSelectAll();

    // updates
    if(typeof Drupal.settings.broadworks_patch_advisor !== 'undefined') {
        var upload_path = Drupal.settings.broadworks_patch_advisor.upload_path;
        var apreviewNode = $("#pdropzone-preview .wrap-progress");
        var apreviewTemplate = apreviewNode.parent().html();
        $("#pdropzone-preview .wrap-progress").remove();

        var MAX_FILES = 1;

        if ($('#pdropzone').length) {
            $('#pdropzone').parent().removeClass('col-sm-6').addClass('col-sm-12');
            var dropzone = new Dropzone(document.querySelector("#pdropzone"), {
                url: upload_path, // hook menu path passed from module
                paramName: "filename",
                previewTemplate: apreviewTemplate,
                previewsContainer: "#pdropzone-preview",
                acceptedFiles: '',
                clickable: '#pdropzone .fileUpload',
                maxFiles: MAX_FILES,
                //autoProcessQueue: false,
                uploadMultiple: false,
                parallelUploads: 1
            });
            dropzone.on("addedfile", function (file) {
                if ($('#pdropzone').parent().hasClass('col-sm-12')) {
                    $('#pdropzone').parent().removeClass('col-sm-12').addClass('col-sm-6');
                }
            });
            dropzone.on("success", function (file, response) {
                if (response == 'success')
                    location.reload();
                else
                    $(file.previewTemplate).append('<span class="error-message">' + response + '</span>');
            });
            $('input.upload-btn').on("click", function (e) {
                // Make sure that the form isn't actually being sent.
                e.preventDefault();
                e.stopPropagation();
                dropzone.processQueue();
            });
        }
    }

});

}(jQuery));
