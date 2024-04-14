<div class="tab-pane custom_links" id="tabs-2" role="tabpanel">

    <div class="customLinkSection blur">
        <div class="custom_link_section">
            <input type="text" name="custom_link" placeholder="Enter a url">
        </div>


        <div class="p-t-10">
            <label class="checkbox-container m-r-45">Full site (must use homepage url)
                <input type="checkbox" id="full_site2" name="full_site">
                <span class="checkmark"></span>
            </label>
        </div>

        <div class="p-t-10">
            <label class="checkbox-container m-r-45">Replace all url to #
                <input type="checkbox" id="replace_all_url2" name="replace_all_url">
                <span class="checkmark"></span>
            </label>
        </div>


        <div class="p-t-10">
            <label class="checkbox-container m-r-45" for="custom_url_skip_assets"><?php _e('Skip Assets (Css, Js, Images or Videos)', 'export-wp-page-to-static-html'); ?>
                <input type="checkbox" id="custom_url_skip_assets" name="custom_url_skip_assets">
                <span class="checkmark"></span>
            </label>

            <div class="skip_assets_subsection export_html_sub_settings">
                <label class="checkbox-container m-r-45" for="custom_url_skip_stylesheets"><?php _e('Skip Stylesheets (.css)', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_stylesheets" name="custom_url_skip_stylesheets" checked>
                    <span class="checkmark"></span>
                </label>

                <label class="checkbox-container m-r-45" for="custom_url_skip_scripts"><?php _e('Skip Scripts (.js)', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_scripts" name="custom_url_skip_scripts" checked>
                    <span class="checkmark"></span>
                </label>

                <label class="checkbox-container m-r-45" for="custom_url_skip_images"><?php _e('Skip Images', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_images" name="custom_url_skip_images" checked>
                    <span class="checkmark"></span>
                </label>

                <label class="checkbox-container m-r-45" for="custom_url_skip_videos"><?php _e('Skip Videos', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_videos" name="custom_url_skip_videos" checked>
                    <span class="checkmark"></span>
                </label>


                <label class="checkbox-container m-r-45" for="custom_url_skip_audios"><?php _e('Skip Audios', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_audios" name="custom_url_skip_audios" checked>
                    <span class="checkmark"></span>
                </label>

                <label class="checkbox-container m-r-45" for="custom_url_skip_docs"><?php _e('Skip Documents', 'export-wp-page-to-static-html'); ?>
                    <input type="checkbox" id="custom_url_skip_docs" name="custom_url_skip_docs" checked>
                    <span class="checkmark"></span>
                </label>
            </div>

        </div>


        <div class="p-t-10">
            <label class="checkbox-container ftp_upload_checkbox m-r-45 <?php
            if ($ftp_status !== 'connected') {
                echo 'ftp_disabled';
            }
            ?>"><?php _e('Upload to ftp', 'export-wp-page-to-static-html'); ?>
                <input type="checkbox" id="upload_to_ftp2" name="upload_to_ftp"

                    <?php
                    if ($ftp_status !== 'connected') {
                        echo 'disabled=""';
                    }
                    ?>
                >
                <span class="checkmark"></span>
            </label>

            <div class="ftp_Settings_section2 export_html_sub_settings">


                <!--  <div class="ftp_settings_item">
                                                <input type="text" id="ftp_host2" name="ftp_host" placeholder="Host" value="<?php echo $host; ?>">
                                            </div>
                                            <div class="ftp_settings_item">
                                                <input type="text" id="ftp_user2" name="ftp_user" placeholder="User" value="<?php echo $user; ?>">
                                            </div>
                                            <div class="ftp_settings_item">
                                                <input type="password" id="ftp_pass2" name="ftp_pass" placeholder="Password" value="<?php echo $pass; ?>">
                                            </div> -->
                <div class="ftp_settings_item">
                    <label for="ftp_path2">FTP upload path</label>
                    <input type="text" id="ftp_path2" name="ftp_path" placeholder="Upload path" value="<?php echo $path; ?>">
                    <div class="ftp_path_browse1"><a href="#">Browse</a></div>
                </div>
            </div>
        </div>


        <div class="p-t-10">
            <div class="email_settings_section">
                <div class="email_settings_item2">
                    <label class="checkbox-container m-r-45"><?php _e('Receive notification when complete', 'export-wp-page-to-static-html'); ?>
                        <input type="checkbox" id="email_notification2" name="email_notification">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <div class="email_settings_item">
                    <input type="text" id="receive_notification_email2" name="notification_email" placeholder="Enter emails (optional)">
                    <span>Enter emails seperated by comma (,) (optional)</span>
                </div>

            </div>
        </div>

        <div class="p-t-20"></div>
        <button class="btn btn--radius-2 btn--blue export_external_page_to_html" type="submit"><?php _e('Export HTML', 'export-wp-page-to-static-html'); ?> <span class="spinner_x hide_spin"></span></button>
        <a class="cancel_rc_html_export_process" href="#">
            Cancel
        </a>
        <a href="" class="btn btn--radius-2 btn--green download-btn hide" type="submit"><?php _e('Download the file', 'export-wp-page-to-static-html'); ?></a>
        <a href="" class="view_exported_file hide" type="submit" target="_blank"><?php _e('View Exported File', 'export-wp-page-to-static-html'); ?></a>

    </div>

    <div class="eh_premium">
        This option available for premium version only
        <div class="go_pro2">
            <select id="licenses">
                <option value="1" selected="selected">Single Site License</option>
                <option value="3">3-Site License</option>
                <option value="unlimited">Unlimited Site License</option>
            </select>
            <button id="purchase" class="location">Upgrade Now</button>
        </div>
    </div>
</div>