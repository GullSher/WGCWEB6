<?php
/**
 * Smush trait.
 *
 * @since 2.4.0
 * @package Hummingbird\Core
 */

namespace Hummingbird\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Smush
 */
trait Smush {
	/**
	 * Variable used to distinguish between versions of Smush.
	 * Sets true if the Pro version is installed. False in all other cases.
	 *
	 * @var bool $is_smush_pro
	 */
	public $is_smush_pro = false;

	/**
	 * Check if Smush is installed.
	 *
	 * @return bool
	 */
	public function is_smush_installed() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		if ( array_key_exists( 'wp-smush-pro/wp-smush.php', $plugins ) ) {
			$this->is_smush_pro = true;
		}

		return array_key_exists( 'wp-smush-pro/wp-smush.php', $plugins ) || array_key_exists( 'wp-smushit/wp-smush.php', $plugins );
	}

	/**
	 * Check if Smush is active.
	 *
	 * @return bool
	 */
	public function is_smush_enabled() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'wp-smush-pro/wp-smush.php' ) || is_plugin_active( 'wp-smushit/wp-smush.php' );
	}

	/**
	 * Checks whether the Smush can be configured on a site or not.
	 *
	 * @return bool
	 */
	public function is_smush_configurable() {
		// If single site return true.
		if ( ! is_multisite() || is_network_admin() ) {
			return true;
		}

		$networkwide = get_site_option( 'wp-smush-networkwide' );
		return '0' !== $networkwide && false !== $networkwide;
	}

	/**
	 * Check if Smush has lazy load enabled.
	 *
	 * @return bool
	 */
	public function is_lazy_load_enabled() {
		if ( ! $this->is_smush_enabled() ) {
			return false;
		}

		$subsite_control = get_site_option( 'wp-smush-networkwide' );

		$settings = is_multisite() && ! $subsite_control ? get_site_option( 'wp-smush-settings', array() ) : get_option( 'wp-smush-settings', array() );
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return false;
		}

		return ! empty( $settings['lazy_load'] ) && $settings['lazy_load'];
	}

	/**
	 * Check if user can enable Smush lazy loading.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_lazy_load_configurable() {
		// Render all pages on single site installs.
		if ( ! is_multisite() ) {
			return true;
		}

		$access = get_site_option( 'wp-smush-networkwide' );

		if ( ! $access ) {
			return is_network_admin();
		}

		if ( '1' === $access ) {
			return ! is_network_admin();
		}

		if ( is_array( $access ) ) {
			if ( is_network_admin() && ! in_array( 'lazy_load', $access, true ) ) {
				return true;
			}

			if ( ! is_network_admin() && in_array( 'lazy_load', $access, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if smush is installed and is pro version, esle returns false.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	private function is_smush_pro() {
		if ( ! $this->is_smush_pro ) {
			// Calling is_smush_installed() sets $is_smush_pro property accordingly.
			$this->is_smush_installed();
		}

		return $this->is_smush_pro;
	}

	/**
	 * Provides the activation url for Smush plugin.
	 *
	 * @since 3.4.0
	 *
	 * @param string|null $version    Can be pro, free or null.
	 * @param bool        $with_nonce Should url include nonce.
	 *
	 * @return string
	 */
	public function smush_activation_url( $version = null, $with_nonce = true ) {
		$is_pro = false;

		if ( ! in_array( $version, array( 'free', 'pro' ), true ) ) {
			$is_pro = $this->is_smush_pro();
		} elseif ( 'pro' === $version ) {
			$is_pro = true;
		}

		$nonce_action = $is_pro ? 'activate-plugin_wp-smush-pro/wp-smush.php' : 'activate-plugin_wp-smushit/wp-smush.php';
		$path         = $is_pro ?
			'plugins.php?action=activate&amp;plugin=wp-smush-pro/wp-smush.php' :
			'plugins.php?action=activate&amp;plugin=wp-smushit/wp-smush.php';
		$url          = network_admin_url( $path );

		return $with_nonce ? wp_nonce_url( $url, $nonce_action ) : $url;
	}

}
