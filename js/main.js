"use strict";

/**
 * Send a JSON request to server, receive JSON back.
 * Usage: sendJSON(params).then( function(jsonResponse) );
 * @param data Changed to JSON before sending
 * @param {string} url optional, default == ./ajax-handler.php
 * @param {boolean} returnJSON
 * @returns {Promise<object>} JSON
 */
async function sendJSON ( data, url = './ajax-handler.php', returnJSON = true ) {
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
async function sendForm ( data, url = './ajax-handler.php', returnJSON = true ) {
	let response = await fetch( url, {
		method: 'post',
		credentials: 'same-origin',
		// explicitly no Content-Type with FormData
		body: data
	} );
	return (returnJSON) ? await response.json() : await response;
}

/**
 * Kilobyte = 1024 bits
 * @type {number} 1024
 */
const KB = 1024;
/**
 * Megabyte, KB * KB bits
 * @type {number} 1 048 576
 */
const MB = 1048576;
/**
 * Gigabyte, MB * MB (Probably (hopefully?) won't need this one)
 * @type {number} 1 073 741 824
 */
const GB = 1073741824;