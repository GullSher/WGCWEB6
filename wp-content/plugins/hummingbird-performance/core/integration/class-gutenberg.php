<?php
/**
 * Class for integration with the Gutenberg editor: WP_Hummingbird_Gutenberg_Integration class
 *
 * @since 1.9.4
 * @package Hummingbird\Core\Integration
 */

namespace Hummingbird\Core\Integration;
use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gutenberg
 */
class Gutenberg {

	/**
	 * Enabled status.
	 *
	 * @since 1.9.4
	 *
	 * @var bool $enabled
	 */
	private $enabled;

	/**
	 * WP_Hummingbird_Gutenberg_Integration constructor.
	 *
	 * @since 1.9.4
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Detect page caching and gutenberg, and initialize block editor components
	 *
	 * @since 2.5.0
	 */
	public function init() {
		// Page caching is not enabled.
		$page_cache = apply_filters( 'wp_hummingbird_is_active_module_page_cache', false );

		$critical_css = Utils::get_module( 'critical_css' )->is_active();

		if ( ! $page_cache && ! $critical_css && Utils::is_member() ) {
			return;
		}

		$this->check_for_gutenberg();

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_blocks' ) );
	}

	/**
	 * Make sure we only enqueue when Gutenberg is active.
	 *
	 * For WordPress pre 5.0 - only when Gutenberg plugin is installed.
	 * For WordPress 5.0+ - only when Classic Editor is NOT installed.
	 *
	 * @since 1.9.4
	 */
	private function check_for_gutenberg() {
		global $wp_version;

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if WordPress 5.0 or higher.
		$is_wp5point0 = version_compare( $wp_version, '4.9.9', '>' );

		if ( $is_wp5point0 ) {
			$this->enabled = ! is_plugin_active( 'classic-editor/classic-editor.php' );
		} else {
			$this->enabled = is_plugin_active( 'gutenberg/gutenberg.php' );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.9.4
	 */
	public function enqueue_gutenberg_blocks() {
		if ( ! $this->enabled ) {
			return;
		}

		$critical_css = Utils::get_module( 'critical_css' )->is_active();

		$button_label = ( ! apply_filters( 'wpmudev_branding_hide_branding', false ) ) ? esc_html__( 'HB', 'wphb' ) : '';
		$post_id      = get_the_ID();

		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		$type = get_post_type();

		$ignore_page_type                = Utils::get_module( 'critical_css' )->skip_page_type( $type );
		$display_critical                = $critical_css && ! $ignore_page_type && 'page' === $type;
		$single_post_critical_css_status = false;

		// Gutenberg block scripts.
		wp_enqueue_script(
			'wphb-gutenberg',
			WPHB_DIR_URL . 'admin/assets/js/wphb-gb-block.min.js',
			array(),
			WPHB_VERSION,
			true
		);


		if ( ! empty( $post_id ) ) {
			$single_post_critical_css_status = Utils::get_module( 'critical_css' )->get_single_post_critical_css_status( $post_id );
		}

		$page_cache = apply_filters( 'wp_hummingbird_is_active_module_page_cache', false );

		$pro_label = ( ! Utils::is_member() ) ? esc_html__( '(Pro)', 'wphb' ) : '';
		$args      = array(
			'strings' => array(
				'pageCache'                   => $page_cache,
				'isMember'                    => Utils::is_member(),
				'displayProLabelButton'       => ! Utils::is_member() && Utils::get_module( 'minify' )->is_active(),
				'eoPageUrl'                   => Utils::get_admin_menu_url( 'minification' ) . '&view=tools&triggercriticalupsellmodal=1',
				'gutenbergUTM'                => Utils::get_link( 'plugin', 'hummingbird_criticalcss_gutenberg' ),
				'button'                      => sprintf(
					/* translators: %s - Cache button label */
					esc_html__( 'Clear %s post cache', 'wphb' ),
					$button_label
				),
				'notice'                      => esc_html__( 'Cache for post has been cleared.', 'wphb' ),
				'criticalCreateButton'        => sprintf(
					/* translators: %s - button label for non member */
					esc_html__( 'Generate CSS File %s', 'wphb' ),
					$pro_label
				),
				'criticalRecreateButton'      => esc_html__( 'Regenerate CSS File', 'wphb' ),
				'criticalRevertButton'        => esc_html__( 'Revert back to the default CSS File', 'wphb' ),
				'criticalCss'                 => $display_critical,
				'singlePostCriticalCSSStatus' => $single_post_critical_css_status,
			),
			'nonces'  => array(
				'HBFetchNonce' => wp_create_nonce( 'wphb-fetch' ),
			),
		);

		$args = array_merge_recursive( $args, Utils::get_tracking_data() );

		wp_localize_script( 'wphb-gutenberg', 'wphb', $args );
	}
}
