(function ( $ ) {
    $(document).ready(function() {
        $( "#search-auto" ).autocomplete({
            minLength: 6,
            source: '/broadsoft_search/autocomplete',
            focus: function( event, ui ) {
                $( "#search-auto" ).val( ui.item.title );
                return false;
            }
        }).data( "autocomplete" )._renderItem = function( ul, item ) {
            var list =  $( "<li></li>" )
                .data("item.autocomplete", item)
                .append("<div class='heading'><h4 class='title'><a href='"+item.url+"'>" + item.title + "</a></h4></div>")
                .append("<p>" + item.text + "</p>")
                .append("<p><a href='"+item.url+"' class='topic'>" + item.file + "</a></p>")
                .appendTo(".suggestion-list .inner .list-contents");
            $(ul).remove();
            $('.suggestion-list').removeClass('hide');
            return list;
        };

        $("#search-auto").on("paste keyup", function() {
            $('.suggestion-list').addClass('hide');
            $('.suggestion-list .inner .list-contents').empty();
        });

        $(document).mouseup(function (e)
        {
            var container = $("#broadsoft-files-search-form");

            if (!container.is(e.target) // if the target of the click isn't the container...
                && container.has(e.target).length === 0) // ... nor a descendant of the container
            {
                $('.suggestion-list').addClass('hide');
                $('.suggestion-list .inner .list-contents').empty();
            }
        });

    });
}( jQuery ));