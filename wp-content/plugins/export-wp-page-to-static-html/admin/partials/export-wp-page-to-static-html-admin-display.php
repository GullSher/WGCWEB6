<?php

    /**
     * Provide a admin area view for the plugin
     *
     * This file is used to markup the admin-facing aspects of the plugin.
     *
     * @link       https://www.upwork.com/fl/rayhan1
     * @since      1.0.0
     *
     * @package    Export_Wp_Page_To_Static_Html
     * @subpackage Export_Wp_Page_To_Static_Html/admin/partials
     */


    $args = array(
        'post_type' => 'page',
        'post_status' => ['publish', 'private'],
        'posts_per_page' => '-1'
    );

    $query = new WP_Query( $args );

    $ftp_status = get_option('rc_export_html_ftp_connection_status', "");


    $ftp_data = get_option('rc_export_html_ftp_data');

    $host = isset($ftp_data->host) ? $ftp_data->host : "";
    $user = isset($ftp_data->user) ? $ftp_data->user : "";
    $pass = isset($ftp_data->pass) ? $ftp_data->pass : "";
    $path = isset($ftp_data->path) ? $ftp_data->path : "";

    $createIndexOnSinglePage = get_option('rcExportHtmlCreateIndexOnSinglePage', true);
    $saveAllAssetsToSpecificDir = get_option('rcExportHtmlSaveAllAssetsToSpecificDir', true);
    $addContentsToTheHeader = get_option('rcExportHtmlAddContentsToTheHeader');
    $addContentsToTheFooter = get_option('rcExportHtmlAddContentsToTheFooter');

    ;?>

