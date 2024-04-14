/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';
import controls from './controls';

export const STORE_NAME = 'wphb/minify';

export const store = createReduxStore(
	STORE_NAME,
	{
		reducer,
		actions,
		selectors,
		controls,
		resolvers
	}
);

register( store );
