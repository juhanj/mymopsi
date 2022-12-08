'use strict';

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

const SEC = 1000; // One second, in milliseconds
const MIN = 60; // seconds
const HOUR = 3600; // 60 * MIN seconds
const DAY = 86400; // 24 * HOUR seconds

const GMAP_MINZOOM = 3;
const GMAP_MAXZOOM = 20;
const GMAP_FINLAND = { lat: 62.25, lng: 26.39 };
const GMAP_INITZOOM = 3;

/**
 * It's a non-breaking space character.
 * @type {string}
 */
const NBSP = " ";

export {
	KB,
	MB,
	GB,
	SEC,
	MIN,
	HOUR,
	DAY,
	GMAP_MINZOOM,
	GMAP_MAXZOOM,
	GMAP_FINLAND,
	GMAP_INITZOOM,
	NBSP,
}