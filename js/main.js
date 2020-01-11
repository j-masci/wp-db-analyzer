
(function($){

    /**
     * initialize the ajax for the reports.
     */
    function report_ajax_init(){

        // gets updated report html from the server.
        // note: the element here is not a form tag.
        $('body').on('wpdba.get_report', '.wpdba-report', function(e){

            var ele = $(this);
            var name = ele.attr('data-name' ) || 'unnamed';

            console.log(ele.find('[name]' ) );
            console.log(ele.find('[name]' ) ).serialize();
            console.log(ele.find('[name]' ) ).serializeArray();

            $.ajax({
                url: ele.attr('data-action'),
                type: ele.attr('data-method') || 'POST',
                dataType: 'json',
                data: ele.find('[name]').serializeArray(),
                beforeSend: function(xhr, settings){
                    ele.addClass('ajax-loading');
                    console.log(settings);
                },
                error: function(xhr, status, error){
                    ele.removeClass('ajax-loading');
                    console.error("ajax error: " + name, xhr, status, error );
                    alert( "Unexpected error in \"" + name + "\" report." );
                },
                success: function(response, status, xhr){

                    ele.removeClass('ajax-loading');

                    // todo: execute possible callback function from server
                    if ( response.success ) {
                        ele.empty().append(response.html);
                    } else {
                        // todo: have to add controls to re-build the report outside of the report itself otherwise this is no good.
                        form.prepend( "<p>Unexpected error trying to load new data for this report.</p>" );
                    }
                },
            });
        });
    }

    $(document).ready(function(){
        report_ajax_init();
    });

})(jQuery);