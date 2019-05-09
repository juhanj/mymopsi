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

//TODO: Change url-paramtere to absolute path. Possible even? --jj190330
async function sendFormData ( data, url = './ajax_handler.php' ) {
    console.log("sendFormData(): Sending Fetch FormData POST request...");
    let response = await fetch( url, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data
    });
    return await response.json();
}
