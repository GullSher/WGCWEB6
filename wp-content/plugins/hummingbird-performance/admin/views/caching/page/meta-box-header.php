<?php
/**
 * Page caching header meta box.
 *
 * @package Hummingbird
 *
 * @var string $title       Module title.
 * @var bool   $has_fastcgi Has FastCGI enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<div class="sui-actions-right">
	<?php if ( $has_fastcgi && ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) ) : ?>
		<button type="button" class="sui-button sui-button-ghost" id="wphb-disable-fastcgi" aria-live="polite">
			<!-- Default State Content -->
			<span class="sui-button-text-default">
				<span class="sui-icon-power-on-off" aria-hidden="true"></span>
				<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
			</span>

			<!-- Loading State Content -->
			<span class="sui-button-text-onload">
				<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				<?php esc_html_e( 'Deactivating', 'wphb' ); ?>
			</span>
		</button>
	<?php endif; ?>

	<button type="button" class="sui-button sui-button-ghost sui-tooltip sui-tooltip-top-right" id="wphb-clear-cache" data-module="page_cache" data-tooltip="<?php esc_attr_e( 'Clear all locally cached static pages', 'wphb' ); ?>" aria-live="polite">
		<!-- Default State Content -->
		<span class="sui-button-text-default">
			<?php esc_html_e( 'Clear cache', 'wphb' ); ?>
		</span>

		<!-- Loading State Content -->
		<span class="sui-button-text-onload">
			<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			<?php esc_html_e( 'Clearing cache', 'wphb' ); ?>
		</span>
	</button>
</div>
