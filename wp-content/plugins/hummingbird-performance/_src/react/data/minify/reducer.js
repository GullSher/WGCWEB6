/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

export const options = ( state = {}, action ) => {
	if ( action?.type === 'SET_CDN_STATUS' ) {
		return {
			...state,
			cdn: action.options.cdn,
		};
	}

	if ( action?.type === 'LOAD_OPTIONS' ) {
		return action.options;
	}

	return state;
};

export const assets = ( state = {}, action ) => {
	if ( action?.type === 'LOAD_ASSETS' ) {
		return action.assets;
	}

	return state;
};

// export const minify => GET_ASSETS, UPDATE_ASSETS

export default combineReducers( {
	options,
	assets,
} );
