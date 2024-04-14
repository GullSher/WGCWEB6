<?php

function ewpptsh_wp_plugin_admin_notice() {
    // Check if the plugin has been active for 7 days
    //$activation_date = get_option('ewpptsh_wp_plugin_activation_date');

    $plugin_path = WP_PLUGIN_DIR . '/export-wp-page-to-static-html/index.php';
    $activation_date = filemtime($plugin_path);


    $dateToCheck = get_option('ewpptsh_next_review_status2', '');
    $reviewStatus = get_option('ewpptsh_review_status2', '');
    // Calculate timestamp of 7 days ago
    $sevenDaysAgo = time() - (7 * 24 * 60 * 60);

    if ( ($reviewStatus!=="done"&&$reviewStatus!=="hide")&&( ($reviewStatus=="later" && $reviewStatus!=="" && $dateToCheck < $sevenDaysAgo)) || ($reviewStatus=="" && $activation_date < $sevenDaysAgo)) {
        // Display the notice
        $review_url = 'https://wordpress.org/support/plugin/ai-content-writing-assistant/reviews/#new-post';

        $notice_html = '<div class="export-html-review-notice2 notice notice-success" style="">
               <p style="font-size: 18px;"></p><h2 style="margin: 0" class="title">' . __('You have been using "Export WP Pages to Static HTML" plugin for a while now. Thank you!', 'export-wp-page-to-static-html') . ' 💕 </h2><p>' . __('If you have a minute, can you write a <b><a target="_blank" href="https://wordpress.org/support/plugin/export-wp-page-to-static-html/reviews/?rate=5#new-post">little review</a></b> for me? That would <b>really</b> bring me joy and motivation! 💫 <br>Don\'t hesitate to <b>share your feature requests</b> with the review, I always check them and try my best.', 'export-wp-page-to-static-html') . '</p>                                        
               <div style="padding: 5px 0 12px 0;display: flex;align-items: center"><a target="_blank" class="button button-primary" style="margin-right: 10px" href="https://wordpress.org/support/plugin/export-wp-page-to-static-html/reviews/?rate=5#new-post">
                    ✏️ '. __('Write Review', 'export-wp-page-to-static-html') .'</a>

                    <button id="submit-review-done2" class="button button-secondary" style="margin-right: 10px;"> ✌️ '. __('Done!', 'export-wp-page-to-static-html') . '</button>

                    <div style="flex: auto"></div>

                    <button id="submit-remind-later2" class="button button-secondary" style="margin-right: 10px;">⏰ ' . __('Remind me later', 'export-wp-page-to-static-html') . '</button>

                    <button id="submit-hide2" class="button-link">' .__('Hide', 'export-wp-page-to-static-html') . '</button>

                </div>
            </div>';
        echo $notice_html;
    }
}
add_action('admin_notices', 'ewpptsh_wp_plugin_admin_notice');

function ewpptsh_wp_plugin_dismiss_notice() {
    if (is_admin() && wp_verify_nonce(sanitize_key($_REQUEST['rc_nonce']), 'rc-nonce')) {
        update_option('ewpptsh_wp_plugin_dismissal_date', time());
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_ewpptsh_wp_plugin_dismiss_notice', 'ewpptsh_wp_plugin_dismiss_notice');

function ewpptsh_wp_plugin_close_notice() {
    if (is_admin() && wp_verify_nonce(sanitize_key($_REQUEST['rc_nonce']), 'rc-nonce')) {
        update_option('ewpptsh_wp_plugin_notice_closed', true);
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_ewpptsh_wp_plugin_close_notice', 'ewpptsh_wp_plugin_close_notice');

function ewpptsh_admin_print_footer_scripts(){
    ?>
    <style>
        .export-html-review-notice2 {
            background-color: #fff !important;
        }

        .export-html-review-notice2 p {
            margin: 0 0 10px;
        }

        .export-html-review-notice2 a {
            color: #0073aa;
            text-decoration: none;
        }

        .export-html-review-notice2 a:hover {
            text-decoration: underline;
        }
        .export-html-review-notice2 p {
            font-size: 14px;
        }

    </style>
    <script>
        jQuery(document).ready(function($) {
            // Handle "Done!" button click
            $(document).on('click', '#submit-review-done2', function(e) {
                e.preventDefault();

                // Perform AJAX request
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ewpptsh_submit_review2',
                        nonce: rcewpp.nonce,
                        type: 'done'
                    },
                    success: function(response) {
                        // Handle the success response
                        $('.export-html-review-notice2').remove();
                        console.log('Review submitted successfully.');
                    },
                    error: function(xhr, status, error) {
                        // Handle the error response
                        console.log('Error submitting review:', error);
                    }
                });
            });

            // Handle "Remind me later" button click
            $(document).on('click', '#submit-remind-later2', function(e) {
                e.preventDefault();

                // Perform AJAX request
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ewpptsh_submit_review2',
                        nonce: rcewpp.nonce,
                        type: 'remind_later'
                    },
                    success: function(response) {
                        // Handle the success response
                        $('.export-html-review-notice2').remove();
                        console.log('Remind me later request submitted successfully.');
                    },
                    error: function(xhr, status, error) {
                        // Handle the error response
                        console.log('Error submitting remind me later request:', error);
                    }
                });
            });

            // Handle "Hide" button click
            $(document).on('click', '#submit-hide2', function(e) {
                e.preventDefault();

                // Perform AJAX request
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'ewpptsh_submit_review2',
                        nonce: rcewpp.nonce,
                        type: 'hide'
                    },
                    success: function(response) {
                        // Handle the success response
                        $('.export-html-review-notice2').remove();
                        console.log('Hide request submitted successfully.');
                    },
                    error: function(xhr, status, error) {
                        // Handle the error response
                        console.log('Error submitting hide request:', error);
                    }
                });
            });
        });
    </script>
    <?php
}

add_action("admin_print_footer_scripts", "ewpptsh_admin_print_footer_scripts");
