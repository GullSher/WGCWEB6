<?php
/**
 * Tools meta box.
 *
 * @since 1.8
 * @package Hummingbird
 *
 * @var string $css                            Above the fold CSS.
 * @var string $manual_inclusion               Manual Inclusion critical css
 * @var bool   $is_member                      Is user a Pro Member.
 * @var bool   $delay_js                       Delay JS status.
 * @var string $delay_js_timeout               Delay JS Timeout.
 * @var string $delay_js_excludes              Delay JS Exclusion lists.
 * @var string $critical_css                   Critical CSS.
 * @var string $critical_css_mode              Critical CSS Mode.
 * @var string $critical_css_type              Critical CSS type.
 * @var string $critical_css_remove_type       Critical CSS remove type.
 * @var string $critical_css_generation_notice Critical css completion notice.
 * @var string $critical_css_status            Critical css status for queue.
 * @var array  $pages                          Page Types.
 * @var bool   $blog_is_frontpage              If blog is front page.
 * @var array  $custom_post_types              Custom post types.
 * @var array  $settings                       Settings data.
 */

use Hummingbird\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_site_delay_js_enabled     = $delay_js && $is_member;
$is_site_critical_css_enabled = $critical_css && $is_member;

if ( ! $critical_css_mode ) {
	$critical_css_mode = ( $css ? 'manual_css' : 'critical_css' );
}

?>

