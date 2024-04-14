<?php
/**
 * Provides connection to WPMU API to perform queries against performance endpoint.
 *
 * @package Hummingbird
 */

namespace Hummingbird\Core\Api\Service;

use Hummingbird\Core\Api\Exception;
use Hummingbird\Core\Api\Request\WPMUDEV;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Performance extends Service.
 */
class Performance extends Service {

	/**
	 * Endpoint name.
	 *
	 * @var string $name
	 */
	public $name = 'performance';

	/**
	 * API version.
	 *
	 * @access private
	 * @var    string $version
	 */
	private $version = 'v2';

	/**
	 * Timeout.
	 *
	 * @var int
	 */
	const CRITICAL_API_TIMEOUT = 120;

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
	 * Check if performance test has finished on server.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function check() {
		return $this->request->post(
			'site/check/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Ping to performance module, so it starts to gather data.
	 *
	 * @since 1.8.1 Changed timeout from 0.1 to 2 seconds.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function ping() {
		$this->request->set_timeout( 2 );
		return $this->request->post(
			'site/check/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Get the latest performance test results.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function results() {
		return $this->request->get(
			'site/result/latest/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Get the latest delayjs exclusion list.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function get_delayjs_exclusion() {
		return $this->request->get(
			'delay_js_exclusion_list/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Ignore the latest performance test results.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function ignore() {
		return $this->request->get(
			'site/result/ignore/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Test if GZIP is enabled.
	 *
	 * @since 1.6.0
	 * @return array|mixed|object|WP_Error
	 */
	public function check_gzip() {
		$domain = $this->request->get_this_site();

		$params = array(
			'html'       => $domain,
			'javascript' => WPHB_DIR_URL . 'core/modules/dummy/dummy-js.js',
			'css'        => WPHB_DIR_URL . 'core/modules/dummy/dummy-style.css',
		);

		return $this->request->post(
			'test/gzip/',
			array(
				'domain' => $domain,
				'tests'  => wp_json_encode( $params ),
			)
		);
	}

	/**
	 * Test if caching is enabled.
	 *
	 * @since 1.6.0
	 * @return array|mixed|object|WP_Error
	 */
	public function check_cache() {
		$params = array(
			'javascript' => WPHB_DIR_URL . 'core/modules/dummy/dummy-js.js',
			'css'        => WPHB_DIR_URL . 'core/modules/dummy/dummy-style.css',
			'media'      => WPHB_DIR_URL . 'core/modules/dummy/dummy-media.mp3',
			'images'     => WPHB_DIR_URL . 'core/modules/dummy/dummy-image.png',
		);

		return $this->request->post(
			'test/cache/',
			array(
				'domain' => $this->request->get_this_site(),
				'tests'  => wp_json_encode( $params ),
			)
		);
	}

	/**
	 * Designed to facilitate the process of obtaining Critical CSS for a given URL. It achieves this by connecting to a dedicated API service responsible for generating the Critical CSS content.
	 *
	 * @since 3.6.0
	 *
	 * @param array  $urls    URLs to generate critical CSS.
	 * @param string $type    Types of critical CSS generation: CRITICAL for above-the-fold and PURGE for the entire page's CSS.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function generate_critical_css( $urls, $type = 'CRITICAL' ) {
		$this->request->set_timeout( self::CRITICAL_API_TIMEOUT );

		return $this->request->post(
			'critical-css/calculate/',
			array(
				'domain' => $this->get_network_home_url_on_subsite(),
				'type'   => $type,
				'urls'   => $urls,
			)
		);
	}

	/**
	 * Designed to retrieve and obtain Critical CSS for a specific ID.
	 *
	 * @since 3.6.0
	 *
	 * @param string $id Id for the generated queue.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function get_generated_critical_css( $id ) {
		$this->request->set_timeout( self::CRITICAL_API_TIMEOUT );

		return $this->request->get(
			'critical-css/get/',
			array(
				'id'     => $id,
				'domain' => $this->get_network_home_url_on_subsite(),
			)
		);
	}

	/**
	 * Designed to retrieve network home url on subsite.
	 *
	 * @since 3.6.0
	 *
	 * @return string
	 */
	public function get_network_home_url_on_subsite() {
		return is_multisite() && ! is_main_site() ? network_home_url() : $this->request->get_this_site();
	}
}
