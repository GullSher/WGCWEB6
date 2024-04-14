<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.upwork.com/fl/rayhan1
 * @since      1.0.0
 *
 * @package    Export_Wp_Page_To_Static_Html
 * @subpackage Export_Wp_Page_To_Static_Html/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Export_Wp_Page_To_Static_Html
 * @subpackage Export_Wp_Page_To_Static_Html/includes
 * @author     ReCorp <rayhankabir1000@gmail.com>
 */
class Export_Wp_Page_To_Static_Html {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Export_Wp_Page_To_Static_Html_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'EXPORT_WP_PAGE_TO_STATIC_HTML_VERSION' ) ) {
			$this->version = EXPORT_WP_PAGE_TO_STATIC_HTML_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'export-wp-page-to-static-html';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Export_Wp_Page_To_Static_Html_Loader. Orchestrates the hooks of the plugin.
	 * - Export_Wp_Page_To_Static_Html_i18n. Defines internationalization functionality.
	 * - Export_Wp_Page_To_Static_Html_Admin. Defines all hooks for the admin area.
	 * - Export_Wp_Page_To_Static_Html_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-export-wp-page-to-static-html-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-export-wp-page-to-static-html-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-export-wp-page-to-static-html-admin.php';
		
		/**
		 * The class responsible for defining global functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/global_functions.php';
		/**
		 * The class responsible for defining review functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/review-notice.php';


		$this->loader = new Export_Wp_Page_To_Static_Html_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Export_Wp_Page_To_Static_Html_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Export_Wp_Page_To_Static_Html_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new ExportHtmlAdmin\Export_Wp_Page_To_Static_Html_Admin( $this->get_plugin_name(), $this->get_version() );


		if (isset($_GET['page']) && sanitize_key($_GET['page']) == 'export-wp-page-to-html') {
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Export_Wp_Page_To_Static_Html_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Export_Wp_Page_To_Static_Html_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Export_Wp_Page_To_Static_Html_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, EWPPTSH_PLUGIN_URL . '/admin/css/export-wp-page-to-static-html-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'ewppth_select2', EWPPTSH_PLUGIN_URL . '/admin/css/select2.min.css', array(), '4.0.5', 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Export_Wp_Page_To_Static_Html_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Export_Wp_Page_To_Static_Html_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, EWPPTSH_PLUGIN_URL . '/admin/js/export-wp-page-to-static-html-admin.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( 'rc_export_logs', EWPPTSH_PLUGIN_URL . '/admin/js/export-logs.js', array( $this->plugin_name ), $this->version, false );
        wp_enqueue_script( 'rc_extract_internal_page', EWPPTSH_PLUGIN_URL . '/admin/js/extract-internal-pages.js', array( $this->plugin_name, 'toaster' ), $this->version, false );

        wp_enqueue_script( 'ewppth_select2', EWPPTSH_PLUGIN_URL . '/admin/js/select2.min.js', array( 'jquery' ), '4.0.5', false );
        wp_enqueue_script( 'toaster', EWPPTSH_PLUGIN_URL . '/admin/js/toastr.js', array( 'jquery' ), '4.0.5', false );

    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Export_Wp_Page_To_Static_Html_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
