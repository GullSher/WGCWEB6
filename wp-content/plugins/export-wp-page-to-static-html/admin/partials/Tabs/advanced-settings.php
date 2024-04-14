<div class="tab-pane" id="tabs-5" role="tabpanel">
    <div class="p-t-20">
        <label class="checkbox-container full_site m-r-45" for="createIndexOnSinglePage"><?php _e('Create <b>index.html</b> on single page exporting', 'export-wp-page-to-static-html'); ?>
            <input type="checkbox" id="createIndexOnSinglePage" name="createIndexOnSinglePage" <?php echo $createIndexOnSinglePage ? 'checked' : ''; ?> >
            <span class="checkmark"></span>
        </label>
    </div>
    <div class="p-t-20">
        <label class="checkbox-container m-r-45" for="saveAllAssetsToSpecificDir"><?php _e('Save all assets files to the specific directory (css, js, images, fonts)', 'export-wp-page-to-static-html'); ?>
            <input type="checkbox" id="saveAllAssetsToSpecificDir" name="saveAllAssetsToSpecificDir" <?php echo $saveAllAssetsToSpecificDir ? 'checked' : ''; ?>>
            <span class="checkmark"></span>
        </label>
    </div>

    <div class="p-t-20">
        <label class="label m-r-45" for="addContentsToTheHeader"><?php _e('<b>Add contents to the header</b>', 'export-wp-page-to-static-html'); ?>
            <br>
            <textarea id="addContentsToTheHeader" name="addContentsToTheHeader" style="height: 80px; width: 100%"><?php echo $addContentsToTheHeader; ?></textarea>
        </label>
    </div>
    <div class="p-t-20">
        <label class="label m-r-45" for="addContentsToTheFooter"><?php _e('<b>Add contents to the footer</b>', 'export-wp-page-to-static-html'); ?>
            <br>
            <textarea id="addContentsToTheFooter" name="addContentsToTheFooter" style="height: 80px; width: 100%"><?php echo $addContentsToTheFooter; ?></textarea>
        </label>
    </div>
    <div class="p-t-20">
        <?php if(current_user_can('administrator')): ?>
        <div class="settings-item">
            <label class="label">
                <b><?php _e('User roles can access', 'export-wp-page-to-static-html'); ?></b>
            </label>

            <?php

            $selected_user_roles = (array) get_option('wpptsh_user_roles', array());
            $selected_user_roles = array_map('esc_attr', $selected_user_roles);
            $wp_roles = wp_roles()->get_names();
            foreach ( $wp_roles as $role => $name ) {
                if ($role=="administrator"){
                    echo '<label for="wpptsh-administrator" class="checkbox-label wpptsh-user-roles" style="margin-right: 12px;"><input id="wpptsh-administrator" type="checkbox" name="administrator_" checked disabled> Administrator</label>';
                }
                else{
                    $isChecked = in_array($role, $selected_user_roles) ? 'checked': '';
                    echo '<label for="wpptsh-'.esc_attr($role).'" class="checkbox-label wpptsh-user-roles" style="margin-right: 12px;"><input id="wpptsh-'.esc_attr($role).'" '.esc_attr($isChecked).' type="checkbox" name="user_roles['.esc_attr($role).']" value="'.esc_attr($role).'"> '.esc_attr($name).'</label>';
                }
            }
            ?>
            <div style="margin-top: 5px; font-size: 13px;"><i><?php _e('Select user roles to access the "Export WP Pages to Static HTML/CSS" option.', 'export-wp-page-to-static-html'); ?></i></div>
        </div>
        <?php endif; ?>
    </div>

    <button class="btn btn--radius-2 btn--blue m-t-20 btn_save_settings" type="submit">Save Settings <span class="spinner_x hide_spin"></button>
    <span class="badge badge-success badge_save_settings" style="display: none; padding: 5px">Successfully Saved!</span>
</div>