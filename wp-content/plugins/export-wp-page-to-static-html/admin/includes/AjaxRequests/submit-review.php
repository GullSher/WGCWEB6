<?php


namespace ExportHtmlAdmin\EWPPTH_AjaxRequests\submitReview;

class initAjax extends \ExportHtmlAdmin\Export_Wp_Page_To_Static_Html_Admin
{
    private $ajax;

    public function __construct($ajax)
    {
        /*Initialize Ajax rc_search_posts*/
        add_action('wp_ajax_ewpptsh_submit_review', array( $this, 'submit_review_ajax_handler' ));
        add_action('wp_ajax_ewpptsh_submit_review2', array( $this, 'submit_review_ajax_handler2' ));
        $this->ajax = $ajax;
    }


    /**
     * Ajax action name: rc_search_posts
     * @since    1.0.0
     * @access   public
     * @return json
     */

    // AJAX handler for submitting the review requests
    function submit_review_ajax_handler() {
        // Verify the nonce

        if (!$this->ajax->nonceCheck()){
            wp_send_json_error('Nonce failed!');
            die();
        }

        // Handle the different types of requests
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

        switch ($type) {
            case 'done':
                // Perform actions for "Done!" button click
                update_option('ewpptsh_review_status', 'done');
                //response
                wp_send_json_success('Review submitted successfully.');
                break;

            case 'remind_later':
                // Perform actions for "Remind me later" button click

                update_option('ewpptsh_review_status', 'later');
                update_option('ewpptsh_next_review_status', time());

                //response
                wp_send_json_success('Remind me later request submitted successfully.');
                break;

            case 'hide':
                update_option('ewpptsh_review_status', 'later');
                update_option('ewpptsh_next_review_status', time());

                //response
                wp_send_json_success('Remind me later request submitted successfully.');
                /*// Perform actions for "Hide" button click
                update_option('ewpptsh_review_status', 'hide');
                // response
                wp_send_json_success('Hide request submitted successfully.');*/
                break;

            default:
                wp_send_json_error('Invalid request type.');
                break;
        }
    }
    // AJAX handler for submitting the review requests
    function submit_review_ajax_handler2() {
        // Verify the nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce( $nonce, "rc-nonce" )) {
            wp_send_json_error('Invalid nonce.');
        }

        // Handle the different types of requests
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

        switch ($type) {
            case 'done':
                // Perform actions for "Done!" button click
                update_option('ewpptsh_review_status2', 'done');
                //response
                wp_send_json_success('Review submitted successfully.');
                break;

            case 'remind_later':
                // Perform actions for "Remind me later" button click

                update_option('ewpptsh_review_status2', 'later');
                update_option('ewpptsh_next_review_status2', time());

                //response
                wp_send_json_success('Remind me later request submitted successfully.');
                break;

            case 'hide':
                update_option('ewpptsh_review_status2', 'later');
                update_option('ewpptsh_next_review_status2', time());

                //response
                wp_send_json_success('Remind me later request submitted successfully.');
                /*// Perform actions for "Hide" button click
                update_option('ewpptsh_review_status2', 'hide');
                // response
                wp_send_json_success('Hide request submitted successfully.');*/
                break;

            default:
                wp_send_json_error('Invalid request type.');
                break;
        }
    }


}