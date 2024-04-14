<?php


namespace ExportHtmlAdmin\EWPPTH_AjaxRequests\saveAdvancedSettings;

class initAjax extends \ExportHtmlAdmin\Export_Wp_Page_To_Static_Html_Admin
{
    private $ajax;
    public function __construct($ajax)
    {
        /*Initialize Ajax saveAdvancedSettings*/
        add_action('wp_ajax_saveAdvancedSettings', array( $this, 'saveAdvancedSettings' ));
        $this->ajax = $ajax;
    }

    /**
     * Ajax action name: saveAdvancedSettings
     * @since    2.0.0
     * @access   public
     * @return json
     */
    public function saveAdvancedSettings(){
        $createIndexOnSinglePage = isset($_POST['createIndexOnSinglePage']) && sanitize_key($_POST['createIndexOnSinglePage']) == 'true' ? true : false;
        $saveAllAssetsToSpecificDir = isset($_POST['saveAllAssetsToSpecificDir']) && sanitize_key($_POST['saveAllAssetsToSpecificDir']) == 'true'  ? true : false;
        $addContentsToTheHeader = isset($_POST['addContentsToTheHeader']) ? sanitize_textarea_field($_POST['addContentsToTheHeader']) : "";
        $addContentsToTheFooter = isset($_POST['addContentsToTheFooter']) ? sanitize_textarea_field($_POST['addContentsToTheFooter']) : "";

        $user_roles = isset($_POST['userRolesArray']) && is_array($_POST['userRolesArray']) ? array_map('sanitize_text_field', $_POST['userRolesArray']) : array();

        if (!$this->ajax->nonceCheck()){
            echo json_encode(array('success' => false, 'status' => 'nonce_verify_error', 'response' => ''));
            die();
        }
        update_option('rcExportHtmlCreateIndexOnSinglePage', $createIndexOnSinglePage);
        update_option('rcExportHtmlSaveAllAssetsToSpecificDir', $saveAllAssetsToSpecificDir);
        update_option('rcExportHtmlAddContentsToTheHeader', $addContentsToTheHeader);
        update_option('rcExportHtmlAddContentsToTheFooter', $addContentsToTheFooter);
        update_option('wpptsh_user_roles', $user_roles);

        echo json_encode(array('success' => true, 'status' => 'success', 'response' => $user_roles));

        die();
    }


}