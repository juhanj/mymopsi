'use strict';

import {MIN, HOUR, NBSP} from "./constants.js";

export class Common {

	/**
	 * Format meters into human readable format.
	 * < 1k meters => 1000 meters (no decimals)
	 * > 1k meters => 1,0 km (with decimal)
	 * > 10k meters => 10 km (no decimal)
	 *
	 * @param {int|float} distance in meters
	 * @param {string}    unit     'm'||'km' ; If given unit is KM, skip meter formatting
	 * @param {int[]}     bounds   At what point switch from m to km, and km without decimals
	 *
	 * @return {string}
	 */
	static fDistance ( distance, unit = 'm', bounds = [ 1000, 10 ] ) {
		distance = Math.round( distance );
		let formatted;

		// Format result in meters
		if ( distance < (bounds[0] ?? 1000) && unit === 'm' ) {
			formatted = distance.toLocaleString( 'fi-FI', { maximumFractionDigits: 0 } ) + ' m';
		}
		// Format in kilometers
		else {
			if ( unit === 'm' ) {
				distance /= 1000;
			}
			// Give result with 1 decimal point precision if less than 10 km
			if ( distance < (bounds[1] ?? 10) ) {
				formatted = distance.toLocaleString( 'fi-FI', { maximumFractionDigits: 1 } ) + ' km';
			}
			// No decimal points for >10 km
			else {
				formatted = distance.toLocaleString( 'fi-FI', { maximumFractionDigits: 0 } ) + ' km';
			}
		}

		return formatted;
	}

	/**
	 * Format time into human readable format
	 * < 60 s => seconds, no decimal
	 * > 60 s => minutes, no decimal
	 * > 60 min => hours with minutes
	 * > 10 hours => hours, no minutes, no decimals
	 *
	 * @param {int|float} time      in seconds
	 * @param {string}    unit      s || m || h ;
	 *                             Skip to correct formatting level
	 * @param {int[]}     bounds    Bounds between different levels of formatting
	 *                             Default: [ 60 (s), 60*60 (m), 60*60*10 (h) ]
	 *
	 * @return {string}
	 */
	static fTime ( time, unit = 's', bounds = [ MIN, HOUR, HOUR * 10 ] ) {
		// If number not second, convert to second
		if ( unit === 'm' ) {
			time *= MIN;
		} else if ( unit === 'h' ) {
			time *= HOUR;
		}

		time = Math.round( time );
		let formatted;

		// seconds
		if ( time < bounds[0] ) {
			formatted = `${time} s`;
		}

		// minutes
		else if ( time < bounds[1] ) {
			time /= MIN;

			if ( time < MIN ) {
				formatted = `${Math.round( time )} m`;
			}
		}

		// hours < 10h
		else if ( time < bounds[2] ) {
			time /= HOUR;
			formatted = time.toLocaleString( 'fi-FI', { maximumFractionDigits: 1 } ) + ' h';
		} else {
			time /= HOUR;
			formatted = time.toLocaleString( 'fi-FI', { maximumFractionDigits: 0 } ) + ' h';
		}

		return formatted;
	}

	/**
	 * Format bytes as human-readable text.
	 *
	 * Taken from {@link https://stackoverflow.com/a/14919494|Stackoverflow}
	 *
	 * @param {int} bytes Number of bytes.
	 * @param {boolean} si True to use metric (SI) units, aka powers of 1000. False to use
	 *           binary (IEC), aka powers of 1024.
	 * @param {int} dp Number of decimal places to display.
	 *
	 * @return {string} Formatted string.
	 */
	static fFileSize ( bytes, si = false, dp = 1 ) {
		const thresh = si ? 1000 : 1024;

		if ( Math.abs( bytes ) < thresh ) {
			return bytes + ' B';
		}

		const units = si
			? [ 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ]
			: [ 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB' ];
		let u = -1;
		const r = 10 ** dp;

		do {
			bytes /= thresh;
			++u;
		} while ( Math.round( Math.abs( bytes ) * r ) / r >= thresh && u < units.length - 1 );


		return bytes.toFixed( dp ) + ' ' + units[u];
	}

	/**
	 * Give GPS lat-lng coordinate, get Degree Minute Second format back
	 * @param {Object} location .lat .lng
	 * @returns {string} Degree Minute Second GPS formatted location
	 */
	static fGPSDecimalToDMS ( location ) {
		let latitude = toDegreesMinutesAndSeconds( location.lat );
		let latitudeCardinal = location.lat >= 0 ? "N" : "S";

		let longitude = toDegreesMinutesAndSeconds( location.lng );
		let longitudeCardinal = location.lng >= 0 ? "E" : "W";

		return latitude + NBSP + latitudeCardinal + ", " + longitude + NBSP + longitudeCardinal;

		/**
		 * Nested function, because this is used nowhere else
		 * @param {number} coordinate Latitude or langitude
		 * @returns {string} [degree]° [minute]′[second]″
		 *      There's a NBSP between degree and minute
		 */
		function toDegreesMinutesAndSeconds ( coordinate ) {
			let absolute = Math.abs( coordinate );
			let degrees = Math.floor( absolute );
			let minutesNotTruncated = (absolute - degrees) * MIN;
			let minutes = Math.floor( minutesNotTruncated );
			let seconds = Math.floor( (minutesNotTruncated - minutes) * MIN );

			return degrees + "°" + NBSP + minutes + "′" + seconds + "″";
		}
	}
}