/**
 * Internal dependencies
 */
import { fetchFromAPI } from './controls';
import { loadOptions, loadAssets } from './actions';

/**
 * Resolver function for options
 *
 * Name must match the selector it is resolving for.
 *
 * @return {Object} Options object.
 */
export function* getOptions() {
	const path = '/hummingbird/v1/minify/options';
	const options = yield fetchFromAPI( path );

	if ( options ) {
		return loadOptions( options );
	}

	error_log( 'Error fetching from REST API' );
}

/**
 * Resolver function for assets
 *
 * Name must match the selector it is resolving for.
 *
 * @return {Object} Options object.
 */
export function* getAssets() {
	const path = '/hummingbird/v1/minify/assets';
	const assets = yield fetchFromAPI( path );

	if ( assets ) {
		return loadAssets( assets );
	}

	error_log( 'Error fetching from REST API' );
}
