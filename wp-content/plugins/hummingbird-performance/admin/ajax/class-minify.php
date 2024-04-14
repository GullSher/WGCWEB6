<?php
/**
 * Asset Optimization AJAX actions.
 *
 * @since 2.7.2
 * @package Hummingbird\Admin\Ajax\Caching
 */

namespace Hummingbird\Admin\Ajax;

use Hummingbird\Core\Modules\Minify\Minify_Group;
use Hummingbird\Core\Modules\Minify\Sources_Collector;
use Hummingbird\Core\Settings;
use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Minify.
 */
class Minify {

	/**
	 * Minify constructor.
	 */
	public function __construct() {
		$endpoints = array(
			'minify_auto_status',
			'minify_clear_cache',
			'minify_recheck_files',
			'minify_reset_settings',
			'minify_auto_save_settings',
			'minify_manual_save_settings',
			'minify_activate_safe_mode',
			'minify_save_safe_mode_settings',
			'minify_save_and_publish_safe_mode',
			'minify_publish_safe_mode',
			'minify_discard_safe_mode',
			'minify_manual_status',
			'minify_regenerate_asset',
			'minify_toggle_cdn',
		);

		foreach ( $endpoints as $endpoint ) {
			/**
			 * Register callbacks.
			 *
			 * @uses minify_auto_status()
			 * @uses minify_clear_cache()
			 * @uses minify_recheck_files()
			 * @uses minify_reset_settings()
			 * @uses minify_auto_save_settings()
			 * @uses minify_manual_save_settings()
			 * @uses minify_activate_safe_mode()
			 * @uses minify_save_safe_mode_settings()
			 * @uses minify_publish_safe_mode()
			 * @uses minify_save_and_publish_safe_mode()
			 * @uses minify_discard_safe_mode()
			 * @uses minify_manual_status()
			 * @uses minify_regenerate_asset()
			 * @uses minify_toggle_cdn()
			 */
			add_action( "wp_ajax_wphb_react_$endpoint", array( $this, $endpoint ) );
		}
	}

	/**
	 * Get exclusions.
	 *
	 * @since 3.3.0  Moved out to a function to remove duplicate code.
	 *
	 * @param array $options  Asset optimization module options.
	 *
	 * @return array
	 */
	private function get_exclusions( $options ) {
		if ( 'basic' === $options['type'] ) {
			$excluded_styles  = $options['dont_minify']['styles'];
			$excluded_scripts = $options['dont_minify']['scripts'];
		} else {
			$excluded_styles  = array_unique( array_merge( $options['dont_minify']['styles'], $options['dont_combine']['styles'] ) );
			$excluded_scripts = array_unique( array_merge( $options['dont_minify']['scripts'], $options['dont_combine']['scripts'] ) );

			$excluded_styles  = array_values( $excluded_styles );
			$excluded_scripts = array_values( $excluded_scripts );
		}

		return array( (array) $excluded_styles, (array) $excluded_scripts );
	}

	/**
	 * Get asset optimization status.
	 *
	 * @since 2.7.2
	 */
	public function minify_auto_status() {
		check_ajax_referer( 'wphb-fetch' );

		// Check permission.
		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		$options = Utils::get_module( 'minify' )->get_options();

		list( $excluded_styles, $excluded_scripts ) = $this->get_exclusions( $options );

		wp_send_json_success(
			array(
				'assets'     => Sources_Collector::get_collection(),
				'enabled'    => array(
					'styles'  => isset( $options['do_assets']['styles'] ) ? $options['do_assets']['styles'] : false,
					'scripts' => isset( $options['do_assets']['scripts'] ) ? $options['do_assets']['scripts'] : false,
					'fonts'   => isset( $options['do_assets']['fonts'] ) ? $options['do_assets']['fonts'] : false,
				),
				'exclusions' => array(
					'styles'  => $excluded_styles,
					'scripts' => $excluded_scripts,
				),
				'type'       => $options['type'],
			)
		);
	}

	/**
	 * Fetch/refresh asset optimization status.
	 *
	 * @since 2.7.2
	 */
	public function minify_clear_cache() {
		check_ajax_referer( 'wphb-fetch' );

		// Check permission.
		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		Utils::get_module( 'minify' )->clear_cache( false );
		Utils::get_module( 'critical_css' )->regenerate_critical_css();

		wp_send_json_success(
			array(
				'isCriticalActive' => Utils::get_module( 'critical_css' )->is_active(),
			)
		);
	}

