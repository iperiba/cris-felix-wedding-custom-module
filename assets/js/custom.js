(function($) {
    $(document).ready(function() {
        $("#guest-email-select-all-button").click(function(){
            $(".guest-email-input").each(function() {
                $(this).attr( 'checked', true )
            });
        });

        $("#guest-email-deselect-all-button").click(function(){
            $(".guest-email-input").each(function() {
                $(this).attr( 'checked', false )
            });
        });
    })
})(jQuery);
