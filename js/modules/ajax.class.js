'use strict';

export class Ajax {
	/**
	 * Send a JSON request to server, receive JSON back.
	 * Usage: sendJSON(params).then( function(jsonResponse) );
	 * @param data Changed to JSON string before sending
	 * @param {string} url optional, default == ./ajax-handler.php
	 * @param {boolean} returnJSON
	 * @returns {Promise<object>} JSON
	 */
	static async sendJSON ( data, url = './ajax-handler.php', returnJSON = true ) {
		let response = await fetch( url, {
			method: 'post',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify( data )
		} );
		return (returnJSON) ? await response.json() : await response;
	}

	/**
	 * Send a POST request to server, receive JSON back.
	 * Usage: sendForm(formdata).then( function(jsonResponse) );
	 * @param {FormData} data Form-element, must be an FormData object
	 * @param {string} url optional, default = ./ajax-handler.php
	 * @param {boolean} returnJSON
	 * @returns {Promise<object>} JSON
	 */
	static async sendForm ( data, url = './ajax-handler.php', returnJSON = true ) {
		let response = await fetch( url, {
			method: 'post',
			credentials: 'same-origin',
			// explicitly no Content-Type with FormData
			body: data
		} );
		return (returnJSON) ? await response.json() : await response.text();
	}
}