/* global WPHB_Admin */
/* global wphb */

/**
 * Asset Optimization scripts.
 *
 * @package
 */

import Fetcher from '../utils/fetcher';
import { getString, getLink } from '../utils/helpers';
import MinifyScanner from '../scanners/MinifyScanner';

/**
 * External dependencies
 */
 const MixPanel = require( 'mixpanel-browser' );
 let criticalAjaxInterval;
 const ajaxExecutionInterval = 10000; // The interval set to 10 seconds

( function( $ ) {
	'use strict';

	WPHB_Admin.minification = {
		module: 'minification',
		$checkFilesResultsContainer: null,
		checkURLSList: null,
		checkedURLS: 0,

		init() {
			const self = this;
			if ('undefined' !== typeof wphb.minification.criticalStatusForQueue.status && ( 'pending' === wphb.minification.criticalStatusForQueue.status || 'processing' === wphb.minification.criticalStatusForQueue.status ) ) {
				criticalAjaxInterval = setInterval( this.criticalUpdateStatusNotice, ajaxExecutionInterval );
			}

			// Init files scanner.
			this.scanner = new MinifyScanner(
				wphb.minification.get.totalSteps,
				wphb.minification.get.currentScanStep
			);

			// Check files button.
			$( '#check-files' ).on( 'click', function( e ) {
				e.preventDefault();
				$( document ).trigger( 'check-files' );
			} );

			$( document ).on( 'check-files', function() {
				window.SUI.openModal( 'check-files-modal', 'wpbody-content', 'check-files-modal' );
				$( this ).attr( 'disabled', true );
				self.scanner.start();
			} );

			// CDN checkbox update status
			const checkboxes = $( 'input[type=checkbox][name=use_cdn]' );
			checkboxes.on( 'change', function() {
				$( '#cdn_file_exclude' ).toggleClass( 'sui-hidden' );
				const cdnValue = $( this ).is( ':checked' );

				// Handle two CDN checkboxes on Asset Optimization page
				checkboxes.each( function() {
					this.checked = cdnValue;
				} );

				// Update CDN status
				Fetcher.minification.toggleCDN( cdnValue ).then( () => {
					WPHB_Admin.notices.show();
				} );
			} );

			// Delay Js Execution checkbox update status.
			$( 'input[type=checkbox][name=delay_js]' ).on(
				'change',
				function() {
					$( '#delay_js_file_exclude' ).toggleClass( 'sui-hidden' );
				}
			);

			// Critical CSS checkbox update status.
			const criticalCss = $( 'input[type=checkbox][name=critical_css_option]' );
			criticalCss.on( 'change', function() {
				$( '#critical_css_file_exclude' ).toggleClass( 'sui-hidden' );
			} );

			// Critical CSS checkbox update status.
			const criticalCssType = $( 'select[name=critical_css_type]' );
			criticalCssType.on( 'change', function( e ) {
				$( '.load_cs_options' ).addClass( 'sui-hidden' );
				$( '.load_' + e.target.value ).removeClass( 'sui-hidden' );
			} );

			$( '#manual_css_switch_now' ).on( 'click', function() {
				if ( ! wphbReact.isMember ) {
					self.hbTrackEoMPEvent( this );
					return;
				}

				window.WPHB_Admin.minification.criticalCSSSwitchMode( 'critical_css' );
			} );

			$( 'input[type=checkbox][name=debug_log]' ).on(
				'change',
				function() {
					const enabled = $( this ).is( ':checked' );
					Fetcher.minification.toggleLog( enabled ).then( () => {
						WPHB_Admin.notices.show();
						if ( enabled ) {
							$( '.wphb-logging-box' ).show();
						} else {
							$( '.wphb-logging-box' ).hide();
						}
					} );
				}
			);

			/**
			 * Save critical css file
			 */
			$( '#wphb-minification-tools-form' ).on( 'submit', function( e ) {
				e.preventDefault();

				const spinner = $( this ).find( '.spinner' );
				spinner.addClass( 'visible' );

				Fetcher.minification
					.saveCriticalCss( $( this ).serialize() )
					.then( ( response ) => {
						spinner.removeClass( 'visible' );
						if ( 'undefined' !== typeof response && response.success ) {
							const eventUpdateSummary = new Event("reloadSummary");
							document.getElementById("wphb-minification-tools-form").dispatchEvent(eventUpdateSummary);
							if ( response.is_delay_value_updated ) {
								window.wphbMixPanel.trackDelayJSEvent( {
									'update_type': response.delay_js_update_type,
									'Location': 'eo_settings',
									'Timeout': response.delay_js_timeout,
									'Excluded Files': (response.delay_js_exclude) ? 'yes' : 'no',
								} );
							}

							if ( response.isCriticalValueUpdated ) {
								window.wphbMixPanel.trackCriticalCSSEvent( response.updateType, response.location, response.mode, response.settingsModified, response.settingsDefault );
							}

							if ( response.isStatusTagNeedsUpdate ) {
								self.triggerCriticalStatusUpdateAjax( response.htmlForStatusTag );
							} else if ( 'deactivate' === response.updateType ) {
								self.criticalUpdateStatusTag( response.htmlForStatusTag );
							}

							const styleType = 'activate' === response.updateType ? 'block' : 'deactivate' === response.updateType ? 'none' : '';
							self.hbToggleElement( 'wphb-clear-critical-css', styleType );

							WPHB_Admin.notices.show( response.message, 'blue', false );
						} else {
							WPHB_Admin.notices.show( response.message, 'error' );
						}
					} );
			} );

			/**
			 * Parse custom asset dir input
			 *
			 * @since 1.9
			 */
			const textField = document.getElementById( 'file_path' );
			if ( null !== textField ) {
				textField.onchange = function( e ) {
					e.preventDefault();
					Fetcher.minification
						.updateAssetPath( $( this ).val() )
						.then( ( response ) => {
							if ( response.message ) {
								WPHB_Admin.notices.show( response.message, 'error' );
							} else {
								WPHB_Admin.notices.show();
							}
						} );
				};
			}

			/**
			 * Asset optimization network settings page.
			 *
			 * @since 2.0.0
			 */

			// Show/hide settings, based on checkbox value.
			$( '#wphb-network-ao' ).on( 'click', function() {
				$( '#wphb-network-border-frame' ).toggleClass( 'sui-hidden' );
			} );

			// Handle settings select.
			$( '#wphb-box-minification-network-settings' ).on(
				'change',
				'input[type=radio]',
				function( e ) {
					const divs = document.querySelectorAll(
						'input[name=' + e.target.name + ']'
					);

					// Toggle logs frame.
					if ( 'log' === e.target.name ) {
						$( '.wphb-logs-frame' ).toggle( e.target.value );
					}

					for ( let i = 0; i < divs.length; ++i ) {
						divs[ i ].parentNode.classList.remove( 'active' );
					}

					e.target.parentNode.classList.add( 'active' );
				}
			);

			// Network settings.
			$( '#wphb-ao-network-settings' ).on( 'click', function( e ) {
				e.preventDefault();

				const spinner = $( '.sui-box-footer' ).find( '.spinner' );
				spinner.addClass( 'visible' );

				const form = $( '#ao-network-settings-form' ).serialize();
				Fetcher.minification
					.saveNetworkSettings( form )
					.then( ( response ) => {
						spinner.removeClass( 'visible' );
						if ( 'undefined' !== typeof response && response.success ) {
							WPHB_Admin.notices.show();
						} else {
							WPHB_Admin.notices.show( getString( 'errorSettingsUpdate' ), 'error' );
						}
					} );
			} );

			/**
			 * Save exclusion rules.
			 */
			$( '#wphb-ao-settings-update' ).on( 'click', function( e ) {
				e.preventDefault();

				const spinner = $( '.sui-box-footer' ).find( '.spinner' );
				spinner.addClass( 'visible' );

				const data = self.getMultiSelectValues( 'cdn_exclude' );

				Fetcher.minification
					.updateExcludeList( JSON.stringify( data ) )
					.then( () => {
						spinner.removeClass( 'visible' );
						WPHB_Admin.notices.show();
					} );
			} );

			/**
			 * Asset optimization 2.0
			 *
			 * @since 2.6.0
			 */

			// How does it work? stuff.
			const expandButtonManual = document.getElementById( 'manual-ao-hdiw-modal-expand' );
			if ( expandButtonManual ) {
				expandButtonManual.onclick = function() {
					document.getElementById( 'manual-ao-hdiw-modal' ).classList.remove( 'sui-modal-sm' );
					document.getElementById( 'manual-ao-hdiw-modal-header-wrap' ).classList.remove( 'sui-box-sticky' );
					document.getElementById( 'automatic-ao-hdiw-modal' ).classList.remove( 'sui-modal-sm' );
				};
			}

			const collapseButtonManual = document.getElementById( 'manual-ao-hdiw-modal-collapse' );
			if ( collapseButtonManual ) {
				collapseButtonManual.onclick = function() {
					document.getElementById( 'manual-ao-hdiw-modal' ).classList.add( 'sui-modal-sm' );
					const el = document.getElementById( 'manual-ao-hdiw-modal-header-wrap' );
					if ( el.classList.contains( 'video-playing' ) ) {
						el.classList.add( 'sui-box-sticky' );
					}
					document.getElementById( 'automatic-ao-hdiw-modal' ).classList.add( 'sui-modal-sm' );
				};
			}

			// How does it work? stuff.
			const expandButtonAuto = document.getElementById( 'automatic-ao-hdiw-modal-expand' );
			if ( expandButtonAuto ) {
				expandButtonAuto.onclick = function() {
					document.getElementById( 'automatic-ao-hdiw-modal' ).classList.remove( 'sui-modal-sm' );
					document.getElementById( 'manual-ao-hdiw-modal' ).classList.remove( 'sui-modal-sm' );
				};
			}

			const collapseButtonAuto = document.getElementById( 'automatic-ao-hdiw-modal-collapse' );
			if ( collapseButtonAuto ) {
				collapseButtonAuto.onclick = function() {
					document.getElementById( 'automatic-ao-hdiw-modal' ).classList.add( 'sui-modal-sm' );
					document.getElementById( 'manual-ao-hdiw-modal' ).classList.add( 'sui-modal-sm' );
				};
			}

			const autoTrigger = document.getElementById( 'hdw-auto-trigger-label' );
			if ( autoTrigger ) {
				autoTrigger.addEventListener( 'click', () => {
					window.SUI.replaceModal(
						'automatic-ao-hdiw-modal-content',
						'wphb-basic-hdiw-link'
					);
				} );
			}

			const manualTrigger = document.getElementById( 'hdw-manual-trigger-label' );
			if ( manualTrigger ) {
				manualTrigger.addEventListener( 'click', () => {
					window.SUI.replaceModal(
						'manual-ao-hdiw-modal-content',
						'wphb-basic-hdiw-link'
					);
				} );
			}
			// Clear critical css files.
			$( '#wphb-clear-critical-css' ).on( 'click', ( e ) => {
				e.preventDefault();
				self.clearCriticalCss( e.target );
			} );
			return this;
		},

		/**
		 * Call ajax to get the critical css status for queue.
		 *
		 * @param {string} statusHtml
		 */
		 triggerCriticalStatusUpdateAjax( statusHtml ) {
			criticalAjaxInterval = setInterval( this.criticalUpdateStatusNotice, ajaxExecutionInterval );
			this.criticalUpdateStatusTag( statusHtml );
		 },

		/**
		 * Call ajax to get the critical css status for queue.
		 */
		criticalUpdateStatusNotice() {
			Fetcher.minification
			.getCriticalStatusForQueue()
			.then( ( response ) => {
				if ( 'undefined' !== typeof response.criticalStatusForQueue.status && 'complete' === response.criticalStatusForQueue.status ) {
					clearInterval( criticalAjaxInterval );
					WPHB_Admin.minification.criticalUpdateStatusTag( response.htmlForStatusTag );
					const criticalDisplayError = 'critical_display_error_message';

					if ( 'COMPLETE' === response.criticalStatusForQueue.result ) {
						WPHB_Admin.notices.show( getString( 'criticalGeneratedNotice' ), 'success', false );
						WPHB_Admin.minification.hbToggleElement( criticalDisplayError, 'none' );
					} else if ( 'ERROR' === response.criticalStatusForQueue.result ) {
						window.SUI.closeNotice( 'wphb-ajax-update-notice' );
						window.wphbMixPanel.track( 'error_encountered', {
							critical_css_error: response.errorCode
						} );
						WPHB_Admin.minification.hbToggleElement( criticalDisplayError, 'block' );
						document.getElementById( 'critical_error_message_tag' ).innerHTML = response.criticalErrorMessage;
					}
				}
			} );
		},

		/**
		 * Update the status html.
		 *
		 * @param {string} statusHtml
		 */
		 criticalUpdateStatusTag( statusHtml ) {
			document.getElementById( 'critical_progress_tag' ).remove();
			document.getElementById( 'generate_css_label' ).insertAdjacentHTML( 'afterend', statusHtml );
		},

		/**
		 * Toggle an element.
		 */
		hbToggleElement( elementId, styleType ) {
			if ( '' === styleType ) {
				return;
			}

			const regenerateButton = document.getElementById( elementId );
			regenerateButton.style.display = styleType;
		},

		/**
		 * Track MP event for delay js and critical css.
		 *
		 * @param {object} element
		 */
		 hbTrackEoMPEvent( element ) {
			window.wphbMixPanel.trackEoUpsell( element.dataset.eventname, element.dataset.location );
		},

		/**
		 * Track MP event on AO activation.
		 */
		 hbTrackMPOnAoActivate() {
			window.wphbMixPanel.enableFeature( 'Asset Optimization' )
			window.wphbMixPanel.trackAOUpdated( {
				'Mode': 'speedy',
				'assets_found': 0,
				'total_files': 0,
				'filesize_reductions': 0,
			} );
		},

		/**
		 * Switch from advanced to basic view.
		 * Called from switch view modal.
		 *
		 * @param {string} mode
		 */
		switchView( mode ) {
			let hide = false;
			const trackBox = document.getElementById(
				'hide-' + mode + '-modal'
			);

			if ( trackBox && true === trackBox.checked ) {
				hide = true;
			}

			Fetcher.minification.toggleView( mode, hide ).then( () => {
				window.wphbMixPanel.trackAOUpdated( {
					'Mode': mode === 'advanced' ? 'Manual' : wphb.stats.type,
					'assets_found': wphb.stats.assetsFound,
					'total_files': wphb.stats.totalFiles,
					'filesize_reductions': wphb.stats.filesizeReductions,
				} );
				
				window.location.href = getLink( 'minification' );
			} );
		},

		/**
		 * Go to the Asset Optimization files page.
		 *
		 * @since 1.9.2
		 * @since 2.1.0  Added show_tour parameter.
		 * @since 2.6.0  Remove show_tour parameter.
		 */
		goToSettings() {
			window.SUI.closeModal();

			Fetcher.minification
				.toggleCDN( $( 'input#enable_cdn' ).is( ':checked' ) )
				.then( () => {
					window.location.href = getLink( 'minification' );
				} );
		},

		/**
		 * Get all selected values from multiselect.
		 *
		 * @since 2.6.0
		 *
		 * @param {string} id Select ID.
		 * @return {{styles: *[], scripts: *[]}}  Styles & scripts array.
		 */
		getMultiSelectValues( id ) {
			const selected = $( '#' + id ).find( ':selected' );

			const data = { scripts: [], styles: [] };

			for ( let i = 0; i < selected.length; ++i ) {
				data[ selected[ i ].dataset.type ].push( selected[ i ].value );
			}

			return data;
		},

		/**
		 * Skip upgrade.
		 *
		 * @since 2.6.0
		 */
		skipUpgrade() {
			Fetcher.common.call( 'wphb_ao_skip_upgrade' ).then( () => {
				window.location.href = getLink( 'minification' );
			} );
		},

		/**
		 * Perform AO upgrade.
		 *
		 * @since 2.6.0
		 */
		doUpgrade() {
			Fetcher.common.call( 'wphb_ao_do_upgrade' ).then( () => {
				window.location.href = getLink( 'minification' );
			} );
		},

		/**
		 * Purge asset optimization orphaned data.
		 *
		 * @since 3.1.2
		 * @see Admin\Pages\Minification::orphaned_notice
		 */
		purgeOrphanedData() {
			const count = document.getElementById( 'count-ao-orphaned' )
				.innerHTML;

			Fetcher.advanced.clearOrphanedBatch( count ).then( () => {
				window.location.reload();
			} );
		},

		/**
		 * Clear critical CSS.
		 *
		 * @since 3.6.0
		 *
		 * @param {Object} target Target button that was clicked.
		 */
		clearCriticalCss: ( target ) => {
			target.classList.add( 'sui-button-onload-text' );
			Fetcher.minification.clearCriticalCssFiles().then( ( response ) => {
				if ( 'undefined' !== typeof response && response.success ) {
					window.wphbMixPanel.track( 'critical_css_cache_purge', {
						location: 'eo_settings'
					} );

					WPHB_Admin.minification.triggerCriticalStatusUpdateAjax( response.htmlForStatusTag );
					$( '.box-caching-summary span.sui-summary-large' ).html( '0' ); 
					WPHB_Admin.notices.show( getString( 'successCriticalCssPurge' ), 'blue', false );
				} else {
					WPHB_Admin.notices.show( getString( 'errorCriticalCssPurge' ), 'error' );
				}
			} ).finally( () => target.classList.remove( 'sui-button-onload-text' ) );
		},

		criticalCSSSwitchMode( mode ) {
			$('#critical_css_mode').val( mode )
			if ( 'manual_css' === mode ) {
				$("#manual_css_delivery_box").removeClass('sui-hidden');
				$("#critical_css_delivery_box").addClass('sui-hidden');
			} else {
				$("#manual_css_delivery_box").addClass('sui-hidden');
				$("#critical_css_delivery_box").removeClass('sui-hidden');
				const manualCriticalBox = document.getElementById( 'manual_critical_css' ).value;
				const advancedCriticalBox = document.getElementById( 'critical_css_advanced' ).value;

				if ( '' === advancedCriticalBox ) {
					document.getElementById( 'critical_css_advanced' ).value = manualCriticalBox;
				}
			}
		},
	}; // End WPHB_Admin.minification.
}( jQuery ) );
