/**
 * Control action creator that calls a control function to query a REST API endpoint.
 *
 * @param {string} path
 * @return {Object} Control action object.
 */
export const fetchFromAPI = ( path ) => {
	return {
		type: 'API_FETCH',
		path,
	};
};

export default {
	/**
	 * Control function that performs REST API fetch.
	 *
	 * @param {string} path
	 * @return {(function(Object): {request: Object, type: string})|*} Request response.
	 */
	API_FETCH( { path } ) {
		return wp.apiFetch( { path } );
	},
};
