<?php
/**
 * Class Utils holds common functions used by the plugin.
 *
 * Class has the following structure:
 * I.   General helper functions
 * II.  Layout functions
 * III. Time and date functions
 * IV.  Link and url functions
 * V.   Modules functions
 *
 * @package Hummingbird\Core
 * @since 1.8
 */

namespace Hummingbird\Core;

use Hummingbird\WP_Hummingbird;
use stdClass;
use WP_User;
use WPMUDEV_Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Utils
 */
class Utils {

	/**
	 * Store HB plugin discount percent.
	 *
	 * @var int
	 */
	const HB_PLUGIN_DISCOUNT = 80;

	/***************************
	 *
	 * I. General helper functions
	 * is_member()
	 * has_access_to_hub()
	 * is_free_installed()
	 * is_dash_logged_in()
	 * src_to_path()
	 * enqueue_admin_scripts()
	 * get_tracking_data()
	 * get_admin_capability()
	 * get_current_user_name()
	 * calculate_sum()
	 * format_bytes()
	 * format_interval()
	 * format_interval_hours()
	 * is_ajax_network_admin()
	 ***************************/

	/**
	 * Check if user is a paid one in WPMU DEV
	 *
	 * @return bool
	 */
	public static function is_member() {
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( \WPMUDEV_Dashboard::$upgrader, 'user_can_install' ) ) {
			return \WPMUDEV_Dashboard::$upgrader->user_can_install( 1081721, true );
		}

