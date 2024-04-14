/* global WPHB_Admin */
/* global SUI */

import Fetcher from '../utils/fetcher';

( function( $ ) {
	WPHB_Admin.dashboard = {
		module: 'dashboard',

		init() {
			$( '.wphb-performance-report-item' ).on( 'click', function() {
				const url = $( this ).data( 'performance-url' );
				if ( url ) {
					location.href = url;
				}
			} );

			const clearCacheModalButton = document.getElementById(
				'clear-cache-modal-button'
			);
			if ( clearCacheModalButton ) {
				clearCacheModalButton.addEventListener(
					'click',
					this.clearCache
				);
			}

			// Delay JS checkbox update status.
			const dashboardDelay = $( '#view_delay_dashboard' );
			dashboardDelay.on( 'change', function() {
				// Update Delay JS status.
				const delayValue = $( this ).is( ':checked' );
				Fetcher.minification.toggleDelayJs( delayValue ).then( ( response ) => {
					window.wphbMixPanel.trackDelayJSEvent( {
						'update_type': (response.delay_js) ? 'activate' : 'deactivate',
						'Location': 'dash_widget',
						'Timeout': response.delay_js_timeout,
						'Excluded Files': (response.delay_js_exclude) ? 'yes' : 'no',
					} );
					
					WPHB_Admin.notices.show();
				} );
			} );

			// Critical CSS checkbox update status.
			const dashboardCritical = $( '#critical_css_toggle' );
			dashboardCritical.on( 'change', function() {
				// Update Critical CSS status.
				const criticalValue = $( this ).is( ':checked' );
				Fetcher.minification.toggleCriticalCss( criticalValue ).then( ( response ) => {
					window.wphbMixPanel.trackCriticalCSSEvent( response.criticalCss, 'dash_widget', response.mode, '', '' );
					if ( criticalValue && wphb.links.eoUrl ) {
						window.location.href = wphb.links.eoUrl;
					} else {
						WPHB_Admin.notices.show();
					}
				} );
			} );

			return this;
		},

		/**
		 * Clear selected cache.
		 *
		 * @since 2.7.1
		 */
		clearCache() {
			this.classList.toggle( 'sui-button-onload-text' );

			const checkboxes = document.querySelectorAll(
				'input[type="checkbox"]'
			);

			const modules = [];
			for ( let i = 0; i < checkboxes.length; i++ ) {
				if ( false === checkboxes[ i ].checked ) {
					continue;
				}

				modules.push( checkboxes[ i ].dataset.module );
			}

			Fetcher.common.clearCaches( modules ).then( ( response ) => {
				this.classList.toggle( 'sui-button-onload-text' );
				SUI.closeModal();
				WPHB_Admin.notices.show( response.message );
			} );
		},

		/**
		 * Hide upgrade summary modal.
		 *
		 * @param {Object} element Target button that was clicked.
		 */
		 hideUpgradeSummary: ( element ) => {
			window.SUI.closeModal();
			Fetcher.common.call( 'wphb_hide_upgrade_summary' ).then( () => {
				if ( element.hasAttribute( 'href' ) ) {
					window.location.href = element.href;
				}
			} );

			return false;
		},
	};
}( jQuery ) );