	/**
	 * Re-check files.
	 *
	 * @since 2.7.2
	 */
	public function minify_recheck_files() {
		check_ajax_referer( 'wphb-fetch' );

		// Check permission.
		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		Utils::get_module( 'minify' )->clear_cache( false );

		$collector = Utils::get_module( 'minify' )->sources_collector;
		$collector::clear_collection();

		// Activate minification if is not.
		Utils::get_module( 'minify' )->toggle_service( true );
		Utils::get_module( 'minify' )->scanner->init_scan();

		wp_send_json_success();
	}

	/**
	 * Reset asset optimization settings.
	 *
	 * @since 2.7.2
	 */
	public function minify_reset_settings() {
		check_ajax_referer( 'wphb-fetch' );

		// Check permission.
		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		$options = Utils::get_module( 'minify' )->get_options();

		$defaults = Settings::get_default_settings();

		$options['do_assets']    = $defaults['minify']['do_assets'];
		$options['dont_minify']  = $defaults['minify']['dont_minify'];
		$options['dont_combine'] = $defaults['minify']['dont_combine'];
		$options['fonts']        = $defaults['minify']['fonts'];

		Utils::get_module( 'minify' )->update_options( $options );
		Utils::get_module( 'minify' )->clear_cache( false );

		wp_send_json_success();
	}

	/**
	 * Save asset optimization settings.
	 *
	 * @since 2.7.2
	 */
	public function minify_auto_save_settings() {
		check_ajax_referer( 'wphb-fetch' );

		// Check permission.
		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		$settings = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$settings = json_decode( html_entity_decode( $settings ), true );

		$options = Utils::get_module( 'minify' )->get_options();

		// Update selected type.
		$type_changed = false;
		if ( isset( $settings['type'] ) && in_array( $settings['type'], array( 'speedy', 'basic' ), true ) ) {
			$type_changed    = $options['type'] !== $settings['type'];
			$options['type'] = $settings['type'];
		}

		// Process font optimization changes.
		$options['do_assets']['fonts'] = ! ( isset( $settings['fonts'] ) && false === $settings['fonts'] ) && 'speedy' === $settings['type'];
		if ( false === $options['do_assets']['fonts'] ) {
			$options['fonts'] = array();
		}

		$collections = Sources_Collector::get_collection();

		foreach ( array( 'scripts', 'styles' ) as $type ) {
			$new_value = ! ( isset( $settings[ $type ] ) && false === $settings[ $type ] );

			$remove_exclusions = true === $new_value && false === $options['do_assets'][ $type ];

			// Save the type selection.
			$options['do_assets'][ $type ] = $new_value;

			// By default, we minify and combine everything.
			$options['dont_minify'][ $type ] = array();
			if ( 'speedy' === $settings['type'] ) {
				$options['dont_combine'][ $type ] = array();
			} else {
				$options['dont_combine'][ $type ] = array_keys( $collections[ $type ] );
			}

			// At this point we have no setting field? Weird, let's skip further processing.
			if ( ! isset( $settings[ $type ] ) ) {
				continue;
			}

			$handles = array();
			if ( false === $options['do_assets'][ $type ] ) {
				// If an option (CSS/JS) is disabled, put all handles in the "don't do" list.
				$handles = array_keys( $collections[ $type ] );
			} elseif ( ! $remove_exclusions && count( $settings['exclusions'][ $type ] ) !== count( $collections[ $type ] ) ) {
				// If the exclusion does not have all the assets, exclude the selected ones.
				$handles = $settings['exclusions'][ $type ];
			}

			$options['dont_minify'][ $type ] = $handles;
			// We've already excluded all the handles for basic above.
			if ( 'speedy' === $settings['type'] ) {
				$options['dont_combine'][ $type ] = $handles;
			}
		}

		Utils::get_module( 'minify' )->update_options( $options );

		// After we've updated the options - process fonts.
		if ( true === $options['do_assets']['fonts'] ) {
			do_action( 'wphb_process_fonts' );
		}

		Utils::get_module( 'minify' )->clear_cache( false );

		if ( $type_changed ) {
			$type_changed = sprintf( /* translators: %1$s - optimization type, %2$s - opening <a> tag, %3$s - closing </a> tag */
				esc_html__( '%1$s optimization is now active. Plugins and theme files are now being queued for processing and will gradually be optimized as they are requested by your visitors. For more information on how automatic optimization works, you can check %2$sHow Does It Work%3$s section.', 'wphb' ),
				'basic' === $settings['type'] ? __( 'Basic', 'wphb' ) : __( 'Speedy', 'wphb' ),
				"<a href='#' id='wphb-basic-hdiw-link' data-modal-open='automatic-ao-hdiw-modal-content'>",
				'</a>'
			);
		}

		list( $excluded_styles, $excluded_scripts ) = $this->get_exclusions( $options );

		wp_send_json_success(
			array(
				'assets'     => $collections,
				'enabled'    => array(
					'styles'  => $options['do_assets']['styles'],
					'scripts' => $options['do_assets']['scripts'],
					'fonts'   => $options['do_assets']['fonts'],
				),
				'exclusions' => array(
					'styles'  => $excluded_styles,
					'scripts' => $excluded_scripts,
				),
				'notice'     => $type_changed,
			)
		);
	}

