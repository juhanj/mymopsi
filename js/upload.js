"use strict";

/**
 * @param {Object} response
 * @param {Object} response.request
 * @param {string} response.request.batchSizeBytes
 * @param {Object} response.result
 * @param {Object[]} response.result.success
 * @param {Object[]} response.result.errors
 */
function handleRequestResponse ( response ) {
	console.log( `Received response for batch` );
	console.log(response);
}

let maxBatchSize = 10 * MB;

let uploadForm = document.getElementById( 'upload-form' );
let fileInput = document.getElementById( 'file-input' );
let submitButton = document.getElementById( 'submit-button' );

let progressBars = document.getElementById( 'progress-bar-container' );
let progressBarFiles = document.getElementById( 'progress-files' );
let progressBarBits = document.getElementById( 'progress-bits' );

let filesInfo = document.getElementById( 'files-info' );
let successfulUploads = document.getElementById( 'successful-uploads' );
let failedUploads = document.getElementById( 'failed-uploads' );

fileInput.onchange = () => {
	submitButton.hidden = false;

	let temp = '';
	let totalFileSize = 0;
	Array.from( fileInput.files ).forEach( ( file ) => {
		temp += `<p>${file.name}, ${file.size}, ${file.type}, ${file.lastModified}</p>`;
		totalFileSize += file.size;
	} );

	filesInfo.innerHTML = temp;
	filesInfo.hidden = false;

	progressBarFiles.max = fileInput.files.length;
	progressBarBits.max = totalFileSize;
};

uploadForm.onsubmit = ( event ) => {
	// Prevent default browser behaviour, in this case submitting a form normally (causing page-load)
	event.preventDefault();
	// Check that an upload isn't already in progress. We don't want to sent the same upload twice.
	// Also check that there are files selected, just in case.

	uploadForm.hidden = true;
	progressBars.hidden = false;

	/**
	 * while-true loop for sending files in batches
	 * create new formdata
	 * add to formdata as the loop goes through array.
	 * send formdata when maxBatchSize reached
	 * empty formdata, start loop
	 */
	let formData = new FormData( uploadForm );
	let currentBatchIndx = 1;

	for ( let i = 0; i < fileInput.files.length; i++ ) {
		// set current batch size to 0
		let currentBatchSize = 0;

		// Delete images[] that was automatically added when creating FormData
		// I will manually add the images one by one, to be sent in batches
		formData.delete( 'images[]' );

		// Looping through fileInput.files, also moving the for-loop forwards
		// adding files to the formData, according to batchsize
		while ( fileInput.files[i] && (currentBatchSize + fileInput.files[i].size) < maxBatchSize ) {
			console.log( `File ${fileInput.files[i].name} added to batch ${currentBatchIndx}` );
			formData.append( 'images[]', fileInput.files[i] );
			currentBatchSize += fileInput.files[i].size;
			i++;
		}

		// Check that we are actually sending something, and not just empty requests.
		if ( currentBatchSize > 0 ) {
			// Add batch-size and index to the request data. These are not used by server, just for debuggin purposes
			formData.set( 'batchSizeBytes', currentBatchSize.toString() );
			formData.set( 'batchIndex', currentBatchIndx.toString() );

			sendForm( formData )
				.then( handleRequestResponse );

			console.log( `Batch ${currentBatchIndx} sent, and waiting for response...` );
			currentBatchIndx++;
		} else {
			console.log( `File ${fileInput.files[i].name} too big to send, skipping...` );
			progressBarFiles.value++;
			progressBarBytes.value += fileInput.files[i].size;

			failedUploads.append( `<p>${fileInput.files[i].name},${fileInput.files[i].size}</p>` );
		}
	}
};
