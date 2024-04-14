<?php
/**
 * Plugin Name: B Social Share - Block
 * Description: Share your website/website-page link to social networks and mobile messengers
 * Version: 1.0.5
 * Author: bPlugins LLC
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: social-share
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

// Constant
define( 'BSSB_PLUGIN_VERSION', isset($_SERVER['HTTP_HOST']) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.5' );
define( 'BSSB_ASSETS_DIR', plugin_dir_url( __FILE__ ) . 'assets/' );

// B Social Share
class BSSBSocialShare{
	function __construct(){
		add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
		add_action( 'wp_enqueue_scripts', [$this, 'wpEnqueueScripts'] );
		add_action( 'init', [$this, 'onInit'] );
	}

	function enqueueBlockAssets(){ wp_enqueue_style( 'fontAwesome', BSSB_ASSETS_DIR . 'css/fontAwesome.min.css', [], BSSB_PLUGIN_VERSION ); }
	function wpEnqueueScripts(){ wp_enqueue_script( 'goodshare', BSSB_ASSETS_DIR . 'js/goodshare.min.js', [], BSSB_PLUGIN_VERSION, true ); }

	function onInit() {
		wp_register_style( 'bssb-social-share-editor-style', plugins_url( 'dist/editor.css', __FILE__ ), [ 'wp-edit-blocks' ], BSSB_PLUGIN_VERSION ); // Backend Style
		wp_register_style( 'bssb-social-share-style', plugins_url( 'dist/style.css', __FILE__ ), [ 'wp-editor' ], BSSB_PLUGIN_VERSION ); // Frontend Style

		register_block_type( __DIR__, [
			'editor_style'		=> 'bssb-social-share-editor-style',
			'style'				=> 'bssb-social-share-style',
			'render_callback'	=> [$this, 'render']
		] ); // Register Block

		wp_set_script_translations( 'bssb-social-share-editor-script', 'social-share', plugin_dir_path( __FILE__ ) . 'languages' ); // Translate
	}

	function render( $attributes ){
		extract( $attributes );

		$className = $className ?? '';
		$bssbBlockClassName = 'wp-block-bssb-social-share ' . $className . ' align' . $align;

		ob_start(); ?>
		<div class='<?php echo esc_attr( $bssbBlockClassName ); ?>' id='bssbSocialShare-<?php echo esc_attr( $cId ) ?>' data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'>
			<div class='bssbStyle'></div>

			<ul class='bssbSocialShare'>
				<?php foreach ( $socials as $index => $social ) {
					extract( $social );
					// Classes
					$upIconClass = isset( $upIcon['id'] ) ? 'wp-image-'. $upIcon['id'] : '';
					$upIconEl = !empty( $upIcon['url'] ) ? "<img class='$upIconClass' src='". $upIcon['url'] ."' alt='". $upIcon['alt'] ."' />" : '';
					$iconEl = !empty( $icon['class'] ) ? "<i class='". $icon['class'] ."'></i>" : '';
					$filterIconEl = $isUpIcon ? $upIconEl : $iconEl; ?>

					<li class='icon icon-<?php echo esc_attr( $index ); ?>' data-social='<?php echo esc_attr( $network ); ?>'>
						<?php echo wp_kses_post( $filterIconEl ); ?>
					</il>
				<?php } ?>
			</ul>
		</div>

		<?php return ob_get_clean();
	} // Render
}
new BSSBSocialShare;