	/**
	 * Save asset optimization settings (manual mode).
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function minify_manual_save_settings() {
		check_ajax_referer( 'wphb-fetch' );

		$settings = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$settings = json_decode( html_entity_decode( $settings ), true );

		$minify      = Utils::get_module( 'minify' );
		$this->save_manual_settings( $settings, array( $minify, 'update_options' ) );

		$this->minify_manual_status();
	}

	private function save_manual_settings( $settings, $save_method ) {
		$minify  = Utils::get_module( 'minify' );
		$options = $minify->get_options();

		foreach ( $settings as $action => $assets ) {
			if ( ! isset( $options[ $action ] ) || ! is_array( $options[ $action ] ) ) {
				continue;
			}

			if ( 'fonts' !== $action ) {
				// Prevent fatal error.
				$assets_scripts = ( empty( $assets['scripts'] ) || ! is_array( $assets['scripts'] ) ) ? array() : $assets['scripts'];
				$diff_scripts   = array_diff( $assets_scripts, $options[ $action ]['scripts'] );
				$this->clear_out_group( $diff_scripts, 'scripts' );

				// Prevent fatal error.
				$assets_styles = ( empty( $assets['styles'] ) || ! is_array( $assets['styles'] ) ) ? array() : $assets['styles'];
				$diff_styles   = array_diff( $assets_styles, $options[ $action ]['styles'] );
				$this->clear_out_group( $diff_styles, 'styles' );
			}

			$options[ $action ] = $assets;
		}

		call_user_func( $save_method, $options );

		// Remove notice.
		delete_site_option( 'wphb-notice-minification-optimized-show' );

		// Clear all the page cache.
		do_action( 'wphb_clear_page_cache' );
	}

	public function minify_save_safe_mode_settings() {
		check_ajax_referer( 'wphb-fetch' );

		$settings = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$settings = json_decode( html_entity_decode( $settings ), true );

		$this->save_manual_settings(
			$this->filter_asset_options( $settings ),
			array( $this, 'save_safe_mode_settings' )
		);

		$this->minify_manual_status();
	}

	public function minify_save_and_publish_safe_mode() {
		check_ajax_referer( 'wphb-fetch' );

		$settings = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$settings = json_decode( html_entity_decode( $settings ), true );

		$minify = Utils::get_module( 'minify' );
		$this->save_manual_settings( $settings, array( $minify, 'update_options' ) );
		$minify->reset_safe_mode();

		$this->minify_manual_status();
	}

	public function minify_publish_safe_mode() {
		check_ajax_referer( 'wphb-fetch' );

		$minify   = Utils::get_module( 'minify' );
		$settings = array_merge( $minify->get_options(), $minify->get_safe_mode_settings() );
		$settings = $this->filter_asset_options( $settings );

		$this->save_manual_settings( $settings, array( $minify, 'update_options' ) );
		$minify->reset_safe_mode();

		$this->minify_manual_status();
	}

	public function minify_discard_safe_mode() {
		check_ajax_referer( 'wphb-fetch' );

		Utils::get_module( 'minify' )->reset_safe_mode();
		$this->minify_manual_status();
	}

	private function save_safe_mode_settings( $settings ) {
		$minify = Utils::get_module( 'minify' );
		$minify->set_safe_mode_settings( $this->filter_asset_options( $settings ) );
	}

	/**
	 * Clear out groups for assets, where settings have changed.
	 *
	 * @since 3.4.0
	 *
	 * @param array  $assets Changed asset handles.
	 * @param string $type   Asset type (scripts or styles).
	 *
	 * @return void
	 */
	private function clear_out_group( $assets, $type ) {
		if ( empty( $assets ) ) {
			return;
		}

		foreach ( $assets as $asset ) {
			$changed_groups = Minify_Group::get_groups_from_handle( $asset, $type );
			foreach ( $changed_groups as $group ) {
				/**
				 * Delete those groups.
				 *
				 * @var Minify_Group $group
				 */
				$group->delete_file();
			}
		}
	}

