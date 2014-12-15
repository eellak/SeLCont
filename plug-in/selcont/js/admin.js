(function($) {
    "use strict";

    $(function() {

        if( 0 < $('#pres_file_meta_box').length ) {
            $('form').attr('enctype', 'multipart/form-data');
        } // end if

    });
}(jQuery));