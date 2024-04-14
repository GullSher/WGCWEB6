<?php
/**
 * Provides connection to WPMU API to perform queries against Hosting endpoints.
 *
 * @sice 3.3.1
 *
 * @package Hummingbird
 */

namespace Hummingbird\Core\Api\Service;

use Hummingbird\Core\Api\Exception;
use Hummingbird\Core\Api\Request\WPMUDEV;
use WP_Error;
use WPMUDEV_Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Hosting extends Service.
 */
class Hosting extends Service {
	/**
	 * Endpoint name.
	 *
	 * @var string $name
	 */
	public $name = 'hub';

	/**
	 * API version.
	 *
	 * @access private
	 *
	 * @var string $version
	 */
	private $version = 'v1';

	/**
	 * Performance constructor.
	 *
	 * @throws Exception  Exception.
	 */
	public function __construct() {
		$this->request = new WPMUDEV( $this );
	}

	/**
	 * Getter method for api version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get hosting info.
	 *
	 * @param int $site_id  Site ID.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function get_info( $site_id ) {
		return $this->request->get(
			'sites/' . $site_id . '/modules/hosting',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Disable FastCGI.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function disable_fast_cgi() {
		$site_id = $this->get_site_id();

		if ( ! $site_id ) {
			return false;
		}

		delete_site_transient( 'wphb-fast-cgi-enabled' );

		$this->request->add_post_argument( 'is_active', false );
		return $this->request->put(
			'sites/' . $site_id . '/modules/hosting/static-cache',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Get site ID from Dashboard plugin.
	 *
	 * @since 3.3.1
	 * @since 3.4.0 Moved here from Setup class.
	 *
	 * @return false|int
	 */
	private function get_site_id() {
		// Only check on WPMU DEV hosting.
		if ( ! isset( $_SERVER['WPMUDEV_HOSTED'] ) ) {
			return false;
		}

		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return false;
		}

		if ( ! method_exists( 'WPMUDEV_Dashboard_Api', 'get_site_id' ) ) {
			return false;
		}

		return WPMUDEV_Dashboard::$api->get_site_id();
	}

	/**
	 * Get FastCGI status.
	 *
	 * @since 3.4.0 Moved here from Setup class.
	 *
	 * @return bool
	 */
	public function has_fast_cgi() {
		$site_id = $this->get_site_id();

		if ( $site_id ) {
			$hosting = $this->get_info( $site_id );
			if ( is_object( $hosting ) && property_exists( $hosting, 'static_cache' ) ) {
				set_site_transient( 'wphb-fast-cgi-enabled', $hosting->static_cache->is_active, DAY_IN_SECONDS );
				return $hosting->static_cache->is_active;
			}
		}

		return false;
	}

	/**
	 * Check if there's a `x-cache` header on a request, which would mean FastCGI is enabled on a site.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public function has_fast_cgi_header() {
		// Only check on WPMU DEV hosting.
		if ( ! isset( $_SERVER['WPMUDEV_HOSTED'] ) ) {
			return false;
		}

		$fast_cgi_enabled = get_site_transient( 'wphb-fast-cgi-enabled' );

		if ( $fast_cgi_enabled ) {
			return $fast_cgi_enabled;
		}

		$head = wp_remote_head(
			home_url(),
			array(
				'sslverify' => false,
			)
		);

		if ( ! is_wp_error( $head ) ) {
			$headers = wp_remote_retrieve_headers( $head );
			if ( isset( $headers['x-cache'] ) ) {
				set_site_transient( 'wphb-fast-cgi-enabled', true, DAY_IN_SECONDS );
				return true;
			}
		}

		set_site_transient( 'wphb-fast-cgi-enabled', false, DAY_IN_SECONDS );
		return false;
	}

}