	/**
	 * Status for asset optimization manual mode.
	 *
	 * @since 3.4.0
	 */
	public function minify_manual_status() {
		check_ajax_referer( 'wphb-fetch' );

		$minify  = Utils::get_module( 'minify' );
		$options = $this->filter_asset_options( $minify->get_options() );
		$safe_mode_options = array_merge( $options, $minify->get_safe_mode_settings() );

		wp_send_json_success(
			array(
				'options'           => array_map( array( $this, 'flatten_array' ), $options ),
				'safe_mode_options' => $safe_mode_options,
			)
		);
	}

	/**
	 * Flatten the input array.
	 *
	 * We do this, because when we send an array with non-consecutive indexes, for example:
	 *   Array (
	 *     [0] => astra-google-fonts
	 *     [17] => global-styles
	 *     [18] => woocommerce-inline
	 *   )
	 * it is converted to an Object in JavaScript. So we need to reset the indexes.
	 *
	 * @param array $arr  Input array.
	 *
	 * @return array
	 */
	public function flatten_array( $arr ) {
		foreach ( $arr as $type => $values ) {
			if ( in_array( $type, array( 'scripts', 'styles' ), true ) ) {
				$arr[ $type ] = array_values( $values );
			} else {
				$arr = array_values( $arr );
			}
		}

		return $arr;
	}

	/**
	 * Reset individual file.
	 *
	 * @since 1.9.2
	 * @since 3.4.0 Moved out from Ajax class.
	 */
	public function minify_regenerate_asset() {
		check_ajax_referer( 'wphb-fetch' );

		if ( ! current_user_can( Utils::get_admin_capability() ) ) {
			die();
		}

		$data = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$data = json_decode( html_entity_decode( $data ), true );

		if ( ! isset( $data['handle'] ) || ! isset( $data['type'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Error removing asset file.', 'wphb' ),
				)
			);
		}

		Utils::get_module( 'minify' )->clear_file( $data['handle'], $data['type'] );

		wp_send_json_success();
	}

	/**
	 * Toggle CDN.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function minify_toggle_cdn() {
		check_ajax_referer( 'wphb-fetch' );

		$value = filter_input( INPUT_POST, 'data', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		Utils::get_module( 'minify' )->toggle_cdn( $value );
		Utils::get_module( 'minify' )->clear_files();
		$notice = esc_html__( 'Settings updated', 'wphb' );

		wp_send_json_success(
			array(
				'cdn'    => $value,
				'notice' => $notice,
			)
		);
	}

	/**
	 * Toggle safe mode.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function minify_activate_safe_mode() {
		check_ajax_referer( 'wphb-fetch' );

		$minify = Utils::get_module( 'minify' );
		$minify->set_safe_mode_status( true );

		// Reset the options to the DB value
		$saved_options = $this->filter_asset_options( $minify->get_options() );
		$saved_options = array_map( array( $this, 'flatten_array' ), $saved_options );

		// Use the settings sent in the request as the safe mode options
		$unsaved_options = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_UNSAFE_RAW );
		$unsaved_options = json_decode( html_entity_decode( $unsaved_options ), true );

		wp_send_json_success(
			array(
				'options'           => $saved_options,
				'safe_mode_options' => $unsaved_options,
			)
		);
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	private function filter_asset_options( array $options ) {
		unset( $options['do_assets'] );
		unset( $options['enabled'] );
		unset( $options['file_path'] );
		unset( $options['log'] );
		unset( $options['minify_blog'] );
		unset( $options['nocdn'] );
		unset( $options['type'] );
		unset( $options['use_cdn'] );
		unset( $options['view'] );
		unset( $options['delay_js'] );
		unset( $options['delay_js_exclusions'] );
		unset( $options['delay_js_timeout'] );
		unset( $options['delay_js_exclusion_list'] );
		unset( $options['critical_css'] );
		unset( $options['critical_css_type'] );
		unset( $options['critical_css_remove_type'] );
		unset( $options['critical_css_mode'] );
		unset( $options['critical_page_types'] );
		unset( $options['critical_skipped_custom_post_types'] );
		return $options;
	}

}
