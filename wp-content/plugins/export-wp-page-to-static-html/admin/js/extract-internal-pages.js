
$(document).on("click", ".export_internal_page_to_html", function(e){
    e.preventDefault();

    ClearExportLogsData();
    StopInterval('intrvar');

    var page_list = $('#tabs-1 .select_pages_to_export .pages_list .single_page');
    var replace_urls = $('#tabs-1 #replace_all_url').is(':checked') ? true : false;
    var receive_email = $('#tabs-1 #email_notification').is(':checked') ? true : false;
    var email_lists = $('#tabs-1 #receive_notification_email').val();

    var skip_assets = $('#tabs-1 #skip_assets').is(':checked') ? true : false;
    var skip_stylesheets = $('#tabs-1 #skip_stylesheets').is(':checked') ? true : false;
    var skip_scripts = $('#tabs-1 #skip_scripts').is(':checked') ? true : false;
    var skip_images = $('#tabs-1 #skip_images').is(':checked') ? true : false;
    var skip_videos = $('#tabs-1 #skip_videos').is(':checked') ? true : false;
    var skip_audios = $('#tabs-1 #skip_audios').is(':checked') ? true : false;
    var skip_docs = $('#tabs-1 #skip_docs').is(':checked') ? true : false;

    var pages = $('#export_pages').val();
    if (pages.length > 0) {

        $('.logs_list').html('');
        $('.progress').removeClass('completed');
        $('.see_logs_in_details').show();
        $('.htmlExportLogs').show();

        $(this).find('.spinner_x').removeClass('hide_spin');
        $('.download-btn').addClass('hide');
        $('.export_failed.error').hide();
        $('.cancel_rc_html_export_process').show()
        $('#cancel_ftp_process').val('false');

        $(this).attr('disabled', 'disabled');


        var ftp = 'no';
        var path = '';

        var full_site = 'no';
        if ($('#full_site').is(":checked")) {
            full_site = 'yes';
        }

        var skip_assets_data = {};
        if(skip_assets){
            if(skip_stylesheets){
                skip_assets_data['stylesheets'] = true;
            }
            if(skip_scripts){
                skip_assets_data['scripts'] = true;
            }
            if(skip_images){
                skip_assets_data['images'] = true;
            }
            if(skip_videos){
                skip_assets_data['videos'] = true;
            }
            if(skip_videos){
                skip_assets_data['audios'] = true;
            }
            if(skip_docs){
                skip_assets_data['docs'] = true;
            }
        }

        var datas = {
            'action': 'rc_export_wp_page_to_static_html',
            'rc_nonce': rcewpp.nonce,
            'pages': pages,
            'replace_urls': replace_urls,
            'skip_assets': skip_assets_data,
            'full_site': full_site,
            'ftp': ftp,
            'path': path,
            'receive_email': receive_email,
            'email_lists': email_lists,
            'time': Date.now()
        };


        $.ajax({
            url: rcewpp.ajax_url,
            data: datas,
            type: 'post',
            //async: false,
            dataType: 'json',
            cache: false,

            beforeSend: function(){

            },
            success: function(r){
                if(r.success == 'true'){
                    setTimeout(function(){
                        get_export_log_percentage(1000);
                    }, 2000);

                } else {
                    console.log('Something went wrong, please try again!');
                }

            }, error: function(){

            }
        });
    }
    else{
        alert('Please select a page');
    }
});

jQuery(document).ready(function($) {
    // Handle "Done!" button click
    $(document).on('click', '#submit-review-done', function(e) {
        e.preventDefault();

        // Perform AJAX request
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'ewpptsh_submit_review',
                rc_nonce: rcewpp.nonce,
                type: 'done'
            },
            success: function(response) {
                // Handle the success response
                $('.export-html-review-notice').remove();
                console.log('Review submitted successfully.');
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log('Error submitting review:', error);
            }
        });
    });

    // Handle "Remind me later" button click
    $(document).on('click', '#submit-remind-later', function(e) {
        e.preventDefault();

        // Perform AJAX request
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'ewpptsh_submit_review',
                rc_nonce: rcewpp.nonce,
                type: 'remind_later'
            },
            success: function(response) {
                // Handle the success response
                $('.export-html-review-notice').remove();
                console.log('Remind me later request submitted successfully.');
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log('Error submitting remind me later request:', error);
            }
        });
    });

    // Handle "Hide" button click
    $(document).on('click', '#submit-hide', function(e) {
        e.preventDefault();

        // Perform AJAX request
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'ewpptsh_submit_review',
                rc_nonce: rcewpp.nonce,
                type: 'hide'
            },
            success: function(response) {
                // Handle the success response
                $('.export-html-review-notice').remove();
                console.log('Hide request submitted successfully.');
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log('Error submitting hide request:', error);
            }
        });
    });
});

