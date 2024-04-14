<?php
namespace ExportHtmlAdmin;

class AddMenuPage{
    private $admin;
    /**
     * AddMenuPage constructor.
     */
    public function __construct($admin)
    {
        $this->admin = $admin;

        if ($this->admin->hasAccess()){
            /*Adding admin menu on the admin sidebar*/
            add_action('admin_menu', array($this, 'register_export_wp_pages_menu') );
        }
    }


    public function register_export_wp_pages_menu(){

        add_menu_page(
            __('Export WP Page to Static HTML/CSS', 'different-menus'),
            'Export WP Page to Static HTML/CSS',
            'publish_posts',
            'export-wp-page-to-html',
            array(
                $this,
                'load_admin_dependencies'
            ),
            EWPPTSH_PLUGIN_URL . '/admin/images/html-icon.png',
            89
        );

        add_action('admin_init', array( $this,'register_export_wp_pages_settings') );
    }

    public function load_admin_dependencies(){
        require_once EWPPTSH_PLUGIN_DIR . '/admin/partials/export-wp-page-to-static-html-admin-display.php';
    }

    public function register_export_wp_pages_settings(){
        register_setting('export_wp_pages_settings', 'recorp_ewpp_settings');
    }
}