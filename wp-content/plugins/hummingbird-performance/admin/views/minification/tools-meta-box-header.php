<?php
/**
 * Page caching header meta box.
 *
 * @package Hummingbird
 *
 * @var string $title       Module title.
 * @var bool   $has_fastcgi Has FastCGI enabled.
 */

use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_display_regenerate_button = ! Utils::get_module( 'critical_css' )->is_active() ? 'style="display: none"' : 'style="display: block"';
?>
<h3 class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<div class="sui-actions-right">
	<button type="button" <?php echo wp_kses_post( $is_display_regenerate_button ); ?> class="sui-button sui-tooltip sui-tooltip-top-right sui-tooltip-constrained" id="wphb-clear-critical-css" data-module="critcal_css" data-tooltip="<?php esc_attr_e( 'Clears all local or hosted assets and regenerates all required ones. Homepage assets will be automatically regenerated.', 'wphb' ); ?>" aria-live="polite">
		<!-- Default State Content -->
		<span class="sui-button-text-default">
			<?php esc_html_e( 'Regenerate Critical CSS', 'wphb' ); ?>
		</span>

		<!-- Loading State Content -->
		<span class="sui-button-text-onload">
			<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			<?php esc_html_e( 'Regenerate Critical CSS', 'wphb' ); ?>
		</span>
	</button>
</div>
