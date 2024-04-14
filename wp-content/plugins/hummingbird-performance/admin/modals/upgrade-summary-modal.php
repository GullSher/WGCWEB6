<?php
/**
 * Upgrade highlight modal.
 *
 * @since 2.6.0
 * @package Hummingbird
 */

use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="sui-modal sui-modal-md">
	<div
			role="dialog"
			id="upgrade-summary-modal"
			class="sui-modal-content"
			aria-modal="true"
			aria-labelledby="upgrade-summary-modal-title"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<?php if ( ! apply_filters( 'wpmudev_branding_hide_branding', false ) ) : ?>
					<figure class="sui-box-banner" aria-hidden="true">
						<img src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-summary-bg.png' ); ?>" alt=""
							srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-summary-bg.png' ); ?> 1x, <?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/upgrade-summary-bg@2x.png' ); ?> 2x">
					</figure>
				<?php endif; ?>

				<button class="sui-button-icon sui-button-float--right" onclick="window.WPHB_Admin.dashboard.hideUpgradeSummary( this )">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this modal', 'wphb' ); ?></span>
				</button>

				<h3 id="upgrade-summary-modal-title" class="sui-box-title sui-lg" style="white-space: inherit">
					<?php esc_html_e( 'New: Generate Critical CSS', 'wphb' ); ?>
				</h3>
			</div>

			<div class="sui-box-body sui-spacing-top--20 sui-spacing-bottom--20">
				<div class="wphb-upgrade-feature">
					<p class="wphb-upgrade-item-desc" style="text-align: center">
						<?php
						printf( /* translators: %1$s - username, %2$s - opening <strong> tag, %3$s - closing <strong> tag, %4$s - opening <strong> tag, %5$s - closing <strong> tag */
							esc_html__( 'Hey %1$s! Hummingbird can now %2$sautomatically generate critical CSS and optimize CSS rendering.%3$s How cool is that? All the critical CSS code will be automatically added to the page header, improving your site’s loading speed and performance. You can specify when CSS should load, and fallback CSS file(s). Are you excited? To try out this new feature, navigate to %4$sAsset Optimization > Extra Optimization.%5$s', 'wphb' ),
							esc_html( Utils::get_user_name() ),
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
						);
						?>
					</p>
				</div>
				<div class="wphb-upgrade-feature">
					<ul class="sui-list">
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Reduce first input delay', 'wphb' ); ?></span></li>
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Reduce first contentful paint', 'wphb' ); ?></li>
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Remove unused CSS', 'wphb' ); ?></span></li>
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Remove Render-block issue', 'wphb' ); ?></li></span></li>
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Less load time to interactive', 'wphb' ); ?></li></span></li>
						<li><span class="sui-icon-check" aria-hidden="true"></span><span class="sui-list-label"><?php esc_html_e( 'Load page Faster', 'wphb' ); ?></span></li>
					</ul>

					<?php
					if ( is_multisite() ) {
						$hb_button      = esc_html__( 'Got it', 'wphb' );
						$hb_button_link = '#';
						printf( /* translators: %1$s - opening p tag, %2$s - opening <strong> tag, %3$s - closing <strong> tag, %4$s - closing p tag */
							esc_html__( '%1$sTo enable this feature, go to %2$sAsset Optimization > Extra Optimization%3$s.%4$s', 'wphb' ),
							'<p class="wphb-upgrade-item-desc" style="text-align: center;margin-top: 10px">',
							'<strong>',
							'</strong>',
							'</p>'
						);
					} else {
						$hb_button      = esc_html__( 'Check It Out Now', 'wphb' );
						$hb_button_link = Utils::get_admin_menu_url( 'minification' ) . '&view=tools';
					}
					?>
				</div>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
			<a href="<?php echo esc_url( $hb_button_link ); ?>" class="sui-button sui-button-blue" onclick="window.WPHB_Admin.dashboard.hideUpgradeSummary( this )">
					<?php echo esc_html( $hb_button ); ?>
			</a>
			</div>
		</div>
	</div>
</div>
