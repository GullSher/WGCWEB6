/* global wphb */

const MixPanel = require( 'mixpanel-browser' );

( function() {
	'use strict';

	window.wphbMixPanel = {
		/**
		 * Init super properties (common with every request).
		 */
		init() {
			if (
				'undefined' === typeof wphb.mixpanel ||
				! wphb.mixpanel.enabled
			) {
				return;
			}

			MixPanel.init( '5d545622e3a040aca63f2089b0e6cae7', {
				opt_out_tracking_by_default: ! wphb.mixpanel.enabled,
				ip: false,
			} );

			MixPanel.register( {
				plugin: wphb.mixpanel.plugin,
				plugin_type: wphb.mixpanel.plugin_type,
				plugin_version: wphb.mixpanel.plugin_version,
				wp_version: wphb.mixpanel.wp_version,
				wp_type: wphb.mixpanel.wp_type,
				locale: wphb.mixpanel.locale,
				active_theme: wphb.mixpanel.active_theme,
				php_version: wphb.mixpanel.php_version,
				mysql_version: wphb.mixpanel.mysql_version,
				server_type: wphb.mixpanel.server_type,
			} );
		},

		/**
		 * Opt in tracking.
		 */
		optIn() {
			wphb.mixpanel.enabled = true;
			this.init();
			MixPanel.opt_in_tracking();
		},

		/**
		 * Opt out tracking.
		 */
		optOut() {
			MixPanel.opt_out_tracking();
		},

		/**
		 * Deactivate feedback.
		 *
		 * @param {string} reason   Deactivation reason.
		 * @param {string} feedback Deactivation feedback.
		 */
		deactivate( reason, feedback = '' ) {
			this.track( 'plugin_deactivate', {
				reason,
				feedback,
			} );
		},

		/**
		 * Track feature enable.
		 *
		 * @param {string} feature Feature name.
		 */
		enableFeature( feature ) {
			this.track( 'plugin_feature_activate', { feature } );
		},

		/**
		 * Track EO Upsell event.
		 *
		 * @param {string} eventName Event name.
		 * @param {string} location  Location.
		 */
		trackEoUpsell( eventName, location ) {
			const mpEventName = 'delayjs' === eventName ? 'js_delay_upsell' : 'critical_css_upsell';
	
			this.track( mpEventName, {
				'Modal Action': 'direct_cta',
				'Location': location,
			} );
		},

		/**
		 * Track Delay JS Upsell event.
		 *
		 * @param {object} properties Properties.
		 */
		 trackDelayJSEvent( properties ) {
			if ( 'activate' === properties.update_type ) {
				this.enableFeature( 'JS Delay' );
			}

			if ( 'deactivate' === properties.update_type ) {
				this.disableFeature( 'JS Delay' );
			}

			this.track( 'js_delay_updated', properties );
		},

		/**
		 * Track Critical Upsell event.
		 *
		 * @param {string} updateType       Update type.
		 * @param {string} location         Location.
		 * @param {string} mode             Mode.
		 * @param {string} settingsModified Settings modified.
		 * @param {string} settingsDefault  Settings default.
		 */
		trackCriticalCSSEvent( updateType, location, mode, settingsModified, settingsDefault ) {
			if ( 'activate' === updateType ) {
				this.enableFeature( 'Critical Css' );
			}

			if ( 'deactivate' === updateType ) {
				this.disableFeature( 'Critical Css' );
			}

			this.track( 'critical_css_updated', {
				'update_type': updateType,
				'Location': location,
				'mode': mode,
				'settings_modified': settingsModified,
				'settings_default': settingsDefault,
			} );
		},

		/**
		 * Track AO updated event.
		 *
		 * @param {object} properties Properties.
		 */
		 trackAOUpdated( properties ) {
			const mode = properties.Mode.charAt(0).toUpperCase() + properties.Mode.slice(1);
			properties.Mode = mode;
			this.track( 'ao_updated', properties );
		},

		/**
		 * Track AO updated event.
		 *
		 * @param {object} feature Feature.
		 */
		 trackGutenbergEvent( feature ) {
			this.track( 'critical_css_gutenberg', { feature } );

			if ( 'revert' === feature ) {
				this.track( 'critical_css_cache_purge', {
					location: 'gutenberg'
				} );
			}
		},


		/**
		 * Track feature disable.
		 *
		 * @param {string} feature Feature name.
		 */
		disableFeature( feature ) {
			this.track( 'plugin_feature_deactivate', { feature } );
		},

		/**
		 * Track an event.
		 *
		 * @param {string} event Event ID.
		 * @param {Object} data  Event data.
		 */
		track( event, data = {} ) {
			if (
				'undefined' === typeof wphb.mixpanel ||
				! wphb.mixpanel.enabled
			) {
				return;
			}

			if ( ! MixPanel.has_opted_out_tracking() ) {
				MixPanel.track( event, data );
			}
		}
	};
}() );
