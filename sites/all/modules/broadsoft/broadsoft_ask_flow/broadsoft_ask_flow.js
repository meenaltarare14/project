(function ( $ ) {
    $(document).ready(function() {
        Drupal.ajax.prototype.commands.showPopup = function(ajax, response, status) {
            showModal(response.name);
        }
        Drupal.ajax.prototype.commands.hidePopup = function(ajax, response, status) {
            if(typeof(response.time) !== 'undefined') {
                setTimeout(function() {hideModal(response.name);}, response.time);
            }
            else
                hideModal(response.name);
        }

        var hideInProgress = false;
        var showModalId = "";

        function showModal(elementId) {
            if (hideInProgress) {
                showModalId = elementId;
            } else {
                $("#" + elementId).modal("show");
            }
        };

        function hideModal(elementId) {
            hideInProgress = true;
            $("#" + elementId).on('hidden.bs.modal', hideCompleted);
            $("#" + elementId).modal("hide");

            function hideCompleted() {
                hideInProgress = false;
                if (showModalId) {
                    showModal(showModalId);
                }
                showModalId = '';
                $("#" + elementId).off('hidden.bs.modal');
            }
        };
    });

    // Get feedback before closing
}( jQuery ));
