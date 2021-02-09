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

const MIN = 60; // seconds
const HOUR = 3600; // 60 * MIN
const DAY = 86400; // 24 * HOUR

export {
	KB,
	MB,
	GB,
	MIN,
	HOUR,
	DAY
}