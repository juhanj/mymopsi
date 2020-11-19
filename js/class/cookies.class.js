'use strict';

export default class Cookies {
	/**
	 * Set cookies with given name, value, and expiry date.
	 * path = /mopsi_dev/mymopsi  &  SameSite = Strict
	 * @param {string} name
	 * @param {string} [value='']
	 * @param {int} [days=30] How long will the browser store the cookie, in days.
	 */
	static setCookie ( name, value = '', days = 30 ) {
		let date = new Date();
		date.setTime( date.getTime() + (days * 24 * 60 * 60 * 1000) );

		let cookieValue = `${name}=${value||''}`;
		let cookieExpire = `expires=${date.toUTCString()}`;
		let cookiePath = 'path=/mopsi_dev/mymopsi';
		let cookieSameSite = 'SameSite=Strict';

		document.cookie = `${cookieValue}; ${cookieExpire}; ${cookiePath}; ${cookieSameSite};`;
	}

	/**
	 * @param {string} name
	 * @returns {string|null}
	 */
	static getCookie ( name ) {
		let nameEQ = name + "=";
		let cookies_array = document.cookie.split( ';' );
		let cookie, i;
		for ( i = 0; i < cookies_array.length; i++ ) {
			cookie = cookies_array[i];
			while ( cookie.charAt( 0 ) === ' ' ) {
				cookie = cookie.substring( 1, cookie.length );
			}
			if ( cookie.indexOf( nameEQ ) === 0 ) {
				return cookie.substring( nameEQ.length, cookie.length );
			}
		}
		return null;
	}

	/**
	 * Delete the cookie by setting max-age to -1.
	 * @param {string} name
	 */
	static deleteCookie ( name ) {
		document.cookie = name + '=; Max-Age=-1;';
	}
}