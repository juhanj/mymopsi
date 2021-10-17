"use strict";

import {Cookies} from './modules/export.js';

document.querySelectorAll("input[name='lang']").forEach((input) => {
	// Add a listener for user made changes
	// Changes lang
	input.addEventListener('click', (element) => {
		Cookies.setCookie(
			'mymopsi_lang',
			element.target.value,
			999
		);
		location.reload();
	});
});