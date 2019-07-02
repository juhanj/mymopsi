"use strict";

/**
 * Send a JSON request to server, receive JSON back.
 * Usage: sendJSON(params).then((jsonResponse) => {});
 * @param data Changed to JSON before sending
 * @param {string} url optional, default == ./ajax-handler.php
 * @returns {Promise<object>} JSON
 */
async function sendJSON(data, url = './ajax-handler.php') {
	let response = await fetch(url, {
		method: 'post',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify(data)
	});
	return await response.json();
}

/**
 * Send a POST request to server, receive JSON back.
 * Usage: sendForm(formdata).then((jsonResponse) => {});
 * @param {FormData} data Form-element, must be an FormData object
 * @param {string} url optional, default == ./ajax-handler.php
 * @returns {Promise<object>} JSON
 */
async function sendForm(data, url = './ajax-handler.php') {
	let response = await fetch(url, {
		method: 'post',
		credentials: 'same-origin',
		// explicitly no Content-Type with FormData
		body: data
	});
	return await response.json();
}

/**
 * Set cookies with given name, value, and expiry date
 * @param {string} name
 * @param {string} [value='']
 * @param {int} [days=30] How long will the browser store the cookie, in days.
 */
function setCookie(name, value = '', days = 30) {
	let date = new Date();
	date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
	let expires = "; expires=" + date.toUTCString();

	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

/**
 * @param {string} name
 * @returns {string|null}
 */
function getCookie(name) {
	let nameEQ = name + "=";
	let cookies_array = document.cookie.split(';');
	let cookie, i;
	for (i = 0; i < cookies_array.length; i++) {
		cookie = cookies_array[i];
		while (cookie.charAt(0) === ' ') {
			cookie = cookie.substring(1, cookie.length);
		}
		if (cookie.indexOf(nameEQ) === 0) {
			return cookie.substring(nameEQ.length, cookie.length);
		}
	}
	return null;
}

/**
 * Delete the cookie by setting max-age to -1.
 * @param {string} name
 */
function deleteCookie(name) {
	document.cookie = name + '=; Max-Age=-1;';
}

/**
 * Add a collection to the local list of collection kept in the cookies.
 * @param {string} name
 * @param {string} uid
 */
function addCollectionToCookies(name, uid) {
	let collections = JSON.parse(getCookie('collections'));

	if ( !collections ) {
		collections = [];
	}

	collections.push({name, uid});

	setCookie('collections', JSON.stringify(collections));
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
 * Gigabyte, MB * MB (Probably won't need this one)
 * @type {number} 1 073 741 824
 */
const GB = 1073741824;

let dialog = document.querySelector('dialog');
dialogPolyfill.registerDialog(dialog);