		return false;
	}

	/**
	 * Check if plugin has access to API features (full, single and free plans).
	 *
	 * @since 3.3.1
	 *
	 * @return bool
	 */
	public static function has_access_to_hub() {
		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return false;
		}

		if ( ! method_exists( 'WPMUDEV_Dashboard_Api', 'get_membership_status' ) ) {
			return self::is_member();
		}

		// Possible values: full, single, free, expired, paused, unit.
		$plan = WPMUDEV_Dashboard::$api->get_membership_status();

		return in_array( $plan, array( 'full', 'single', 'free', 'unit' ), true );
	}

	/**
	 * Determines whether the WPMUDEV Hosted site is connected to The Free HUB.
	 *
	 * @since 3.3.4
	 *
	 * @return bool True if connected to The Free HUB, false otherwise.
	 */
	public static function is_hosted_site_connected_to_free_hub() {
		return class_exists( 'WPMUDEV_Dashboard' ) &&
			is_object( WPMUDEV_Dashboard::$api ) &&
			method_exists( WPMUDEV_Dashboard::$api, 'get_membership_status' ) &&
			'free' === WPMUDEV_Dashboard::$api->get_membership_status() &&
			isset( $_SERVER['WPMUDEV_HOSTED'] );
	}

	/**
	 * Try to cast a source URL to a path
	 *
	 * @param string $src  Source.
	 *
	 * @return string
	 */
	public static function src_to_path( $src ) {
		$path = wp_parse_url( $src );

		// Scheme will not be set on a URL.
		$url = isset( $path['scheme'] );

		if ( ! isset( $path['path'] ) ) {
			return '';
		}

		$path = ltrim( $path['path'], '/' );

		/**
		 * DOCUMENT_ROOT does not always store the correct path. For example, Bedrock appends /wp/ to the default dir.
		 * So if the source is a URL, we can safely use DOCUMENT_ROOT, else see if ABSPATH is defined.
		 */
		if ( $url ) {
			$path = path_join( $_SERVER['DOCUMENT_ROOT'], $path );
		} else {
			$root = defined( 'ABSPATH' ) ? ABSPATH : $_SERVER['DOCUMENT_ROOT'];
			$path = path_join( $root, $path );
		}

		$path = wp_normalize_path( $path );

		return apply_filters( 'wphb_src_to_path', $path, $src );
	}

	/**
	 * Enqueues admin scripts
	 *
	 * @param int $ver Current version number of scripts.
	 */
	public static function enqueue_admin_scripts( $ver ) {
		wp_enqueue_script( 'wphb-admin', WPHB_DIR_URL . 'admin/assets/js/wphb-app.min.js', array( 'jquery', 'underscore' ), $ver, true );

		$last_report = Modules\Performance::get_last_report();
		if ( is_object( $last_report ) && isset( $last_report->data ) ) {
			$desktop_score = is_object( $last_report->data->desktop ) ? $last_report->data->desktop->score : '-';
			$mobile_score  = is_object( $last_report->data->mobile ) ? $last_report->data->mobile->score : '-';
		}

		$i10n = array(
			'cloudflare' => array(
				'is' => array(
					'connected' => self::get_module( 'cloudflare' )->is_connected() && self::get_module( 'cloudflare' )->is_zone_selected(),
				),
			),
			'nonces'     => array(
				'HBFetchNonce' => wp_create_nonce( 'wphb-fetch' ),
			),
			'strings'    => array(
				/* Performance test strings */
				'previousScoreMobile'     => isset( $mobile_score ) ? $mobile_score : '-',
				'previousScoreDesktop'    => isset( $desktop_score ) ? $desktop_score : '-',
				'removeButtonText'        => __( 'Remove', 'wphb' ),
				'youLabelText'            => __( 'You', 'wphb' ),
				'scanRunning'             => __( 'Running speed test...', 'wphb' ),
				'scanAnalyzing'           => __( 'Analyzing data and preparing report...', 'wphb' ),
				'scanWaiting'             => __( 'Test is taking a little longer than expected, hang in there…', 'wphb' ),
				'scanComplete'            => __( 'Test complete! Reloading...', 'wphb' ),
				/* Caching strings */
				'errorCachePurge'         => __( 'There was an error during the cache purge. Check folder permissions are 755 for /wp-content/wphb-cache or delete directory manually.', 'wphb' ),
				'successGravatarPurge'    => __( 'Gravatar cache purged.', 'wphb' ),
				'successPageCachePurge'   => __( 'Page cache purged.', 'wphb' ),
				'errorRecheckStatus'      => __( 'There was an error re-checking the caching status, please try again later.', 'wphb' ),
				'successRecheckStatus'    => __( 'Browser caching status updated.', 'wphb' ),
				'successCloudflarePurge'  => __( 'Cloudflare cache successfully purged. Please wait 30 seconds for the purge to complete.', 'wphb' ),
				'successRedisPurge'       => __( 'Your cache has been cleared.', 'wphb' ),
				'selectZone'              => __( 'Select zone', 'wphb' ),
				/* Misc */
				'errorSettingsUpdate'     => __( 'Error updating settings', 'wphb' ),
				'errorEmptyName'          => __( 'Error: Please enter your name', 'wphb' ),
				'successUpdate'           => __( 'Settings updated', 'wphb' ),
				'deleteAll'               => __( 'Delete All Permanently', 'wphb' ),
				'dbDeleteButton'          => __( 'Delete permanently', 'wphb' ),
				'dbDeleteDraftsButton'    => __( 'Clear draft posts', 'wphb' ),
				'db_delete'               => __( 'Are you sure you wish to delete', 'wphb' ),
				'dbDeleteDrafts'          => __( 'Are you sure you want to clear draft posts and move them to the trash? Trashed posts can be permanently deleted below.', 'wphb' ),
				'db_entries'              => __( 'database entries', 'wphb' ),
				'db_backup'               => __( 'Make sure you have a current backup just in case.', 'wphb' ),
				'dismissLabel'            => __( 'Dismiss', 'wphb' ),
				'successAdvPurgeCache'    => __( 'Preload cache purged successfully.', 'wphb' ),
				'successAdvPurgeMinify'   => __( 'All database data and Custom Post Type information related to Asset Optimization has been cleared successfully.', 'wphb' ),
				'successAoOrphanedPurge'  => __( 'Database entries removed successfully.', 'wphb' ),
				/* Cloudflare */
				'CloudflareHelpAPItoken'  => __( 'Need help getting your API token?', 'wphb' ),
				'CloudflareHelpAPIkey'    => __( 'Need help getting your Global API key?', 'wphb' ),
				/* Notifications */
				'removeRecipient'         => __( 'Remove recipient', 'wphb' ),
				'noRecipients'            => __( "You've not added the users. In order to activate the notification you need to add users first.", 'wphb' ),
				'noRecipientDisable'      => __( "You've removed all recipients. If you save without a recipient, we'll automatically turn off notifications.", 'wphb' ),
				'recipientExists'         => __( 'Recipient already exists.', 'wphb' ),
				'awaitingConfirmation'    => __( 'Awaiting confirmation', 'wphb' ),
				'resendInvite'            => __( 'Resend invite email', 'wphb' ),
				'addRecipient'            => __( 'Add recipient', 'wphb' ),
				'successCriticalCssPurge' => __( 'Cache purged. Regenerating Critical CSS, this could take about a minute.', 'wphb' ),
				'criticalGeneratedNotice' => __( 'Critical CSS generated. Please visit the site and let the cache build up before running a test.', 'wphb' ),
				'errorCriticalCssPurge'   => __( 'There was an error during the critical css files purge. Check folder permissions are 755 for /wp-content/wphb-cache/critical-css or delete directory manually.', 'wphb' ),
				'enableCriticalCss'       => __( 'Settings updated. Generating Critical CSS, this could take about a minute.', 'wphb' ),
			),
			'links'      => array(
				'audits'        => self::get_admin_menu_url( 'performance' ),
				'eoUrl'         => self::get_admin_menu_url( 'minification' ) . '&view=tools',
				'tutorials'     => self::get_admin_menu_url( 'tutorials' ),
				'notifications' => self::get_admin_menu_url( 'notifications' ),
				'disableUptime' => add_query_arg(
					array(
						'action'   => 'disable',
						'_wpnonce' => wp_create_nonce( 'wphb-toggle-uptime' ),
					),
					self::get_admin_menu_url( 'uptime' )
				),
				'resetSettings' => add_query_arg( 'wphb-clear', 'all', self::get_admin_menu_url() ),
			),
		);

		$minify_module  = self::get_module( 'minify' );
		$is_scanning    = $minify_module->scanner->is_scanning();
		$get_ao_stats   = self::get_ao_stats_data();
		$minify_options = $minify_module->get_options();

		if ( $minify_module->is_on_page() || $is_scanning ) {
			$i10n = array_merge_recursive(
				$i10n,
				array(
					'minification' => array(
						'criticalStatusForQueue'     => self::get_module( 'critical_css' )->critical_css_status_for_queue(),
						'gutenbergUpgradeCTAUrl'     => self::get_link( 'plugin', 'hummingbird_criticalcss_gutenberg' ),
						'is'                         => array(
							'scanning' => $is_scanning,
							'scanned'  => $minify_module->scanner->is_files_scanned(),
						),
						'get'                        => array(
							'currentScanStep' => $minify_module->scanner->get_current_scan_step(),
							'totalSteps'      => $minify_module->scanner->get_scan_steps(),
							'showCDNModal'    => ! is_multisite(),
							'showSwitchModal' => (bool) get_option( 'wphb-minification-show-config_modal' ),
						),
					),
					'strings'      => array(
						'aoSettingsSaved' => __( 'Your changes have been published. Note: Files queued for compression will generate once someone visits your homepage.', 'wphb' ),
					),
					'links'        => array(
						'minification' => self::get_admin_menu_url( 'minification' ),
					),
					'stats'        => array(
						'assetsFound'        => $get_ao_stats['enqueued_files'],
						'type'               => $minify_options['type'],
						'totalFiles'         => self::minified_files_count(),
						'filesizeReductions' => absint( $get_ao_stats['compressed_size'] ),
					),
				)
			);
		}

		if ( ! apply_filters( 'wpmudev_branding_hide_doc_link', false ) && $minify_module->is_on_page( true ) ) {
			wp_enqueue_script( 'wphb-react-tutorials', WPHB_DIR_URL . 'admin/assets/js/wphb-react-tutorials.min.js', array( 'wp-i18n' ), WPHB_VERSION, true );
		}

		$i10n = array_merge_recursive(
			$i10n,
			self::get_tracking_data()
		);

		wp_localize_script( 'wphb-admin', 'wphb', $i10n );
	}

	/**
	 * Generate all tracking data for use in JS/React scripts.
	 *
	 * @since 3.3.1 Moved out from enqueue_admin_scripts().
	 *
	 * @return array
	 */
	public static function get_tracking_data() {
		global $wpdb, $wp_version;

		return array(
			'mixpanel' => array(
				'enabled'        => Settings::get_setting( 'tracking', 'settings' ),
				'plugin'         => 'Hummingbird',
				'plugin_type'    => self::is_member() ? 'pro' : 'free',
				'plugin_version' => WPHB_VERSION,
				'wp_version'     => $wp_version,
				'wp_type'        => is_multisite() ? 'multisite' : 'single',
				'locale'         => get_locale(),
				'active_theme'   => wp_get_theme()->get( 'Name' ),
				'php_version'    => PHP_VERSION,
				'mysql_version'  => $wpdb->db_version(),
				'server_type'    => Module_Server::get_server_type(),
			),
		);
	}

	/**
	 * Returns Jed-formatted localization data
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_locale_data() {
		$translations = get_translations_for_domain( 'wphb' );

		$locale = array(
			'' => array(
				'domain' => 'wphb',
				'lang'   => is_admin() ? get_user_locale() : get_locale(),
			),
		);

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale[ $msgid ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Return the needed capability for admin pages.
	 *
	 * @return string
	 */
	public static function get_admin_capability() {
		$cap              = 'manage_options';
		$is_network_admin = is_network_admin() || self::is_ajax_network_admin();

		if ( is_multisite() && $is_network_admin ) {
			$cap = 'manage_network';
		}

		return apply_filters( 'wphb_admin_capability', $cap );
	}

	/**
	 * Get Current username info
	 */
	public static function get_current_user_name() {
		$current_user = wp_get_current_user();

		if ( ! ( $current_user instanceof WP_User ) ) {
			return false;
		}

		if ( ! empty( $current_user->user_firstname ) ) { // First we try to grab user First Name.
			return $current_user->user_firstname;
		}

		return $current_user->user_nicename;
	}

	/**
	 * This function will calculate the sum of file sizes in an array.
	 *
	 * We need this, because Asset Optimization module will store 'original_size' and 'compressed_size' values as
	 * strings, and such strings will contain &nbsp; instead of spaces, thus making it impossible to sum all the
	 * values with array_sum().
	 *
	 * @since 1.9.2
	 *
	 * @param array $arr  Array of items with sizes as strings.
	 *
	 * @return float
	 */
	public static function calculate_sum( $arr ) {
		$sum = 0;

		// Get separators from locale. Some Windows servers will return blank values.
		$locale        = localeconv();
		$thousands_sep = isset( $locale['thousands_sep'] ) ? $locale['thousands_sep'] : ',';
		$decimal_point = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

		foreach ( $arr as $value ) {
			if ( is_null( $value ) ) {
				continue;
			}

			// Remove spaces.
			$sum += (float) str_replace(
				array( '&nbsp;', $thousands_sep, $decimal_point ),
				array( '', '', '.' ),
				$value
			);
		}

		return $sum;
	}

	/**
	 * Return the file size in a humanly readable format.
	 *
	 * Taken from http://www.php.net/manual/en/function.filesize.php#91477
	 *
	 * @since 2.0.0
	 *
	 * @param int $bytes      Number of bytes.
	 * @param int $precision  Precision.
	 *
	 * @return string
	 */
	public static function format_bytes( $bytes, $precision = 1 ) {
		$units  = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 * Convert seconds to a readable value.
	 *
	 * @since 2.0.0
	 *
	 * @param int $seconds  Number of seconds.
	 *
	 * @return string
	 */
	public static function format_interval( $seconds ) {
		if ( 3600 <= $seconds && 86400 > $seconds ) {
			return floor( $seconds / HOUR_IN_SECONDS ) . ' h';
		}

		if ( 86400 <= $seconds && 2419200 > $seconds ) {
			return floor( $seconds / DAY_IN_SECONDS ) . ' d';
		}

		if ( 2419200 <= $seconds && 31536000 > $seconds ) {
			return floor( $seconds / MONTH_IN_SECONDS ) . ' m';
		}

		if ( 31536000 < $seconds && 26611200 >= $seconds ) {
			return floor( $seconds / YEAR_IN_SECONDS ) . ' y';
		}

		return '-';
	}

	/**
	 * Format hours into days.
	 *
	 * @since 2.1.0
	 *
	 * @param int $hours  Number of hours.
	 *
	 * @return array
	 */
	public static function format_interval_hours( $hours ) {
		if ( $hours <= 24 ) {
			return array( $hours, 'hours' );
		}

		$days = floor( $hours / 24 );
		return array( $days, 'days' );
	}

	/**
	 *  Check if network admin.
	 *
	 * The is_network_admin() check does not work in AJAX calls.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_ajax_network_admin() {
		if ( ! is_multisite() ) {
			return false;
		}

		return defined( 'DOING_AJAX' ) && DOING_AJAX && self::is_referrer_network_admin(); // Input var ok.
	}

	/**
	 * Check if network admin url in ajax call.
	 *
	 * @return bool
	 */
	public static function is_referrer_network_admin() {
		return isset( $_SERVER['HTTP_REFERER'] ) && preg_match( '#^' . network_admin_url() . '#i', wp_unslash( $_SERVER['HTTP_REFERER'] ) ); // Input var ok.
	}

	/***************************
	 *
	 * II. Layout functions
	 * get_whitelabel_class()
	 ***************************/

	/**
	 * Return rebranded class.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	public static function get_whitelabel_class() {
		if ( ! apply_filters( 'wpmudev_branding_hide_branding', false ) ) {
			return '';
		}

		return apply_filters( 'wpmudev_branding_hero_image', '' ) ? 'sui-rebranded' : 'sui-unbranded';
	}

	/***************************
	 *
	 * III. Time and date functions
	 * human_read_time_diff()
	 * get_days_of_week()
	 * get_times()
	 * get_timezone_string()
	 ***************************/

	/**
	 * Credits to: http://stackoverflow.com/a/11389893/1502521
	 *
	 * @param int $seconds  Seconds.
	 *
	 * @return string
	 */
	public static function human_read_time_diff( $seconds ) {
		if ( ! $seconds ) {
			return __( 'Disabled', 'wphb' );
		}

		$minutes = 0;
		$hours   = 0;
		$days    = 0;
		$months  = 0;
		$years   = 0;

		while ( $seconds >= YEAR_IN_SECONDS ) {
			$years ++;
			$seconds = $seconds - YEAR_IN_SECONDS;
		}

		while ( $seconds >= MONTH_IN_SECONDS ) {
			$months ++;
			$seconds = $seconds - MONTH_IN_SECONDS;
		}

		while ( $seconds >= DAY_IN_SECONDS ) {
			$days ++;
			$seconds = $seconds - DAY_IN_SECONDS;
		}

		while ( $seconds >= HOUR_IN_SECONDS ) {
			$hours++;
			$seconds = $seconds - HOUR_IN_SECONDS;
		}

		while ( $seconds >= MINUTE_IN_SECONDS ) {
			$minutes++;
			$seconds = $seconds - MINUTE_IN_SECONDS;
		}

		$diff = new stdClass();

		$diff->y = $years;
		$diff->m = $months;
		$diff->d = $days;
		$diff->h = $hours;
		$diff->i = $minutes;
		$diff->s = $seconds;

		if ( $diff->y || ( 11 === $diff->m && 30 <= $diff->d ) ) {
			$years = $diff->y;
			if ( 11 === $diff->m && 30 <= $diff->d ) {
				$years++;
			}
			/* translators: %d: year */
			$diff_time = sprintf( _n( '%d year', '%d years', $years, 'wphb' ), $years );
		} elseif ( $diff->m ) {
			/* translators: %d: month */
			$diff_time = sprintf( _n( '%d month', '%d months', $diff->m, 'wphb' ), $diff->m );
		} elseif ( $diff->d ) {
			/* translators: %d: day */
			$diff_time = sprintf( _n( '%d day', '%d days', $diff->d, 'wphb' ), $diff->d );
		} elseif ( $diff->h ) {
			/* translators: %d: hour */
			$diff_time = sprintf( _n( '%d hour', '%d hours', $diff->h, 'wphb' ), $diff->h );
		} elseif ( $diff->i ) {
			/* translators: %d: minute */
			$diff_time = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'wphb' ), $diff->i );
		} else {
			/* translators: %d: second */
			$diff_time = sprintf( _n( '%d second', '%d seconds', $diff->s, 'wphb' ), $diff->s );
		}

		return $diff_time;
	}

	/**
	 * Get days of the week.
	 *
	 * @since 1.4.5
	 *
	 * @return mixed
	 */
	public static function get_days_of_week() {
		$timestamp = date_create( 'next Monday' );
		if ( 7 === get_option( 'start_of_week' ) ) {
			$timestamp = date_create( 'next Sunday' );
		}
		$days = array();
		for ( $i = 0; $i < 7; $i ++ ) {
			$days[]    = date_format( $timestamp, 'l' );
			$timestamp = date_modify( $timestamp, '+1 day' );
		}

		return apply_filters( 'wphb_scan_get_days_of_week', $days );
	}

	/**
	 * Return times frame for select box
	 *
	 * @since 1.4.5
	 *
	 * @return mixed
	 */
	public static function get_times() {
		$data = array();
		for ( $i = 0; $i < 24; $i ++ ) {
			foreach ( apply_filters( 'wphb_scan_get_times_interval', array( '00' ) ) as $min ) {
				$time          = $i . ':' . $min;
				$data[ $time ] = apply_filters( 'wphb_scan_get_times_hour_min', $time );
			}
		}

		return apply_filters( 'wphb_scan_get_times', $data );
	}

	/**
	 * Return time zone string.
	 *
	 * @since 3.1.1
	 *
	 * @return string
	 */
	public static function get_timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists.
			if ( 0 === $current_offset ) {
				$tzstring = 'UTC+0';
			} elseif ( $current_offset < 0 ) {
				$tzstring = 'UTC' . $current_offset;
			} else {
				$tzstring = 'UTC+' . $current_offset;
			}
		}

		return $tzstring;
	}

	/***************************
	 *
	 * IV. Link and url functions
	 * get_link()
	 * get_documentation_url()
	 * still_having_trouble_link()
	 * get_admin_menu_url()
	 * get_avatar_url()
	 ***************************/

	/**
	 * Return URL link.
	 *
	 * @param string $link_for      Accepts: 'chat', 'plugin', 'support', 'smush', 'docs'.
	 * @param string $campaign      Utm campaign tag to be used in link. Default: 'hummingbird_pro_modal_upgrade'.
     * @param string $hub_campaign  Utm campaign tag to be used to redirect to HUb site.
	 *
	 * @return string
	 */
	public static function get_link( $link_for, $campaign = 'hummingbird_pro_modal_upgrade', $hub_campaign = '' ) {
		$domain   = 'https://wpmudev.com';
		$wp_org   = 'https://wordpress.org';
		$utm_tags = "?utm_source=hummingbird&utm_medium=plugin&utm_campaign=$campaign";

		if ( defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER ) {
			$domain = WPMUDEV_CUSTOM_API_SERVER;
		}

		switch ( $link_for ) {
			case 'configs':
				$link = "$domain/hub2/configs/my-configs";
				break;
			case 'hub-welcome':
				$link = "$domain/hub-welcome/$utm_tags";
				break;
			case 'chat':
				$link = "$domain/live-support/$utm_tags";
				break;
			case 'plugin':
				$link = "$domain/project/wp-hummingbird/$utm_tags";
				break;
			case 'support':
				if ( self::is_member() ) {
					$link = "$domain/hub2/support/#get-support";
				} else {
					$link = "$wp_org/support/plugin/hummingbird-performance";
				}
				break;
			case 'docs':
				$link = "$domain/docs/wpmu-dev-plugins/hummingbird/$utm_tags";
				break;
			case 'smush':
				if ( self::is_member() ) {
					// Return the pro plugin URL.
					$url  = WPMUDEV_Dashboard::$ui->page_urls->plugins_url;
					$link = $url . '#pid=912164';
				} else {
					// Return the free URL.
					$link = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-smushit' ), 'install-plugin_wp-smushit' );
				}
				break;
			case 'smush-plugin':
				$link = "$domain/project/wp-smush-pro/$utm_tags";
				break;
			case 'hosting':
				$get_random_cta = self::get_random_hosting_cta();
				$link           = "$domain/$get_random_cta/$utm_tags&coupon=HUMMINGBIRD-HOSTING-1M&from_checkout=1";
				break;
			case 'wpmudev':
				$link = "$domain/$utm_tags";
				break;
			case 'tutorials':
				$link = "$domain/blog/tutorials/tutorial-category/hummingbird-pro/$utm_tags";
				break;
			case 'tracking':
				$link = "$domain/docs/privacy/our-plugins/#usage-tracking";
				break;
			case 'wpmudev-login':
				$link = "$domain/login?signin=$hub_campaign&hummingbird_url=" . site_url();
				break;
			case 'connect-url':
				$link = self::connect_url( $domain, ltrim( $utm_tags, '?' ) );
				break;
			default:
				$link = '';
				break;
		}

		return $link;
	}

	/**
	 * Returns the random CTA.
	 *
	 * @return string
	 */
	public static function get_random_hosting_cta() {
		$hosting_cta = array( 'hosting', 'register' );
		$random_keys = array_rand( $hosting_cta, 1 );

		return $hosting_cta[ $random_keys ];
	}

	/**
	 * Returns the signup url.
	 * If Dashboard plugin is active the signup url returned will be the Dashboard signup page. Else Hub signup page.
	 *
	 * @param string $domain   Domain name.
	 * @param string $utm_tags UTM Tags.
	 * @return string
	 */
	public static function connect_url( $domain, $utm_tags ) {
		if ( self::is_dash_plugin_active_and_disconnected() ) {

			return add_query_arg(
				array(
					'page' => 'wpmudev',
				),
				is_multisite() ? network_admin_url() : get_admin_url()
			) . '&' . $utm_tags;
		}

		return $domain . '/hub2/connect?' . $utm_tags;
	}

	/**
	 * Check if Dash plugin is active and disconnected.
	 *
	 * @return bool
	 */
	public static function is_dash_plugin_active_and_disconnected() {
		return class_exists( 'WPMUDEV_Dashboard' ) && ! WPMUDEV_Dashboard::$api->has_key();
	}

	/**
	 * Get documentation URL.
	 *
	 * @since 1.7.0
	 *
	 * @param string $page  Page slug.
	 * @param string $view  View slug.
	 *
	 * @return string
	 */
	public static function get_documentation_url( $page, $view = '' ) {
		switch ( $page ) {
			case 'wphb-performance':
				if ( 'reports' === $view ) {
					$anchor = '#reporting';
				} elseif ( 'settings' === $view ) {
					$anchor = '#performance-test-settings';
				} else {
					$anchor = '#performance-test';
				}
				break;
			case 'wphb-caching':
				$anchor = '#caching';
				break;
			case 'wphb-gzip':
				$anchor = '#gzip-compression';
				break;
			case 'wphb-minification':
				$anchor = '#asset-optimization';
				break;
			case 'wphb-advanced':
				$anchor = '#advanced-tools';
				break;
			case 'wphb-uptime':
				$anchor = '#uptime';
				break;
			case 'wphb-settings':
				$anchor = '#settings';
				break;
			case 'wphb-notifications':
				$anchor = '#notifications';
				break;
			default:
				$anchor = '';
		}

		return 'https://wpmudev.com/docs/wpmu-dev-plugins/hummingbird/' . $anchor;
	}

	/**
	 * Display start a live chat link for pro user or open support ticket for non-pro user.
	 */
	public static function still_having_trouble_link() {
		esc_html_e( 'Still having trouble? ', 'wphb' );
		if ( self::is_member() && ! apply_filters( 'wpmudev_branding_hide_branding', false ) ) :
			?>
			<a target="_blank" href="<?php echo esc_url( self::get_link( 'chat' ) ); ?>">
				<?php esc_html_e( 'Start a live chat.', 'wphb' ); ?>
			</a>
		<?php else : ?>
			<a target="_blank" href="<?php echo esc_url( self::get_link( 'support' ) ); ?>">
				<?php esc_html_e( 'Open a support ticket.', 'wphb' ); ?>
			</a>
			<?php
		endif;
	}

	/**
	 * Get url for plugin module page.
	 *
	 * @param string $page  Page.
	 *
	 * @return string
	 */
	public static function get_admin_menu_url( $page = '' ) {
		$hummingbird = WP_Hummingbird::get_instance();

		if ( is_object( $hummingbird->admin ) ) {
			$page_slug = empty( $page ) ? 'wphb' : 'wphb-' . $page;
			$page      = $hummingbird->admin->get_admin_page( $page_slug );
			if ( $page ) {
				return $page->get_page_url();
			}
		}

		return '';
	}

	/**
	 * Get avatar URL.
	 *
	 * @since 1.4.5
	 *
	 * @param string $get_avatar User email.
	 *
	 * @return mixed
	 */
	public static function get_avatar_url( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );

		return $matches[1];
	}

	/***************************
	 *
	 * V. Modules functions
	 * get_api()
	 * pro()
	 * admin()
	 * get_modules()
	 * get_module()
	 * get_active_cache_modules()
	 * get_number_of_issues()
	 * minified_files_count()
	 * remove_quick_setup()
	 ***************************/

	/**
	 * Get API.
	 *
	 * @return Api\API
	 */
	public static function get_api() {
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->core->api;
	}

	/**
	 * Get PRO.
	 */
	public static function pro() {
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->pro;
	}

	/**
	 * Get admin.
	 *
	 * @since 3.3.1
	 */
	public static function admin() {
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->admin;
	}

	/**
	 * Return the list of modules and their object instances
	 *
	 * Do not try to load before 'wp_hummingbird_loaded' action has been executed
	 *
	 * @return array
	 */
	private static function get_modules() {
		$hummingbird = WP_Hummingbird::get_instance();
		return $hummingbird->core->modules;
	}

	/**
	 * Get a module instance
	 *
	 * @param string $module Module slug.
	 *
	 * @return bool|Module|Modules\Page_Cache|Modules\GZip|Modules\Minify|Modules\Cloudflare|Modules\Uptime|Modules\Performance|Modules\Advanced|Modules\Redis|Modules\Caching
	 */
	public static function get_module( $module ) {
		$modules = self::get_modules();
		return isset( $modules[ $module ] ) ? $modules[ $module ] : false;
	}

	/**
	 * Return human-readable names of active modules that have a cache.
	 *
	 * Checks Page, Gravatar & Asset Optimization.
	 *
	 * @return array
	 */
	public static function get_active_cache_modules() {
		$modules = array(
			'page_cache' => __( 'Page Cache', 'wphb' ),
			'cloudflare' => __( 'Cloudflare', 'wphb' ),
			'gravatar'   => __( 'Gravatar Cache', 'wphb' ),
			'minify'     => __( 'Asset Optimization Cache', 'wphb' ),
			'redis'      => __( 'Redis Cache', 'wphb' ),
		);

		$hb_modules = self::get_modules();

		foreach ( $modules as $module => $module_name ) {
			// If inactive, skip to next step.
			if ( 'cloudflare' !== $module && isset( $hb_modules[ $module ] ) && ! $hb_modules[ $module ]->is_active() ) {
				unset( $modules[ $module ] );
			}

			// Fix Cloudflare clear cache appearing on dashboard if it had been previously enabled but then uninstalled and reinstalled HB.
			// TODO: do we need this?
			if ( 'cloudflare' === $module && isset( $hb_modules[ $module ] ) && ! $hb_modules[ $module ]->is_connected() && ! $hb_modules[ $module ]->is_zone_selected() ) {
				unset( $modules[ $module ] );
			}
		}

		return $modules;
	}

	/**
	 * Get the number of issues for selected module
	 *
	 * @since 1.8.1 Added $report parameter.
	 *
	 * @param string     $module Module name.
	 * @param bool|array $report Current report.
	 *
	 * @return int
	 */
	public static function get_number_of_issues( $module, $report = false ) {
		$issues = 0;

		switch ( $module ) {
			case 'caching':
				$mod = self::get_module( $module );

				if ( ! $report ) {
					$mod->get_analysis_data();
					$report = $mod->status;
				}

				// No report - break.
				if ( ! $report ) {
					break;
				}

				$recommended = $mod->get_recommended_caching_values();
				foreach ( $report as $type => $value ) {
					$t = strtolower( $type );
					if ( empty( $value ) || $recommended[ $t ]['value'] > $value ) {
						$issues++;
					}
					unset( $t );
				}
				break;
			case 'gzip':
				if ( ! $report ) {
					$mod = self::get_module( $module );
					$mod->get_analysis_data();
					$report = $mod->status;
				}

				// No report - break.
				if ( ! $report ) {
					break;
				}

				$invalid = 0;
				foreach ( $report as $type ) {
					if ( ! $type || 'privacy' === $type ) {
						$invalid++;
					}
				}

				$issues = $invalid;
				break;
		}

		return $issues;
	}

	/**
	 * Checks if current page is admin dashboard.
	 *
	 * @return boolean
	 */
	public static function is_admin_dashboard() {
		if ( is_network_admin() || is_main_site() ) {
			return function_exists( 'get_current_screen' ) && in_array( get_current_screen()->id, array( 'dashboard', 'dashboard-network' ), true );
		}

		return false;
	}

	/**
	 * Return the number of files used by minification.
	 *
	 * @since 1.4.5
	 *
	 * @param bool $only_minified  Only minified files.
	 *
	 * @return int
	 */
	public static function minified_files_count( $only_minified = false ) {
		$minify_module = self::get_module( 'minify' );

		// Get files count.
		$collection = $minify_module->get_resources_collection();
		// Remove those assets that we don't want to display.
		foreach ( $collection['styles'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
				unset( $collection['styles'][ $key ] );
			}

			// Keep only minified files.
			if ( $only_minified && ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
				unset( $collection['styles'][ $key ] );
			}
		}
		foreach ( $collection['scripts'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
				unset( $collection['scripts'][ $key ] );
			}

			// Kepp only minified files.
			if ( $only_minified && ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
				unset( $collection['scripts'][ $key ] );
			}
		}

		return ( count( $collection['scripts'] ) + count( $collection['styles'] ) );
	}

	/**
	 * Returns a list of incompatible plugins if any
	 *
	 * @return array
	 */
	public static function get_incompat_plugin_list() {
		$plugins         = array();
		$caching_plugins = array(
			'autoptimize/autoptimize.php'               => 'Autoptimize',
			'litespeed-cache/litespeed-cache.php'       => 'LiteSpeed Cache',
			'speed-booster-pack/speed-booster-pack.php' => 'Speed Booster Pack',
			'swift-performance-lite/performance.php'    => 'Swift Performance Lite',
			'w3-total-cache/w3-total-cache.php'         => 'W3 Total Cache',
			'wp-fastest-cache/wpFastestCache.php'       => 'WP Fastest Cache',
			'wp-optimize/wp-optimize.php'               => 'WP-Optimize',
			'wp-performance-score-booster/wp-performance-score-booster.php' => 'WP Performance Score Booster',
			'wp-performance/wp-performance.php'         => 'WP Performance',
			'wp-super-cache/wp-cache.php'               => 'WP Super Cache',
		);

		foreach ( $caching_plugins as $plugin => $plugin_name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugins[ $plugin ] = $plugin_name;
			}
		}

		return $plugins;
	}

	/**
	 * Returns AO stats data.
	 *
	 * @return array
	 */
	public static function get_ao_stats_data() {
		$minify_module = self::get_module( 'minify' );
		$collection    = $minify_module->get_resources_collection();

		// Remove those assets that we don't want to display.
		foreach ( $collection['styles'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' )
				|| ! isset( $item['original_size'], $item['compressed_size'] ) ) {
				unset( $collection['styles'][ $key ] );
			}
		}
		foreach ( $collection['scripts'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' )
				|| ! isset( $item['original_size'], $item['compressed_size'] ) ) {
				unset( $collection['scripts'][ $key ] );
			}
		}

		$enqueued_files = count( $collection['scripts'] ) + count( $collection['styles'] );

		$original_size_styles  = self::calculate_sum( wp_list_pluck( $collection['styles'], 'original_size' ) );
		$original_size_scripts = self::calculate_sum( wp_list_pluck( $collection['scripts'], 'original_size' ) );

		$original_size = $original_size_scripts + $original_size_styles;

		$compressed_size_styles  = self::calculate_sum( wp_list_pluck( $collection['styles'], 'compressed_size' ) );
		$compressed_size_scripts = self::calculate_sum( wp_list_pluck( $collection['scripts'], 'compressed_size' ) );
		$compressed_size         = $compressed_size_scripts + $compressed_size_styles;

		if ( ( $original_size_scripts + $original_size_styles ) <= 0 ) {
			$percentage = 0;
		} else {
			$percentage = 100 - (int) $compressed_size * 100 / (int) $original_size;
		}
		$percentage = number_format_i18n( $percentage, 1 );

		$compressed_size_styles  = number_format( $original_size_styles - $compressed_size_styles, 0 );
		$compressed_size_scripts = number_format( $original_size_scripts - $compressed_size_scripts, 0 );

		// Internalization numbers.
		$original_size   = number_format_i18n( $original_size, 1 );
		$compressed_size = number_format_i18n( $compressed_size, 1 );

		$data = compact( 'enqueued_files', 'original_size', 'compressed_size', 'compressed_size_scripts', 'compressed_size_styles', 'percentage' );

		return $data;
	}

	/**
	 * Returns HB active features.
	 *
	 * @return array
	 */
	public static function get_active_features() {
		$active_features  = array();
		$minify_options   = self::get_module( 'minify' )->get_options();
		$advanced_options = self::get_module( 'advanced' )->get_options();

		// CDN.
		if ( self::get_module( 'minify' )->is_active() && $minify_options['use_cdn'] ) {
			$active_features[] = 'CDN';
		}

		// Critical CSS.
		if ( self::get_module( 'minify' )->is_active() && ! empty( $minify_options['critical_css'] ) ) {
			$active_features[] = 'Critical CSS';
		}

		// Delay.
		if ( self::get_module( 'minify' )->is_active() && ! empty( $minify_options['delay_js'] ) ) {
			$active_features[] = 'JS Delay';
		}

		// AO_Speedy','AO_Basic','AO_Manual'.
		if ( self::get_module( 'minify' )->is_active() ) {
			if ( 'advanced' == $minify_options['view'] ) {
				$active_features[] = 'AO_Manual';
			} else {
				if ( 'speedy' == $minify_options['type'] ) {
					$active_features[] = 'AO_Speedy';
				} else {
					$active_features[] = 'AO_Basic';
				}
			}
		}

		// GZip.
		if ( self::get_module( 'gzip' )->is_active() ) {
			$active_features[] = 'GZip';
		}

		// Gravatar.
		if ( self::get_module( 'gravatar' )->is_active() ) {
			$active_features[] = 'Gravatar';
		}

		// Caching.
		if ( self::get_module( 'caching' )->is_active() ) {
			$active_features[] = 'Caching';
		}

		// Page Caching.
		if ( self::get_module( 'page_cache' )->is_active() ) {
			$active_features[] = 'Page Caching';
		}

		// Redis Cache.
		if ( self::get_module( 'redis' )->is_active() ) {
			$active_features[] = 'Redis Cache';
		}

		// RSS Caching.
		if ( self::get_module( 'rss' )->is_active() ) {
			$active_features[] = 'RSS Caching';
		}

		// Cloudflare_integration.
		if ( self::get_module( 'cloudflare' )->is_active() ) {
			$active_features[] = 'Cloudflare_integration';
		}

		// Lazy_comments (Advanced tools).
		if ( isset( $advanced_options['lazy_load'] ) && $advanced_options['lazy_load']['enabled'] ) {
			$active_features[] = 'lazy_comments';
		}
		// Remove_query_strings (Advanced tools).
		if ( ! empty( $advanced_options['query_string'] ) ) {
			$active_features[] = 'remove_query_strings';
		}
		// Disable_cart_fragments (Advanced tools).
		if ( ! empty( $advanced_options['cart_fragments'] ) ) {
			$active_features[] = 'disable_cart_fragments';
		}
		// Remove_emojis (Advanced tools).
		if ( ! empty( $advanced_options['emoji'] ) ) {
			$active_features[] = 'remove_emojis';
		}
		// Prefetch_dns (Advanced tools).
		if ( ! empty( $advanced_options['prefetch'] ) ) {
			$active_features[] = 'prefetch_dns';
		}
		// Preconnect_domains (Advanced tools).
		if ( ! empty( $advanced_options['preconnect'] ) ) {
			$active_features[] = 'preconnect_domains';
		}

		/**
		 * Filters the HB active features for MP.
		 *
		 * @since 3.6.0
		 *
		 * @param array $active_features An array of active features.
		 *
		 * @return array
		 */
		$active_features = apply_filters( 'wphb_tracking_active_features', $active_features );

		return $active_features;
	}

	/**
	 * Check if page builder is active.
	 *
	 * @return bool
	 */
	public static function wphb_is_page_builder() {
		$page_builders = apply_filters(
			'wphb_page_builders',
			array(
				'elementor-preview', // Elementor.
				'cs_preview_state', // Cornerstone Builder.
				'fl_builder', // Beaver builder.
				'et_fb', // Divi.
				'ct_builder', // Oxygen.
				'tve', // Thrive.
				'app', // flatsome.
				'uxb_iframe',
				'fb-edit', // fusion builder.
				'builder',
				'bricks', // bricks.
				'vc_editable', // wp bakery.
			)
		);

		if ( ! empty( $page_builders ) ) {
			foreach ( $page_builders as $page_builder ) {
				if ( isset( $_GET[ $page_builder ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns current user name to be displayed.
	 *
	 * @return string
	 */
	public static function get_user_name() {
		$current_user = wp_get_current_user();

		return ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
	}

	/**
	 * Display unlock pro upsell link.
	 *
	 * @param string $location   Location of the unlock pro upsell.
	 * @param string $utm        URM for Upsell.
	 * @param string $event_name Event name for MP.
	 * @param string $display    Whether to echo or return the link. Default true.
	 */
	public static function unlock_now_link( $location, $utm, $event_name, $display = true ) {
		$html = sprintf(
			'<a target="_blank" data-location="%1$s" href="%2$s" data-eventname="%3$s" id="%4$s" class="wphb-upsell-link wphb-upsell-eo" onclick="WPHB_Admin.minification.hbTrackEoMPEvent( this )">
				%5$s
				<span class="sui-icon-open-new-window" aria-hidden="true"></span>
			</a>',
			esc_attr( $location ),
			esc_url( self::get_link( 'plugin', $utm ) ),
			$event_name,
			'legacy_switch' === $location ? 'manual_css_switch_now' : '',
			esc_html__( 'Unlock now', 'wphb' )
		);

		if ( $display ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Checks whether AMP content is being served.
	 *
	 * @since 3.7.0
	 *
	 * @return bool True if an AMP request, false otherwise.
	 */
	public static function is_amp() {
		if ( is_singular( 'web-story' ) ) {
			return true;
		}

		// amp_is_request For AMP plugin v2.0+ and is_amp_endpoint For older/other AMP plugins.
		return ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) || ( function_exists( 'amp_is_request' ) && amp_is_request() );
	}

	/**
	 * Returns plugin discount.
	 *
	 * @since 3.7.1
	 *
	 * @return string
	 */
	public static function get_plugin_discount() {
		return self::HB_PLUGIN_DISCOUNT . '%';
	}
}
