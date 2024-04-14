<?php

namespace ExportHtmlAdmin\EWPPTH_AjaxRequests;
/**
 * Class name: EWPPTH_AjaxRequests
 */
class EWPPTH_AjaxRequests
{

    public function __construct()
    {
        $this->initAjaxRequestsFiles();
        $this->initAjaxRequestsClass();
    }

    public function initAjaxRequestsFiles()
    {
        include 'AjaxRequests/requestForWpPageToStaticHtml.php';
        include 'AjaxRequests/seeLogsInDetails.php';
        include 'AjaxRequests/exportLogPercentage.php';
        include 'AjaxRequests/searchPosts.php';
        include 'AjaxRequests/checkExportingProcessOnSettingsPageLoad.php';
        include 'AjaxRequests/deleteExportedZipFile.php';
        include 'AjaxRequests/cancelRcExportProcess.php';
        include 'AjaxRequests/saveAdvancedSettings.php';
        include 'AjaxRequests/submit-review.php';
    }

    public function initAjaxRequestsClass()
    {
        new seeLogsInDetails\initAjax($this);
        new exportLogPercentage\initAjax($this);
        new searchPosts\initAjax($this);
        new requestForWpPageToStaticHtml\initAjax($this);
        new checkExportingProcessOnSettingsPageLoad\initAjax($this);
        new deleteExportedZipFile\initAjax($this);
        new cancelRcExportProcess\initAjax($this);
        new saveAdvancedSettings\initAjax($this);
        new submitReview\initAjax($this);

    }

    public function nonceCheck()
    {
        $nonce = isset($_REQUEST['rc_nonce']) ? sanitize_text_field($_REQUEST['rc_nonce']) : '';
        if (!wp_verify_nonce( $nonce, "rc-nonce" )) {
            return false;
        }

        require( ABSPATH . WPINC . '/pluggable.php' );
        $capabilities = \get_option('wpptsh_user_roles',array('administrator'));

        if (!empty($capabilities)){
            foreach ($capabilities as $cap) {
                if (current_user_can($cap)){
                    return true;
                    break;
                }
            }
        }
        if (current_user_can('administrator')){
            return true;
        }
        return false;
    }

}

new EWPPTH_AjaxRequests;