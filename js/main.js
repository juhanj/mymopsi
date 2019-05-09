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

/**
 * @param {string} name
 * @param value
 * @param {int} days
 */
function setCookie(name, value, days) {
	let expires = "";
	let date;
	if (days) {
		date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

/**
 * @param {string} name
 * @returns {string|null}
 */
function getCookie( name ) {
	let nameEQ = name + "=";
	let cookies_array = document.cookie.split(';');
	let cookie, i;
	for (i = 0; i < cookies_array.length; i++) {
		cookie = cookies_array[i];
		while ( cookie.charAt(0) === ' ' ) {
			cookie = cookie.substring(1, cookie.length);
		}
		if ( cookie.indexOf(nameEQ) === 0 ) {
			return cookie.substring(nameEQ.length, cookie.length);
		}
	}
	return null;
}

/**
 * @param {string} name
 */
function deleteCookie(name) {
	document.cookie = name + '=; Max-Age=-1;';
}
}
