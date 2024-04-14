/**
 * Get option.
 *
 * @param {Object} state
 * @param {string} option
 * @return {boolean} CDN state.
 */
export const getOption = ( state, option ) => {
	return state.options?.[ option ];
};

/**
 * Get options.
 *
 * @param {Object} state
 * @return {Object} Options object.
 */
export const getOptions = ( state ) => {
	return state.options;
};

/**
 * Get assets.
 *
 * @param {Object} state
 * @return {Object} Assets object.
 */
export const getAssets = ( state ) => {
	return state.assets;
};
