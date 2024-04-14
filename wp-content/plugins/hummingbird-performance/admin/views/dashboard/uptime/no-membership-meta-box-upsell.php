<?php
/**
 * Uptime upsell notice.
 *
 * @since 3.1.2
 * @package Hummingbird
 */

use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="sui-upsell-notice sui-padding sui-padding-top--hidden sui-padding-bottom__desktop--hidden">
	<div class="sui-upsell-notice__content">
		<div class="sui-notice sui-notice-purple">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
					<p><?php esc_html_e( 'Performance improvements hardly matter if your website isn’t accessible. Monitor your uptime and downtime with WPMU DEV’s Uptime Monitoring website management tool.', 'wphb' ); ?></p>
					<p><a class="sui-button sui-button-purple" target="_blank" href="<?php echo esc_url( Utils::get_link( 'plugin', 'hummingbird_dash_uptime_upsell_link' ) ); ?>">
							<?php esc_html_e( 'UNLOCK NOW WITH PRO', 'wphb' ); ?>
						</a></p>
				</div>
			</div>
		</div>
	</div>
</div>