<input type="hidden" name="critical_css_mode" id="critical_css_mode" value="<?php echo esc_attr( $critical_css_mode ); ?>" />
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
			<span class="sui-list-label"><strong><?php esc_html_e( 'Delay JavaScript', 'wphb' ); ?></strong>
				<?php if ( ! $is_member ) { ?>
					<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'wphb' ); ?></span>
				<?php } ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Improve performance by delaying the loading of non-critical JavaScript files above the fold until user interaction (e.g. scroll, click).', 'wphb' ); ?>
			</span>
	</div>

	<div class="sui-box-settings-col-2">
		<div class="sui-form-field">
			<?php if ( $is_member ) : ?>
				<label for="view_delay_js" class="sui-toggle">
					<input type="checkbox" name="delay_js" id="view_delay_js" aria-labelledby="view_delay_js-label" <?php checked( $is_site_delay_js_enabled ); ?>>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="view_delay_js-label" class="sui-toggle-label">
						<?php esc_html_e( 'Enable Delay JavaScript', 'wphb' ); ?>
					</span>
				</label>
			<?php else : ?>
				<label for="non_logged_in_delay_js" class="sui-toggle">
					<input type="checkbox" name="non_logged_in_delay_js" id="non_logged_in_delay_js" onclick="return false;">
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="non_logged_in_delay_js-label" class="sui-toggle-label">
						<?php esc_html_e( 'Delay JavaScript Execution', 'wphb' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Upgrade to Pro for instant access and fully optimized JS.', 'wphb' ); ?>
					</span>
				</label>
				<?php Utils::unlock_now_link( 'eo_settings', 'hummingbird_delay_js_ao_extra', 'delayjs' ); ?>
			<?php endif; ?>
		</div>
		<?php
		$delay_js_exclude_classes = array( 'sui-description', 'sui-toggle-description' );

		if ( ! $is_site_delay_js_enabled ) {
			$delay_js_exclude_classes[] = 'sui-hidden';
		}
		?>
		<span class="<?php echo implode( ' ', $delay_js_exclude_classes ); ?>" style="margin-top: 10px" id="delay_js_file_exclude">

			<label class="sui-label" for="delay_js_exclude" style="margin-top: 15px">
				<?php esc_html_e( 'Timeout', 'wphb' ); ?>
			</label>
			<span class="sui-description sui-toggle-description">
				<?php esc_html_e( 'Set a timeout in seconds that the scripts will be loaded if no user interaction has been detected.', 'wphb' ); ?>
			</span>
			<select name="delay_js_timeout" id="delay_js_timeout">
				<?php
				$delay_js_timeout_options = array(
					5  => __( '5 seconds', 'wphb' ),
					10 => __( '10 seconds', 'wphb' ),
					15 => __( '15 seconds', 'wphb' ),
					20 => __( '20 seconds (Recommended minimum)', 'wphb' ),
					25 => __( '25 seconds', 'wphb' ),
					30 => __( '30 seconds', 'wphb' ),
				);

				$selected_time = $delay_js_timeout ? $delay_js_timeout : 20;

				?>
				<?php foreach ( $delay_js_timeout_options as $dts_time => $dvalue ) : ?>
					<option value="<?php echo esc_attr( $dts_time ); ?>" <?php selected( $dts_time, $selected_time ); ?>>
						<?php echo esc_html( ucfirst( $dvalue ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<label class="sui-label" for="delay_js_exclude" style="margin-top: 15px">
				<?php esc_html_e( 'Excluded JavaScript Files ', 'wphb' ); ?>
			</label>
			<textarea class="sui-form-control" id="delay_js_exclude" name="delay_js_exclude" placeholder="/wp-content/themes/some-theme/jsfile.js
jsfile
script id"><?php echo esc_html( $delay_js_excludes ); ?></textarea>
			<?php
			printf( /* translators: %1$s - jsfile, %2$s - jsfile with url, %3$s - script id */
				esc_html__( 'Specify the URLs or keywords that should be excluded from delaying execution (one per line). E.g. %1$s or %2$s or %3$s', 'wphb' ),
				'<b>jsfile</b>',
				'<b>/wp-content/themes/some-theme/jsfile.js</b>',
				'<b>script id</b>'
			);
			?>
		</span>
	</div>
</div>

<div class="sui-accordion" id="critical_display_error_message" style="display: <?php echo ! empty( $critical_css_status['error_message'] ) ? esc_attr( 'block' ) : esc_attr( 'none' ); ?>;">
	<div class="sui-accordion-item sui-warning">
		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title sui-accordion-col-4"><span aria-hidden="true" class="sui-icon-warning-alert sui-warning"></span> <?php esc_html_e( 'Critical CSS encounter an issue!', 'wphb' ); ?></div>
			<div class="sui-accordion-col-4"></div>
			<div class="sui-accordion-col-4">
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item" onclick="return false;">
					<span class="sui-icon-chevron-down" aria-hidden="true"></span>
				</button>
			</div>
		</div>
		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<p id="critical_error_message_tag"><?php echo wp_kses_post( $critical_css_generation_notice ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="critical_css_delivery_box" class="sui-box-settings-row <?php echo esc_attr( ( 'manual_css' === $critical_css_mode ? 'sui-hidden' : '' ) ); ?>">
	<div class="sui-box-settings-col-1">
			<span class="sui-list-label"><strong id="generate_css_label"><?php esc_html_e( 'Critical CSS', 'wphb' ); ?></strong><?php echo wp_kses_post( Utils::get_module( 'critical_css' )->get_html_for_status_tag() ); ?>
				<?php if ( ! $is_member ) { ?>
					<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'wphb' ); ?></span>
				<?php } ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Drastically reduce your page load time and eliminate render-blocking CSS by automatically generating the critical CSS required to load your page.', 'wphb' ); ?>
				<br/>
				<br/>
				<?php
				if ( ! empty( $css ) ) {
					printf( /* translators: %1$s - Opening <a> tag, %2$s - Closing </a> tag */
						esc_html__( 'You can switch to %1$smanual mode%2$s to add the critical CSS manually.', 'wphb' ),
						'<a href="javascript:;" onClick="return window.WPHB_Admin.minification.criticalCSSSwitchMode(\'manual_css\');">',
						'</a>'
					);
				}
				?>
			</span>
	</div>

	<div class="sui-box-settings-col-2">
		<div class="sui-form-field">
			<?php if ( $is_member ) : ?>
				<label for="critical_css_toggle" class="sui-toggle">
					<input type="checkbox" name="critical_css_option" id="critical_css_toggle" aria-labelledby="critical_css-label" <?php checked( $is_site_critical_css_enabled ); ?>>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="critical_css_toggle-label" class="sui-toggle-label">
						<?php esc_html_e( 'Generate Critical CSS', 'wphb' ); ?>
					</span>
				</label>
			<?php else : ?>
				<label for="non_logged_in_critical_css" class="sui-toggle">
					<input type="checkbox" name="non_logged_in_critical_css" id="non_logged_in_critical_css" onclick="return false;">
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="non_logged_in_critical_css-label" class="sui-toggle-label">
						<?php esc_html_e( 'Generate Critical CSS', 'wphb' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Another way to Boost site speed Even faster pages, better user experience, and improved SEO. You will love it.', 'wphb' ); ?>
					</span>
				</label>
				<?php Utils::unlock_now_link( 'eo_settings', 'hummingbird_criticalcss_ao_extra', 'critical_css' ); ?>
			<?php endif; ?>
		</div>
		<?php
		$critical_css_exclude_classes = array( 'sui-description', 'sui-toggle-description' );
		if ( ! $is_site_critical_css_enabled ) {
			$critical_css_exclude_classes[] = 'sui-hidden';
		}
		?>
		<span class="<?php echo esc_attr( implode( ' ', $critical_css_exclude_classes ) ); ?>" style="margin-top: 10px" id="critical_css_file_exclude">
		<div class="sui-form-field">
			<label class="sui-label" for="critical_css_type" style="margin-top: 15px">
				<?php esc_html_e( 'Choose how to load critical CSS.', 'wphb' ); ?>
			</label>
			<select name="critical_css_type" id="critical_css_type">
				<?php
				$critical_css_type_options = array(
					'remove'         => __( 'Full Page CSS Optimization (Recommended)', 'wphb' ),
					'asynchronously' => __( 'Above the fold CSS Optimization', 'wphb' ),
				);

				$selected_cs_type = $critical_css_type ? $critical_css_type : 'remove';
				?>
				<?php foreach ( $critical_css_type_options as $cs_option => $cs_value ) : ?>
					<option value="<?php echo esc_attr( $cs_option ); ?>" <?php echo 'on_user_interaction' === $cs_option ? 'disabled' : ''; ?> <?php selected( $cs_option, $selected_cs_type ); ?>>
						<?php echo esc_html( $cs_value ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			$selected_css_type_classes = array();
			foreach ( $critical_css_type_options as $key => $val ) {
				if ( $key === $selected_cs_type ) {
					$selected_css_type_classes[ $key ] = '';
				} else {
					$selected_css_type_classes[ $key ] = 'sui-hidden';
				}
			}
			?>
			<div class="sui-description sui-toggle-description load_cs_options load_asynchronously <?php echo esc_attr( $selected_css_type_classes['asynchronously'] ); ?>">
				<?php esc_html_e( 'Inline above-the-fold CSS, load the rest asynchronously.', 'wphb' ); ?>
			</div>
			<div class="sui-description sui-toggle-description load_cs_options load_remove <?php echo esc_attr( $selected_css_type_classes['remove'] ); ?>">
				<?php esc_html_e( 'Inline all used CSS, delay/remove the rest.', 'wphb' ); ?>
			</div>
		</div>
		<div class="sui-form-field load_cs_options load_remove <?php echo esc_attr( $selected_css_type_classes['remove'] ); ?>" role="radiogroup">
			<div class="sui-description">
				<?php esc_html_e( 'How to handle the Unused CSS', 'wphb' ); ?>
			</div>
			<label for="user_interaction_with_remove" class="sui-radio">
				<input type="radio" value="user_interaction_with_remove" <?php checked( $critical_css_remove_type, 'user_interaction_with_remove' ); ?> name="critical_css_remove_type" id="user_interaction_with_remove" aria-labelledby="user_interaction_with_remove_label">
				<span aria-hidden="true"></span>
				<span id="user_interaction_with_remove_label"><?php esc_html_e( 'Load on User Interaction', 'wphb' ); ?></span>

			</label>
			<label for="remove_unused" class="sui-radio">
				<input type="radio" value="remove_unused" <?php checked( $critical_css_remove_type, 'remove_unused' ); ?> name="critical_css_remove_type" id="remove_unused" aria-labelledby="remove_unused_label">
				<span aria-hidden="true"></span>
				<span id="remove_unused_label"><?php esc_html_e( 'Remove Unused', 'wphb' ); ?></span>

			</label>
		</div>
		<?php
		$cs_type_remove_notice_classes = array();

		if ( 'remove' === $selected_cs_type ) {
			$cs_type_remove_notice_classes[] = 'sui-hidden';
		}
		?>
		<!-- Begin -->
		<table class="sui-table sui-accordion">
			<tbody>
				<tr class="sui-accordion-item sui-table-item-first">
					<td class="sui-table-item-title">
						<?php esc_html_e( 'Post type', 'wphb' ); ?>
						<span class="sui-accordion-open-indicator" aria-label="Expand">
							<span class="sui-icon-chevron-down" aria-hidden="true"></span>
						</span>
					</td>
				</tr>
				<tr class="sui-accordion-item-content">
					<td>
						<div class="sui-box" tabindex="0">
							<div class="sui-box-body">
								<label class="sui-label">
									<?php esc_html_e( 'Toggling on will include the Critical CSS generation for these pages', 'wphb' ); ?>
								</label>
								<?php
								foreach ( $pages as $page_type => $page_name ) :
									?>
									<div class="wphb-dash-table-row">
										<div><?php echo esc_html( $page_name ); ?></div>
										<?php if ( 'home' === $page_type && $blog_is_frontpage ) : ?>
											<span class="sui-tag sui-tag-inactive"><?php esc_html_e( 'Your blog is your frontpage', 'wphb' ); ?></span>
										<?php else : ?>
											<span class="sub"><?php echo esc_html( $page_type ); ?></span>
											<label class="sui-toggle">
												<input type="checkbox" name="critical_page_types[<?php echo esc_attr( $page_type ); ?>]" id="<?php echo esc_attr( $page_type ); ?>" <?php checked( in_array( $page_type, $settings['critical_page_types'], true ) ); ?>>
												<span class="sui-toggle-slider"></span>
											</label>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
								<?php foreach ( $custom_post_types  as $custom_post_type ) : ?>
									<div class="wphb-dash-table-row">
										<div><?php echo esc_html( $custom_post_type->label ); ?></div>
										<span class="sub"><?php echo esc_html( $custom_post_type->name ); ?></span>
										<input type="hidden" name="critical_skipped_custom_post_types[<?php echo esc_attr( $custom_post_type->name ); ?>]" value="1">
										<label class="sui-toggle">
											<input type="checkbox" name="critical_skipped_custom_post_types[<?php echo esc_attr( $custom_post_type->name ); ?>]" id="<?php echo esc_attr( $custom_post_type->name ); ?>" <?php checked( ! in_array( $custom_post_type->name, $settings['critical_skipped_custom_post_types'], true ) ); ?> value="0">
											<span class="sui-toggle-slider"></span>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="sui-table sui-accordion">
			<tbody>
				<tr class="sui-accordion-item">
					<td class="sui-table-item-title">
						<?php esc_html_e( 'Manual Inclusions (Advanced)', 'wphb' ); ?>
						<span class="sui-accordion-open-indicator" aria-label="Expand">
							<span class="sui-icon-chevron-down" aria-hidden="true"></span>
						</span>
					</td>
				</tr>
				<tr class="sui-accordion-item-content">
					<td>
						<div class="sui-box" tabindex="0">
							<div class="sui-box-body">
								<textarea class="sui-form-control" id="critical_css_advanced" name="critical_css_advanced" placeholder="<?php esc_attr_e( 'Add CSS here', 'wphb' ); ?>"><?php echo esc_html( $manual_inclusion ); ?></textarea>
								<div class="sui-description">
									<?php
									$this->admin_notices->show_inline(
										__( 'Only use this option if you see a broken element on your site to add the critical elements manually. This might affect your PageSpeed negatively.', 'wphb' ),
										'warning'
									);
									?>
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- End 1-->
		</span>
	</div>
</div>

<div id="manual_css_delivery_box" class="sui-box-settings-row <?php echo esc_attr( ( 'critical_css' === $critical_css_mode ? 'sui-hidden' : '' ) ); ?>">
	<div class="sui-box-settings-col-1">
		<strong><?php esc_html_e( 'CSS Above the fold', 'wphb' ); ?></strong>
		<span class="sui-description">
			<?php
			esc_html_e(
				'Paste your Manual critical CSS and remove render-blocking CSS from your site. Drastically reduce your page load time by moving all of your stylesheets to the footer to force them to load after your content.',
				'wphb'
			);
			?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<?php
		if ( ! $is_member ) {
			$hb_pro_upsell = sprintf( /* translators: %1$s - opening span tag, %2$s - closing </span> tag */
				esc_html__( '%1$sPro%2$s', 'wphb' ),
				'<span class="sui-tag sui-tag-pro">',
				'</span>'
			);

			$switch_now = Utils::unlock_now_link( 'legacy_switch', 'hummingbird_criticalcss_eo_legacy_switch', 'critical_css', false );
		} else {
			$hb_pro_upsell = sprintf( /* translators: %1$s - opening span tag, %2$s - closing </span> tag */
				esc_html__( '%1$sNEW%2$s', 'wphb' ),
				'<span class="sui-tag sui-tag-green">',
				'</span>'
			);

			$switch_now = sprintf( /* translators: %1$s - opening a tag, %2$s - closing a tag */
				esc_html__( '%1$sSwitch now%2$s', 'wphb' ),
				'<a style="cursor: pointer;" id="manual_css_switch_now">',
				'</a>'
			);
		}

		$notice_text = sprintf( /* translators: %1$s: opening span tag, %2$s: closing span tag, %3$s: pro tag, %4$s: switch critical mode href link, %5$s: closing a tag */
			__( '<b>New - Automatic CSS Generation!</b> %1$s <br> Serve sites faster with advanced Critical CSS generation. Your existing settings will be automatically migrated as Manual Inclusions. %2$s', 'wphb' ),
			$hb_pro_upsell,
			$switch_now
		);
		$this->admin_notices->show_inline( $notice_text, 'blue' );
		?>
		<span class="sui-description">
			<?php esc_html_e( 'CSS to insert into your <head> area', 'wphb' ); ?>
		</span>
		<textarea class="sui-form-control" id="manual_critical_css" name="critical_css" placeholder="<?php esc_attr_e( 'Add CSS here', 'wphb' ); ?>"><?php echo esc_html( $css ); ?></textarea>
		<span class="sui-description"><?php esc_html_e( 'Directions:', 'wphb' ); ?></span>
		<ol class="sui-description">
			<li>
				<?php esc_html_e( 'Add critical layout and styling CSS here. We will insert into <style> tags in your <head> section of each page.', 'wphb' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Next, switch to the manual mode in asset optimization and move all of your CSS files to the footer area.', 'wphb' ); ?>
			</li>
		</ol>
	</div>
</div>
