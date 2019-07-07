"use strict";

document.querySelectorAll("input[name='lang']").forEach((input) => {
	// Add a listener for user made changes
	// Changes lang
	input.addEventListener('click', (element) => {
		setCookie(
			'mymopsi_lang',
			element.target.value,
			999
		);
	});
});
