"use strict";

import {Ajax as req} from "./modules/export.js";

/* *************************************************
 * Functions
 * *************************************************/

function handleFileChange() {
	// form.hidden = true;
	imageElement.hidden = false;

	// Set the <img>'s src to a reference URL to the selected file
	imageElement.src = URL.createObjectURL(fileInput.files.item(0))

	let formdata = new FormData( form );
	console.log( ...formdata );

	req.sendForm( formdata )
		.then( (response) => {
			// console.log(response);
			let jsonMetadata = response.result.metadata[0];
			console.log( jsonMetadata );
		}
	);
}

/* *************************************************
 * "Main" code
 * *************************************************/

let form = document.getElementById( 'upload-form' );
let fileInput = document.getElementById( 'fileInput' );
let imageElement = document.getElementById( 'imagePreview' );

fileInput.onchange = handleFileChange;
