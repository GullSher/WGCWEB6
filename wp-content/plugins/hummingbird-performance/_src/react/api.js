/* global ajaxurl */
/* global wphb */

/**
 * External dependencies.
 */
import { fetch } from 'whatwg-fetch';

const methods = [ 'get', 'post', 'put', 'delete' ];

/**
 * HB API class.
 *
 * Uses jQuery.ajax().
 *
 * @property {Function} get    GET request.
 * @property {Function} post   POST request.
 * @property {Function} put    PUT request.
 * @property {Function} delete DELETE request.
 */
export default class HBAPIFetch {
	/**
	 * Class constructor.
	 */
	constructor() {
		methods.forEach( ( method ) => {
			this[ method ] = this._setupAjaxAPI( method );
		} );
	}

	/**
	 * Setup AJAX endpoints.
	 *
	 * @param {string} method
	 * @return {function(*=, *=): Promise<*>} Response.
	 * @private
	 */
	_setupAjaxAPI( method ) {
		// Can't use body with GET requests? Not a problem - we'll convert GET to a POST request.
		if ( 'get' === method ) {
			method = 'post';
		}

		return ( endpoint = '/', data = false ) => {
			const fetchObject = {
				credentials: 'same-origin',
				method,
				headers: {
					'Content-Type':
						'application/x-www-form-urlencoded; charset=utf-8',
				},
				body:
					'action=wphb_react_' +
					endpoint +
					'&_wpnonce=' +
					wphb.nonces.HBFetchNonce +
					'&data=' +
					JSON.stringify( data ),
			};

			return fetch( ajaxurl, fetchObject ).then( ( response ) => {
				return response.json().then( ( json ) => {
					return response.ok
						? json.data
						: Promise.reject( json.data );
				} );
			} );
		};
	}
}
