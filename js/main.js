"use strict";

/**
 * Send a JSON request
 * @param data Changed to JSON before sending
 * @param {string} url optional, default == ./ajax-handler.php
 * @returns {Promise<object>}
 */
async function sendJSON ( data, url = './ajax-handler.php' ) {
	let response = await fetch( url, {
		method: 'post',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify( data )
	});
	return await response.json();
}
}

}
