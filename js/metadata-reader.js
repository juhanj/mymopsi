"use strict";

import {Ajax as req} from "./modules/export.js";

/* *************************************************
 * Functions
 * *************************************************/

function processMetadataResult ( response ) {
	let metadata = response.result.metadata[0];
	delete metadata.SourceFile;
	delete metadata.ExifTool;
	delete metadata.File;
	console.log( metadata );

	let tempHTML = '';

	for ( let [ title, section ] of Object.entries( metadata ) ) {
		tempHTML += `<h2 class="metadata-section-title">${title}</h2>\n`;

		for ( let [ dt, dd ] of Object.entries( section ) ) {
			tempHTML += `<dl class="metadata-section-list margins-off">
				<dt class="metadata-section-term">${dt}</dt>
				<dd class="metadata-section-detail">${dd}</dd>
				</dl>\n`;
		}
	}

	loader.hidden = true;
	metadataContainer.innerHTML = tempHTML;
}

function handleFileChange () {
	// form.hidden = true;
	imageElement.hidden = false;

	// Set the <img>'s src to a reference URL to the selected file
	imageElement.src = URL.createObjectURL( fileInput.files.item( 0 ) )

	imageContainer.hidden = false;
	loader.hidden = false;
	form.hidden = true;

	let formdata = new FormData( form );

	req.sendForm( formdata )
		.then( processMetadataResult );
}

/* *************************************************
 * "Main" code
 * *************************************************/

let form = document.getElementById( 'upload-form' );
let fileInput = document.getElementById( 'fileInput' );
let imageContainer = document.getElementById( 'imageContainer' );
let imageElement = document.getElementById( 'imagePreview' );
let loader = document.getElementById( 'loader' );
let metadataContainer = document.getElementById( 'imageMetadata' );

fileInput.onchange = handleFileChange;
