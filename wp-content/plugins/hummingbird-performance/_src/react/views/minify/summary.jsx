/**
 * External dependencies
 */
 import React, { useEffect, useState, useRef } from 'react';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
import { dispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from '../../data/minify';
import './summary.scss';
import Tooltip from '../../components/sui-tooltip';
import Icon from '../../components/sui-icon';
import List from '../../components/sui-list';
import BoxSummary from '../../components/sui-box-summary';
import Tag from '../../components/sui-tag';
import Toggle from '../../components/sui-toggle';
import Button from '../../components/sui-button';
import HBAPIFetch from '../../api';
import useSummaryUpdate from './utils/summaryUpdate'

/**
 * MinifySummary functional component.
 *
 * @since 3.4.0
 * @param {Object} props
 * @return {JSX.Element} Summary meta box
 */
export const MinifySummary = ( props ) => {
	const summaryUpdate = useSummaryUpdate();
	const didMount = useRef( false );
	const api = new HBAPIFetch();
	const [ loading, setLoading ] = useState( true );
	const { cdn, safeMode, assets, hasResolved, delayJs, criticalCss } = useSelect( ( select ) => {
		if ( ! select( STORE_NAME ).hasStartedResolution( 'getOptions' ) ) {
			select( STORE_NAME ).getOptions();
		}

		return {
			cdn: select( STORE_NAME ).getOption( 'cdn' ),
			safeMode: select( STORE_NAME ).getOption( 'safeMode' ),
			assets: select( STORE_NAME ).getAssets(),
			hasResolved: select( STORE_NAME ).hasFinishedResolution( 'getOptions' ) && select( STORE_NAME ).hasFinishedResolution( 'getAssets' ),
			delayJs: select( STORE_NAME ).getOption( 'delay_js' ),
			criticalCss: select( STORE_NAME ).getOption( 'critical_css' ),
		};
	}, [] );

	/**
	 * Sync loading state with resolver.
	 *
	 * @since 3.4.0
	 */
	useEffect( () => {
		if ( loading && hasResolved ) {
			setTimeout( () => {
				setLoading( false );
			}, 250 );
		}
	}, [ hasResolved, setLoading ] );

	useEffect( () => {
		if ( didMount.current ) {
			let viewDelayJs = document.getElementById("view_delay_js");
			if ( viewDelayJs ) {
				if ( viewDelayJs.checked !== delayJs  ) {
					viewDelayJs.checked = delayJs;
					jQuery(viewDelayJs).trigger("change");
				}			
			}

			let viewCriticalCss = document.getElementById("critical_css_toggle");
			if ( viewCriticalCss ) {
				if ( viewCriticalCss.checked !== criticalCss  ) {
					viewCriticalCss.checked = criticalCss;
					jQuery(viewCriticalCss).trigger("change");
				}			
			}
		} else {
			didMount.current = true;
		}				
	}, [ delayJs, criticalCss ] );

	/**
	 * Get original/compressed sizes.
	 *
	 * @since 3.4.0
	 *
	 * @return {Array} Array of original and compressed sizes.
	 */
	const getSizes = () => {
		if ( undefined === assets.styles || undefined === assets.scripts ) {
			return [ 0, 0, 0 ];
		}
		if ( undefined === assets.dashboard_data || undefined === assets.dashboard_data.original_size || undefined === assets.dashboard_data.compressed_size || undefined === assets.dashboard_data.percentage ) {
			return [ 0, 0, 0 ];
		}

		return [ assets.dashboard_data.original_size, assets.dashboard_data.compressed_size, assets.dashboard_data.percentage ];
	};

	/**
	 * Get compressed size stats.
	 *
	 * @since 3.4.0
	 *
	 * @return {number} Compressed size value.
	 */
	const getCompressedSize = () => {
		const sizes = getSizes();
		return  sizes[ 1 ];
	};

	/**
	 * Get number of enqueued files.
	 *
	 * @since 3.4.0
	 *
	 * @return {number} Number of assets
	 */
	const getEnqueuedFiles = () => {
		if ( undefined === assets.styles || undefined === assets.scripts ) {
			return 0;
		}

		let total = Object.keys( assets.styles ).length + Object.keys( assets.scripts ).length;

		if ( undefined !== assets.fonts ) {
			total += Object.keys( assets.fonts ).length;
		}

		return total;
	};

	/**
	 * Get percent optimized value.
	 *
	 * @since 3.4.0
	 *
	 * @return {number} Percent
	 */
	const getPercentOptimized = () => {
		const [ originalSize, compressedSize, percent ] = getSizes();

		if ( 0 === originalSize || 0 === compressedSize || 0 === percent ) {
			return 0;
		}

		return percent;
	};

	/**
	 * Toggle CDN.
	 *
	 * @since 3.4.0
	 *
	 * @param {Object} e
	 */
	const toggleCDN = ( e ) => {

		api.post( 'minify_toggle_cdn', e.target.checked )
			.then( ( response ) => {
				if ( response.cdn ) {
					window.wphbMixPanel.enableFeature( 'CDN' );
				} else {
					window.wphbMixPanel.disableFeature( 'CDN' );
				}

				dispatch( STORE_NAME ).invalidateResolution( 'getOptions' );
			} )
			.catch( window.console.log );
	};

	/**
	 * Toggle Delay Js.
	 *
	 * @since 3.4.4
	 *
	 * @param {Object} e
	 */
	const toggleDelay = ( e ) => {

		api.post( 'minify_toggle_delay_js', e.target.checked )
			.then( ( response ) => {
				window.wphbMixPanel.trackDelayJSEvent( {
					'update_type': (response.delay_js) ? 'activate' : 'deactivate',
					'Location': 'ao_summary',
					'Timeout': response.delay_js_timeout,
					'Excluded Files': (response.delay_js_exclude) ? 'yes' : 'no',
				} );

				dispatch( STORE_NAME ).invalidateResolution( 'getOptions' );
			} )
			.catch( window.console.log );
	};

	/**
	 * Toggle Critical CSS.
	 *
	 * @since 3.6.0
	 *
	 * @param {Object} e
	 */
	const toggleCritical = ( e ) => {

		api.post( 'minify_toggle_critical_css', e.target.checked )
			.then( ( response ) => {
				window.wphbMixPanel.trackCriticalCSSEvent( response.criticalCss, 'ao_summary', response.mode, '', '' );

				dispatch( STORE_NAME ).invalidateResolution( 'getOptions' );
				if ( 'activate' === response.criticalCss ) {
					WPHB_Admin.notices.show( wphb.strings.enableCriticalCss, 'blue', false );
					WPHB_Admin.minification.triggerCriticalStatusUpdateAjax( response.htmlForStatusTag );
				} else if ( 'deactivate' === response.criticalCss ) {
					WPHB_Admin.minification.criticalUpdateStatusTag( response.htmlForStatusTag );
				}

				const styleType = 'activate' === response.criticalCss ? 'block' : 'deactivate' === response.criticalCss ? 'none' : '';
				WPHB_Admin.minification.hbToggleElement( 'wphb-clear-critical-css', styleType );
			} )
			.catch( window.console.log );
	};

	/**
	 * Get summary segment content.
	 *
	 * @return {JSX.Element} Summary segment
	 */
	const getSummarySegmentLeft = () => {
		const percentage = getPercentOptimized();

		return (
			<div className="sui-summary-details">
				{ 0 === percentage && 'basic' === props.wphbData.mode &&
					<Tooltip text={ __( 'All assets are auto-compressed', 'wphb' ) }>
						<Icon classes="sui-icon-check-tick sui-lg sui-success" />
					</Tooltip> }

				{ 0 === percentage && 'basic' !== props.wphbData.mode && String.fromCharCode( 8212 ) }

				{ 0 !== percentage &&
					<span className="sui-summary-large">
						{ percentage }%
					</span> }
				<span className="sui-summary-sub" style={{ marginBottom: 20 }}>{ __( 'Compression savings', 'wphb' ) }</span>
				<span className="sui-summary-detail">{ __( 'Total Files', 'wphb' ) }</span>
				<span className="sui-summary-sub">{ getEnqueuedFiles() }</span>
			</div>
		);
	};

	/**
	 * Returns unlock pro upsell link.
	 *
	 * @param {string} utm
	 */
	const getUnlockUpsellLink = ( utm, eventname ) => {
		return (
			<a target="_blank" data-location="ao_summary" data-eventname={ eventname } href={ utm } className="wphb-upsell-link wphb-upsell-eo" onClick={ trackMPEvent }>
				{ __( 'Unlock now  ', 'wphb' ) }
				<span className="sui-icon-open-new-window" aria-hidden="true"></span>
			</a>
		);
	};

	/**
	 * Track EO event on AO summary box.
	 *
	 * @since 3.7.0
	 *
	 * @param {Object} e
	 */
	const trackMPEvent = ( e ) => {
		WPHB_Admin.minification.hbTrackEoMPEvent( e.target )

		return false;
	};

	/**
	 * Get summary segment content.
	 *
	 * @return {JSX.Element} Summary segment
	 */
	const getSummarySegmentRight = () => {
		const compressedSize = getCompressedSize();
		let reduction = compressedSize.toString() + 'kb';

		if ( 'basic' === props.wphbData.mode && 0 === compressedSize ) {
			reduction = (
				<React.Fragment>
					{ __( 'Files are compressed', 'wphb' ) }
					<Icon classes="sui-icon-check-tick sui-md sui-success" />
				</React.Fragment>
			);
		}

		let cdnDetails;
		if ( ! props.wphbData.isMultisite ) {
			if ( props.wphbData.isMember ) {
				cdnDetails =
					<Tooltip text={ __( 'Enable WPMU DEV CDN', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
						<Toggle id="use_cdn" checked={ cdn && props.wphbData.isMember } disabled={ ! props.wphbData.isMember } onChange={ toggleCDN } />
					</Tooltip>;
			} else {
				cdnDetails =
					<Tooltip text={ __( 'Host your files on WPMU DEV’s blazing-fast CDN', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
						<Button url={ props.wphbData.links.cdnUpsell } target="blank" text={ __( 'UPGRADE TO PRO', 'wphb' ) } classes={ [ 'sui-button', 'sui-button-purple' ] } />
					</Tooltip>;
			}
		} else if ( cdn && props.wphbData.isMember ) {
			cdnDetails =
				<Tooltip text={ __( 'The Network Admin has the WPMU DEV CDN turned on', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
					<Icon classes="sui-icon-check-tick sui-md sui-info" />
				</Tooltip>;
		} else {
			cdnDetails =
				<Tooltip text={ __( 'The Network Admin has the WPMU DEV CDN turned off', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
					<Tag value={ __( 'Disabled', 'wphb' ) } type="disabled" />
				</Tooltip>;
		}

		let delayDetails;

		if ( props.wphbData.isMember ) {
			delayDetails =
				<Tooltip text={ __( 'Delay JavaScript Execution', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
					<Toggle id="delay_js" checked={ delayJs } onChange={ toggleDelay } />
				</Tooltip>;
		} else {
			delayDetails = getUnlockUpsellLink( props.wphbData.links.delayUpsell, 'delayjs' );
		}

		let criticalCssDetails;

		if ( props.wphbData.isMember ) {
			criticalCssDetails =
				<Tooltip text={ __( 'Generate Critical CSS', 'wphb' ) } classes={ [ 'sui-tooltip-top-right' ] }>
					<Toggle id="critical_css" checked={ criticalCss } onChange={ toggleCritical } />
				</Tooltip>;
		} else {
			criticalCssDetails = getUnlockUpsellLink( props.wphbData.links.criticalUpsell, 'critical_css' );
		}

		let elements = [
			{
				label: __( 'Filesize reductions', 'wphb' ),
				details: reduction,
			},
			{
				label: <React.Fragment>
					{ __( 'WPMU DEV CDN', 'wphb' ) }
					{ ! props.wphbData.isMember && <Tag type="pro" value={ __( 'Pro', 'wphb' ) } /> }
				</React.Fragment>,
				details: cdnDetails,
			},
		];

		if ( props.wphbData.isMember || ! props.wphbData.links.isEoPage ) {
			const eoElements = [
				{
					label: <React.Fragment>
						{ __( 'Delay JavaScript Execution', 'wphb' ) }
						{ ! props.wphbData.isMember && <Tag type="pro" value={ __( 'Pro', 'wphb' ) } /> }
					</React.Fragment>,
					details: delayDetails,
				},
				{
					label: <React.Fragment>
						{ __( 'Generate Critical CSS', 'wphb' ) }
						{ ! props.wphbData.isMember && <Tag type="pro" value={ __( 'Pro', 'wphb' ) } /> }
					</React.Fragment>,
					details: criticalCssDetails,
				},
			];

			elements = [...elements, ...eoElements ];
		}

		return <List elements={ elements } />;
	};

	if ( ! loading ) {
		return (
			<BoxSummary
				loading={ loading || ! hasResolved }
				brandingHeroImage={ props.wphbData.brandingHeroImage }
				hideBranding={ Boolean( props.wphbData.hideBranding ) }
				summarySegmentLeft={ getSummarySegmentLeft() }
				summarySegmentRight={ getSummarySegmentRight() }
			/>
		);
	} else {
		return null;
	}
};
