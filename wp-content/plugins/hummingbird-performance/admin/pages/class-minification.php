<?php
/**
 * Asset optimization admin pages.
 *
 * @package Hummingbird\Admin\Pages
 */

namespace Hummingbird\Admin\Pages;

use Hummingbird\Admin\Page;
use Hummingbird\Core\Modules\Minify;
use Hummingbird\Core\Settings;
use Hummingbird\Core\Utils;
use Hummingbird\WP_Hummingbird;
use Hummingbird\Core\Modules\Page_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Minification extends Page
 */
class Minification extends Page {

	/**
	 * Display mode.
	 *
	 * @since 1.7.1
	 * @var string $mode  Default: 'basic'. Possible: 'advanced', 'basic'.
	 */
	public $mode = 'basic';

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_react_scripts' ) );

		$this->setup_navigation();

		$minify_module = Utils::get_module( 'minify' );

		if ( ! $minify_module->scanner->is_scanning() ) {
			$minify_module->scanner->finish_scan();
		}

		if ( ! $minify_module->is_active() ) {
			return;
		}

		$redirect = false;

		// We are here from a performance report - enable advanced mode.
		if ( isset( $_GET['enable-advanced-settings'] ) ) {
			Settings::update_setting( 'view', 'advanced', 'minify' );
			$redirect = true;
		}

		$options = $minify_module->get_options();

		// CDN should be disabled.
		if ( isset( $options['use_cdn'] ) && true === $options['use_cdn'] && ! Utils::is_member() ) {
			$minify_module->toggle_cdn( false );
			if ( ! $minify_module->scanner->is_scanning() ) {
				$minify_module->clear_cache( false );
			}
		}

		// Re-check files button clicked.
		if ( isset( $_POST['recheck-files'] ) || isset( $_GET['recheck-files'] ) ) { // Input var ok.
			$minify_module->clear_cache( false );

			$collector = $minify_module->sources_collector;
			$collector::clear_collection();

			$minify_module->scanner->init_scan();
			$redirect = true;
		}

		// Reset to default button clicked on settings page.
		if ( isset( $_GET['reset'] ) ) { // Input var okay.
			check_admin_referer( 'wphb-reset-minification' );
			$minify_module->reset_minification_settings();
			$minify_module->clear_cache();
			$minify_module->scanner->init_scan();
			$redirect = true;
		}

		// Disable clicked on settings page.
		if ( isset( $_GET['disable'] ) ) { // Input var okay.
			check_admin_referer( 'wphb-disable-minification' );
			$minify_module->disable();
			$redirect = true;
		}