<div class="page-wrapper p-b-100 font-poppins static_html_settings">
    <div class="wrapper">
        <div class="card card-4">
            <div class="card-body">
                <h2 class="title"><?php _e('Export WP Pages to Static HTML/CSS', 'export-wp-page-to-static-html'); ?><span class="badge badge-dark version">v<?php echo EXPORT_WP_PAGE_TO_STATIC_HTML_VERSION; ?></span></h2>
                <div class="error-notice">
                    <p><?php echo __('Every site environment is unique, if your site failed to export to html then <a href="https://myrecorp.com/contact-us/">contact us.</a>. We\'ll try to help you as soon as possible.', 'export-wp-page-to-static-html'); ?></p>
                </div>

                <div class="row">
                    <div class="col-7">

                        <div class=" export_html main_settings_page">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">WP Pages</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Custom urls</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">All Exported Files</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-4" role="tab">FTP Settings <span class="tab_ftp_status <?php echo $ftp_status; ?>"></span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-5" role="tab">Advanced Settings</a>
                                </li>
                            </ul><!-- Tab panes -->
                            <div class="tab-content">
                                <!--Tab-1 WP Pages -->
                                <?php include 'Tabs/wp-pages.php'; ?>

                                <!--Tab-2 Custom URL -->
                                <?php include 'Tabs/custom-url.php'; ?>

                                <!--Tab-3 All exported files -->
                                <?php include 'Tabs/all-exported-files.php'; ?>

                                <!--Tab-4 FTP Settings -->
                                <?php include 'Tabs/ftp-settings.php'; ?>

                                <!--Tab-5 Advanced settings -->
                                <?php include 'Tabs/advanced-settings.php'; ?>


                            </div>

                        </div>


                        <div class="htmlExportLogs" style="display: none; margin-top: 15px;">
                            <h4 class="progress-title p-t-15"><?php _e('Html export log', 'export-wp-page-to-static-html'); ?></h4>
                            <span class="totalExported" style="margin-right: 10px">Exported: <span class="total_exported_files progress_">0</span></span>
                            <span class="totalLogs">Fetched files: <span class="total_fetched_files total_">0</span></span>
                            <div class="progress orange" style="margin-top: 20px">
                                <div class="progress-bar" style="width:0%; background:#fe3b3b;">
                                    <div class="progress-value">0%</div>
                                </div>
                            </div>
                            <div class="export_failed error" style="display: none;">Error, failed to export files! </div>
                        </div>

                        <div class="creatingZipFileLogs" style="display: none;">
                            <h4 class="progress-title p-t-15">Creating Zip File</h4>

                            <span class="totalPushedFilesToZip" style="margin-right: 10px">Created: <span class="total_pushed_files_to_zip progress_">0</span></span>
                            <span class="totalFilesToPush">Total files: <span class="total_files_to_push total_">0</span></span>

                            <div class="progress blue" style="margin-top: 20px">
                                <div class="progress-bar" style="width:90%; background:#1a4966;">
                                    <div class="progress-value">0%</div>
                                </div>
                            </div>
                            <div class="export_failed error" style="display: none;">Error, failed to create zip file! </div>
                        </div>

                        <div class="uploadingFilesToFtpLogs" style="display: none;">
                            <h4 class="progress-title p-t-15">Uploading Files to Ftp</h4>

                            <span class="totalUploadedFilesToFtp" style="margin-right: 10px">Uploaded: <span class="total_uploaded_files_to_ftp progress_">0</span></span>
                            <span class="totalFilesToUpload">Total files: <span class="total_files_to_upload total_">0</span></span>

                            <div class="progress green" style="margin-top: 20px">
                                <div class="progress-bar" style="width:90%; background:#4daf7c;">
                                    <div class="progress-value">0%</div>
                                </div>
                            </div>
                            <div class="export_failed error" style="display: none;">Upload failed! Check your network connection!</div>
                        </div>

                        <a class="see_logs_in_details" style="display: none;" href="#">See logs in details</a>

                        <div class="logs p-t-15 col-10">
                            <h4 class="p-t-15"><?php _e('Export log', 'export-wp-page-to-static-html'); ?></h4>
                            <div class="logs_list">
                            </div>
                        </div>

                    </div>

                    <div class="col-3 p-10 dev_section" >

                        <div class="created_by py-2 mt-1 border-bottom"> <?php _e('Created by', 'export-wp-page-to-static-html'); ?> <a href="https://myrecorp.com"><img src="<?php echo home_url() . '/wp-content/plugins/export-wp-page-to-static-html/admin/images/recorp-logo.png'; ?>" alt="ReCorp" width="100"></a></div>


                        <div class="documentation my-2">
                            <a href="https://myrecorp.com/documentation/export-wp-page-to-html"><?php _e('Documentation', 'export-wp-page-to-static-html'); ?></a>
                        </div>
                        <div class="documentation my-2">
                            <a href="https://myrecorp.com/support"><?php _e('Support', 'export-wp-page-to-static-html'); ?></a>
                        </div>
                        <div class="pro mt-4">
                            <span class="go_pro"><a href="https://myrecorp.com/product/export-wp-pages-to-static-html-css-pro/?clk=wp&a=sidebar-pro" target="_blank">Go to pro</a></span>
                          </div>


                        <div class="right_side_notice mt-4">
                            <?php echo do_action('wpptsh_right_side_notice'); ?>
                        </div>
                        <div class="plugin_rating mt-4">
                            <p id="rate-left" class="alignleft">
                                If you like <strong>this plugin</strong> please leave us a <a href="https://wordpress.org/support/plugin/export-wp-page-to-static-html/reviews?rate=5#new-post" target="_blank" class="wc-rating-link" aria-label="five star" data-rated="Thanks :)">★★★★★</a> rating. <br>A huge thanks in advance!  </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- This templates was made by Colorlib (https://colorlib.com) -->

<div class="ftp_path_select">
    <div class="loading_section">
        <span class="spinner_x"></span>
    </div>
    <h2>Select a directory to upload files</h2>

    <div class="ftp_dir_lists">

    </div>

    <button class="ftp_select_path">Select</button>


</div>

<div class="ftp_dark_blur">
    <div class="close ftp_path_selection"></div>
</div>
<div id="cancel_ftp_process" type="hidden" value="false"></div>

<script>

    var $ = jQuery;

    <?php

    if (!empty($query->posts)) {
    foreach ($query->posts as $key => $post) {
    $post_id = $post->ID;
    $post_title = $post->post_title;
    ?>

    <?php
    }
    }
    ?>

    function rc_select2_is_not_ajax(){

        var selectSimple = $('.js-select-simple');

        selectSimple.each(function () {
            var that = $(this);
            var selectBox = that.find('select');
            var selectDropdown = that.find('.select-dropdown');
            selectBox.select2({
                placeholder: "Choose a page",
                maximumSelectionLength: 3,
                dropdownParent: selectDropdown,
                matcher: function(params, option) {
                    // If there are no search terms, return all of the option
                    var searchTerm = $.trim(params.term);
                    if (searchTerm === '') { return option; }

                    // Do not display the item if there is no 'text' property
                    if (typeof option.text === 'undefined') { return null; }

                    var searchTermLower = searchTerm.toLowerCase(); // `params.term` is the user's search term

                    // `option.id` should be checked against
                    // `option.text` should be checked against
                    var searchFunction = function(thisOption, searchTerm) {
                        return thisOption.text.toLowerCase().indexOf(searchTerm) > -1 ||
                            (thisOption.id && thisOption.id.toLowerCase().indexOf(searchTerm) > -1);
                    };

                    if (!option.children) {
                        //we only need to check this option
                        return searchFunction(option, searchTermLower) ? option : null;
                    }

                    //need to search all the children
                    option.children = option
                        .children
                        .filter(function (childOption) {
                            return searchFunction(childOption, searchTermLower);
                        });
                    return option;
                },
                templateResult: function (idioma) {
                    var permalink = $(idioma.element).attr('permalink');
                    var $span = $("<span permalink='"+permalink+"'>" + idioma.text + "</span>");
                    return $span;
                }
            });
        });

    }

    $(document).ready(function(){
        rc_select2_is_not_ajax();
    });
</script>

<?php if($this->admin->getSettings('timestampError', false)||$this->admin->getSettings('cancel_command', false)||$this->admin->getSettings('task')=='completed'){
    $this->admin->removeAllSettings();
}
else if($this->admin->getSettings('task')=='running'):  ?>
    <script>
        $(document).ready(function(){
            <?php if($this->admin->getSettings('creating_html_process')=='running'||$this->admin->getSettings('creating_html_process')=='completed'): ?>
            $('.htmlExportLogs').show();
            <?php endif; ?>
            <?php if($this->admin->getSettings('creating_zip_process')=='running'||$this->admin->getSettings('creating_zip_process')=='completed'): ?>
            $('.creatingZipFileLogs').show();
            <?php endif; ?>
            <?php if($this->admin->getSettings('ftp_status')=='running'||$this->admin->getSettings('ftp_status')=='completed'): ?>
            $('.uploadingFilesToFtpLogs').show();
            <?php endif; ?>
            $('.see_logs_in_details').show();
            get_export_log_percentage(1000);
        });
    </script>
<?php endif; ?>


<?php if($this->admin->getSettings('task')=='completed' && !empty($this->admin->getSettings('zipDownloadLink')) && $this->admin->getSettings('zipDownloadLink')): ?>
    <?php
    $createdLastHtmlFile = "";
    if($this->admin->getSettings('creating_html_process')=="completed"){
        global $wpdb;
        $tempUrl = wp_upload_dir()['baseurl'].'/exported_html_files/tmp_files';
        $created_html_file = $wpdb->get_results("SELECT comment FROM {$wpdb->prefix}export_page_to_html_logs WHERE type='created_html_file' ORDER BY ID ASC LIMIT 1");
        $createdLastHtmlFile = isset($created_html_file[0]) ? $created_html_file[0]->comment : '';
        if(!empty($createdLastHtmlFile)){
            $createdLastHtmlFile = $tempUrl .'/'. $createdLastHtmlFile;
        }
    }
    ?>

    <script>$(document).ready(function(){$('.download-btn').attr('href', '<?php echo $this->admin->getSettings('zipDownloadLink'); ?>').text('Download The Last Exported File').removeClass('hide'); $('.view_exported_file').attr('href', '<?php echo $createdLastHtmlFile; ?>').removeClass('hide').text('View Last Exported File');});</script>
<?php endif; ?>



