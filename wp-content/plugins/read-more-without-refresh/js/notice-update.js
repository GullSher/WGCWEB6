jQuery(document).on( 'click', '.rmwr-notice .notice-dismiss', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'dismiss_rmwr_notice'
        }
    })

})