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

// TODO: Print something to user when language changed. "Refresh page to see changes."