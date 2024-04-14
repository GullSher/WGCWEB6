/**
 * Save options to state.
 *
 * @param {Object} options
 * @return {Object} Options
 */
export const loadOptions = ( options ) => {
	return {
		type: 'LOAD_OPTIONS',
		options
	};
};

/**
 * Save assets to state.
 *
 * @param {Object} assets
 * @return {Object} Assets
 */
export const loadAssets = ( assets ) => {
	return {
		type: 'LOAD_ASSETS',
		assets
	};
};