		if ( $redirect ) {
			wp_safe_redirect( Utils::get_admin_menu_url( 'minification' ) );
			exit;
		}
	}

	/**
	 * Enqueue scripts and styles for React.
	 */
	public function enqueue_react_scripts() {
		wp_enqueue_style( 'wphb-react-minify-styles', WPHB_DIR_URL . 'admin/assets/css/wphb-react-minify.min.css', array(), WPHB_VERSION );
		wp_enqueue_script( 'wphb-react-minify', WPHB_DIR_URL . 'admin/assets/js/wphb-react-minify.min.js', array( 'wp-i18n', 'lodash', 'wphb-react-lib', 'wp-api-fetch' ), WPHB_VERSION, true );

		$current_page = filter_input( INPUT_GET, 'view', FILTER_UNSAFE_RAW );

		wp_localize_script(
			'wphb-react-minify',
			'wphbReact',
			array(
				'isMember'          => Utils::is_member(),
				'isHubMember'       => Utils::has_access_to_hub(),
				'isMultisite'       => is_multisite(),
				'brandingHeroImage' => apply_filters( 'wpmudev_branding_hero_image', '' ),
				'hideBranding'      => apply_filters( 'wpmudev_branding_hide_branding', false ),
				'filters'           => $this->get_selector_filters(),
				'mode'              => $this->mode,
				'showModal'         => (bool) get_option( 'wphb-minification-show-advanced_modal' ),
				'links'             => array(
					'connect'        => Utils::get_link( 'wpmudev-login' ),
					'site'           => site_url(),
					'images'         => WPHB_DIR_URL . 'admin/assets/image/',
					'support'        => array(
						'chat'  => Utils::get_link( 'chat' ),
						'forum' => Utils::get_link( 'support' ),
					),
					'cdnUpsell'      => Utils::get_link( 'plugin', 'hummingbird_topsummary_cdnbutton' ),
					'delayUpsell'    => Utils::get_link( 'plugin', 'hummingbird_delay_js_ao_summary' ),
					'criticalUpsell' => Utils::get_link( 'plugin', 'hummingbird_criticalcss_ao_summary' ),
					'isEoPage'       => 'tools' === $current_page ? true : false,
					'safeMode'       => site_url() . '?minify-safe=true',
				),
			)
		);

		wp_add_inline_script(
			'wphb-react-minify',
			'wp.i18n.setLocaleData( ' . wp_json_encode( Utils::get_locale_data() ) . ', "wphb" );',
			'before'
		);
	}

	/**
	 * Set up navigation for module.
	 *
	 * @since 1.8.2
	 */
	private function setup_navigation() {
		if ( is_multisite() && is_network_admin() ) {
			return;
		}

		$this->tabs = array(
			'files'    => __( 'Assets Optimization', 'wphb' ),
			'tools'    => __( 'Extra Optimization', 'wphb' ),
			'settings' => __( 'Settings', 'wphb' ),
		);

		$minify = Settings::get_setting( 'enabled', 'minify' );
		if ( is_multisite() && ( ( 'super-admins' === $minify && is_super_admin() ) || ( true === $minify ) ) ) {
			$this->tabs['import'] = __( 'Import / Export', 'wphb' );
		}

		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Render upgrade modal.
	 *
	 * @since 2.6.0
	 */
	public function render_modals() {
		parent::render_modals();

		if ( ! apply_filters( 'wp_hummingbird_is_active_module_minify', false ) || is_network_admin() ) {
			return;
		}

		if ( ! get_option( 'wphb_do_minification_upgrade' ) ) {
			return;
		}

		$this->modal( 'upgrade-minification' );
		?>
		<script>
			window.addEventListener("load", function(){
				window.SUI.openModal( 'wphb-upgrade-minification-modal', 'wpbody-content', undefined, false );
			});
		</script>
		<?php
	}

	/**
	 * Asset optimization orphaned data notice.
	 *
	 * @since 3.1.2
	 */
	public function notices() {
		if ( ! apply_filters( 'wp_hummingbird_is_active_module_minify', false ) ) {
			return;
		}

		// Clear cache show notice (from clear cache button and clear cache notice).
		if ( filter_input( INPUT_POST, 'clear-cache', FILTER_UNSAFE_RAW ) ) { // Input var ok.
			$this->admin_notices->show_floating( __( 'Your cache has been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ) );
		}

		if ( filter_input( INPUT_GET, 'wphb-cache-cleared-with-cloudflare', FILTER_UNSAFE_RAW ) ) { // Input var ok.
			$this->admin_notices->show_floating( __( 'Your local and Cloudflare caches have been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ) );
		}

		add_action( 'wphb_sui_header_sui_actions_right', array( $this, 'add_header_actions' ) );
		add_action( 'wphb_asset_optimization_notice', array( $this, 'render_http2_notice' ) );

		$this->infinite_loop_notice();
		$this->server_error_notice();
		$this->orphaned_notice();
	}

	/**
	 * Add content to the header.
	 *
	 * @since 2.5.0
	 */
	public function add_header_actions() {
		if ( ! apply_filters( 'wp_hummingbird_is_active_module_minify', false ) || is_network_admin() ) {
			return;
		}

		if ( ! isset( $this->mode ) || 'advanced' !== $this->mode ) {
			return;
		}
		?>
		<a class="sui-button sui-button-ghost" data-modal-open="wphb-tour-minification-modal" data-modal-open-focus="dialog-close-div" data-modal-mask="true">
			<span class="sui-icon-web-globe-world" aria-hidden="true"></span>
			<?php esc_html_e( 'Take a Tour', 'wphb' ); ?>
		</a>
		<?php
	}

	/**
	 * Show HTTP/2 notice.
	 *
	 * @since 2.6.0
	 */
	public function render_http2_notice() {
		if ( apply_filters( 'wpmudev_branding_hide_branding', false ) ) {
			return;
		}

		if ( ! $this->admin_notices->can_show_notice( 'http2-info' ) ) {
			return;
		}

		if ( Utils::get_module( 'minify' )->scanner->is_scanning() ) {
			return;
		}
		?>
		<div role="alert" class="sui-box sui-summary sui-summary-sm wphb-box-notice <?php echo isset( $_SERVER['WPMUDEV_HOSTED'] ) ? '' : 'wphb-notice-upsell'; ?>" aria-live="assertive">
			<div class="sui-summary-image-space" aria-hidden="true"></div>
			<div class="sui-summary-segment">
				<div class="sui-summary-details sui-no-padding-left">
					<span class="sui-summary-sub sui-no-margin-bottom">
						<?php
						if ( isset( $_SERVER['WPMUDEV_HOSTED'] ) ) {
							esc_attr_e( 'Your server is running the HTTP/2 protocol which automatically optimizes the delivery of your assets for you. You can still combine, and move your files, though this may not always improve performance.', 'wphb' );
						} else {
							printf(
								/* translators: %1$s - opening <a> tag, %2$s - closing </a> tag */
								esc_html__( 'Did you know WPMU DEV Hosting runs the HTTP/2 protocol, which automatically optimizes the delivery of your assets for you? Improve your site speed and performance by hosting your site with WPMU DEV. You can learn more about WPMU DEV Hosting %1$shere%2$s.', 'wphb' ),
								'<a href="' . esc_url( Utils::get_link( 'hosting', 'AO_hosting_upsell' ) ) . '" target="_blank">',
								'</a>'
							);
						}
						?>
					</span>
					<?php if ( ! isset( $_SERVER['WPMUDEV_HOSTED'] ) ) : ?>
						<a href="<?php echo esc_url( Utils::get_link( 'hosting', 'AO_hosting_upsell' ) ); ?>" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;">
							<?php esc_html_e( 'Host with us', 'wphb' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="wphb-dismiss-icon">
				<a id="wphb-floating-http2-info" class="dismiss" href="#" aria-label="<?php esc_attr_e( 'Dismiss', 'wphb' ); ?>">
					<span class="sui-icon-close" aria-hidden="true"></span>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Show notice if infinite loop detected.
	 *
	 * @since 3.4.0 Moved out to a 'wphb_asset_optimization_notice' action.
	 *
	 * @return void
	 */
	private function infinite_loop_notice() {
		if ( ! get_transient( 'wphb_infinite_loop_warning' ) ) {
			return;
		}

		$text = esc_html__( 'Issues processing queue. Hummingbird performance can be degraded.', 'wphb' );
		$this->admin_notices->show_floating( $text, 'error' );
	}

	/**
	 * Server error notice.
	 *
	 * @since 3.4.0 Moved out to a 'wphb_asset_optimization_notice' action.
	 *
	 * @return void
	 */
	private function server_error_notice() {
		$module = Utils::get_module( 'minify' );

		if ( ! $module->errors_controller->is_server_error() ) {
			return;
		}

		$server_errors = $module->errors_controller->get_server_errors();

		$message = sprintf( /* translators: %d: Time left before another retry. */
			__( 'It seems that we are having problems in our servers. Asset optimization will be turned off for %d minutes', 'wphb' ),
			$module->errors_controller->server_error_time_left()
		) . '<br>' . $server_errors[0]->get_error_message();

		$this->admin_notices->show_floating( $message, 'error' );
	}

	/**
	 * Orphaned data notice.
	 *
	 * @return void
	 */
	private function orphaned_notice() {
		$orphaned_metas = Utils::get_module( 'advanced' )->get_orphaned_ao_complex();
		if ( $orphaned_metas < 100 ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible">
			<span class="hidden" id="count-ao-orphaned"><?php echo esc_html( $orphaned_metas ); ?></span>
			<p>
				<?php
				printf( /* translators: %1$s - opening a link <a>, %2$s - Close the link </a>,%3$s - Link to HB health page */
					esc_html__( "We've detected some orphaned asset optimization metadata, which exceeded the acceptable limit. To avoid unnecessary database bloating and performance issues, click %1\$shere%2\$s to delete all the orphaned data. For more information check the %3\$sPlugins Health%2\$s page.", 'wphb' ),
					'<a href="#" onclick="WPHB_Admin.minification.purgeOrphanedData()">',
					'</a>',
					'<a href="' . esc_url( Utils::get_admin_menu_url( 'advanced' ) . '&view=health' ) . '">'
				)
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		if ( is_multisite() && is_network_admin() ) {
			$this->add_meta_box(
				'minification/network-settings',
				__( 'Settings', 'wphb' ),
				array( $this, 'network_settings_meta_box' )
			);

			return;
		}

		/**
		 * Disabled state meta box.
		 */
		$minify_module = Utils::get_module( 'minify' );
		if ( ! $minify_module->is_active() || $minify_module->scanner->is_scanning() ) {
			$this->add_meta_box(
				'minification/empty-files',
				__( 'Get Started', 'wphb' ),
				null,
				null,
				null,
				'box-enqueued-files-empty',
				array(
					'box_content_class' => 'sui-box sui-message',
				)
			);

			return;
		}

		// Move it here from __construct, so we don't make an extra db call if minification is disabled.
		$this->mode = Settings::get_setting( 'view', 'minify' );

		$this->add_meta_box( 'minification/react/summary', null, null, null, null, 'summary' );
		$this->add_meta_box( 'minification/react/files', null, null, null, null, 'minify' );

		/**
		 * Tools meta box.
		 */
		$this->add_meta_box(
			'minification/tools',
			__( 'Extra Optimization', 'wphb' ),
			array( $this, 'tools_metabox' ),
			null,
			null,
			'tools'
		);

		/**
		 * Settings meta box.
		 */
		$this->add_meta_box(
			'minification/settings',
			__( 'Settings', 'wphb' ),
			array( $this, 'settings_metabox' ),
			null,
			null,
			'settings',
			array(
				'box_content_class' => Utils::is_member() ? 'sui-box-body' : 'sui-box-body sui-upsell-items',
			)
		);

		/**
		 * Import/export meta box.
		 *
		 * @since 3.1.1
		 */
		$this->add_meta_box(
			'minification/import',
			__( 'Import / Export', 'wphb' ),
			array( $this, 'import_meta_box' ),
			null,
			null,
			'import'
		);
	}

	/**
	 * *************************
	 * Asset Optimization manual
	 *
	 * @since 2.6.0
	 ***************************/

	/**
	 * Tools meta box.
	 *
	 * @since 1.8
	 */
	public function tools_metabox() {
		$minify_options = Settings::get_settings( 'minify' );

		$this->view(
			'minification/tools-meta-box',
			array(
				'css'                            => Minify::get_css(),
				'manual_inclusion'               => Minify::get_css( 'manual-critical' ),
				'delay_js'                       => Settings::get_setting( 'delay_js', 'minify' ),
				'delay_js_excludes'              => Settings::get_setting( 'delay_js_exclusions', 'minify' ),
				'delay_js_timeout'               => Settings::get_setting( 'delay_js_timeout', 'minify' ),
				'is_member'                      => Utils::is_member(),
				'critical_css'                   => Settings::get_setting( 'critical_css', 'minify' ),
				'critical_css_type'              => Settings::get_setting( 'critical_css_type', 'minify' ),
				'critical_css_remove_type'       => Settings::get_setting( 'critical_css_remove_type', 'minify' ),
				'critical_css_mode'              => Settings::get_setting( 'critical_css_mode', 'minify' ),
				'settings'                       => $minify_options,
				'blog_is_frontpage'              => 'posts' === get_option( 'show_on_front' ) && ! is_multisite(),
				'pages'                          => Page_Cache::get_page_types(),
				'critical_css_status'            => Utils::get_module( 'critical_css' )->critical_css_status_for_queue(),
				'critical_css_generation_notice' => Utils::get_module( 'critical_css' )->critical_css_generation_complete_notice(),
				'custom_post_types'              => get_post_types(
					array(
						'public'   => true,
						'_builtin' => false,
					),
					'objects'
				),
			)
		);
	}

	/**
	 * Settings meta box.
	 *
	 * @since 1.9
	 */
	public function settings_metabox() {
		$log = WP_Hummingbird::get_instance()->core->logger->get_file( 'minify' );
		if ( ! file_exists( $log ) ) {
			$log = false;
		}

		$path_url = $log;
		if ( $path_url && defined( 'WP_CONTENT_DIR' ) ) {
			$path_url = content_url() . str_replace( WP_CONTENT_DIR, '', $log );
		}

		$this->view(
			'minification/settings-meta-box',
			array(
				'cdn_status'   => Utils::get_module( 'minify' )->get_cdn_status(),
				'cdn_excludes' => Settings::get_setting( 'nocdn', 'minify' ),
				'is_member'    => Utils::is_member(),
				'logging'      => Settings::get_setting( 'log', 'minify' ),
				'file_path'    => Settings::get_setting( 'file_path', 'minify' ),
				'logs_link'    => $log,
				'download_url' => wp_nonce_url(
					add_query_arg(
						array(
							'logs'   => 'download',
							'module' => Utils::get_module( 'minify' )->get_slug(),
						)
					),
					'wphb-log-action'
				),
				'path_url'     => $path_url,
				'safe_mode'    => Minify::get_safe_mode_status(),
			)
		);
	}

	/**
	 * Content after tabbed menu.
	 *
	 * @param string $tab  Tab name.
	 */
	public function after_tab( $tab ) {
		if ( 'tools' === $tab ) {
			echo ' <span class="sui-tag sui-tag-green">' . esc_html__( 'NEW', 'wphb' ) . '</span>';
		}
	}

	/**
	 * Network settings meta box.
	 *
	 * @since 2.0.0
	 */
	public function network_settings_meta_box() {
		$minify  = Utils::get_module( 'minify' );
		$options = $minify->get_options();

		$is_member = Utils::is_member();

		$enabled = 'super-admins' === $options['enabled'] || $options['enabled'];

		$this->view(
			'minification/network-settings-meta-box',
			array(
				'download_url'     => wp_nonce_url(
					add_query_arg(
						array(
							'logs'   => 'download',
							'module' => Utils::get_module( 'minify' )->get_slug(),
						)
					),
					'wphb-log-action'
				),
				'enabled'          => $enabled,
				'is_member'        => $is_member,
				'log_enabled'      => $options['log'],
				'type'             => $enabled ? $options['enabled'] : 'super-admins',
				'use_cdn'          => $minify->get_cdn_status(),
				'use_cdn_disabled' => ! $is_member || ! $options['enabled'],
				'file_path'        => $options['file_path'],
			)
		);
	}

	/**
	 * Import/export meta box. Shown on subsites.
	 *
	 * @since 3.1.1
	 */
	public function import_meta_box() {
		$this->view( 'settings/import-export-meta-box' );
	}

	/**
	 * Get plugins and themes to use as search filters for assets.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 */
	private function get_selector_filters() {
		$active_plugins = get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			foreach ( get_site_option( 'active_sitewide_plugins', array() ) as $plugin => $item ) {
				$active_plugins[] = $plugin;
			}
		}

		$selector_filter = array();

		$theme_name = wp_get_theme()->get( 'Name' );

		$selector_filter[ $theme_name ] = $theme_name;

		foreach ( $active_plugins as $plugin ) {
			if ( ! is_file( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				continue;
			}

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

			// Found plugin, add it as a filter.
			if ( $plugin_data['Name'] ) {
				$selector_filter[ $plugin_data['Name'] ] = $plugin_data['Name'];
			}
		}

		return $selector_filter;
	}
}
