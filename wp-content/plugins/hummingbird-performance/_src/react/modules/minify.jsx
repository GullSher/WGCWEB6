/* global wphbReact */
/* global WPHB_Admin */

/**
 * External dependencies
 */
import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { dispatch } from '@wordpress/data';
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import HBAPIFetch from '../api';
import AutoAssets from '../views/minify/assets-auto';
import { ManualAssets } from '../views/minify/assets-manual';
import { MinifySummary } from '../views/minify/summary';
import { STORE_NAME } from '../data/minify';

/**
 * MinifyPage component.
 *
 * @since 2.7.2
 *
 * @param {Object} props
 * @return {JSX.Element} Manual or Auto assets component.
 */
export const MinifyPage = ( props ) => {
	const api = new HBAPIFetch();
	const [ loading, setLoading ] = useState( true );

	/**
	 * Clear asset optimization cache.
	 */
	const clearCache = () => {
		setLoading( true );

		api.post( 'minify_clear_cache' )
			.then( ( response ) => {
				dispatch( STORE_NAME ).invalidateResolution( 'getAssets' );
				if ( response.isCriticalActive ) {
					window.wphbMixPanel.track( 'critical_css_cache_purge', {
						location: 'ao_settings'
					} );
				}
				const message = __( 'Your cache has been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' );
				WPHB_Admin.notices.show( message ); // eslint-disable-line camelcase
				setLoading( false );
			} )
			.catch( window.console.log );
	};

	/**
	 * Re-check files.
	 */
	const reCheckFiles = () => {
		setLoading( true );

		api.post( 'minify_recheck_files' )
			.then( () => window.location.reload() )
			.catch( window.console.log );
	};

	if ( 'advanced' === props.wphbData.mode ) {
		return (
			<ManualAssets
				loading={ loading }
				api={ api }
				mode={ props.wphbData.mode }
				clearCache={ clearCache }
				reCheckFiles={ reCheckFiles }
				showModal={ props.wphbData.showModal }
				filters={ props.wphbData.filters }
				links={ props.wphbData.links }
				isMember={ Boolean( props.wphbData.isMember ) } />
		);
	}

	return (
		<AutoAssets
			loading={ loading }
			api={ api }
			mode={ props.wphbData.mode }
			clearCache={ clearCache }
			reCheckFiles={ reCheckFiles }
			showModal={ props.wphbData.showModal } />
	);
};

MinifyPage.propTypes = {
	wphbData: PropTypes.object,
};

domReady( function() {
	const minify = document.getElementById( 'wrap-wphb-minify' );
	if ( minify ) {
		ReactDOM.render( <MinifyPage wphbData={ wphbReact } />, minify );
	}

	const summary = document.getElementById( 'wrap-wphb-summary' );
	if ( summary ) {
		ReactDOM.render( <MinifySummary wphbData={ wphbReact } />, summary );
	}
} );
