"use strict";
/**************************************************
 * Functions
 **************************************************/
/**
 * @param {Object} response
 * @param {Object} response.request
 * @param {string} response.request.batchSizeBytes
 * @param {string} response.request.batchIndex
 * @param {Object} response.result
 * @param {boolean} response.result.success
 * @param {boolean} response.result.error
 * @param {Object[]} response.result.good_uploads
 * @param {string} response.result.good_uploads.new_ruid
 * @param {string} response.result.good_uploads.mime
 * @param {string} response.result.good_uploads.hash
 * @param {string} response.result.good_uploads.final_path
 * @param {string} response.result.good_uploads.latitude
 * @param {string} response.result.good_uploads.longitude
 * @param {Object[]} response.result.failed_uploads
 * @param {string} response.result.failed_uploads.mime
 * @param {string} response.result.failed_uploads.hash
 * @param {Object[]} response.result.metadata
 */
function handleRequestResponse ( response ) {
	console.log( `Received response for batch ${response.request.batchIndex}` );
	console.log( `\tSuccess: ${response.result.good_uploads.length}` );
	console.log( `\tFailed : ${response.result.failed_uploads.length}` );
	console.log( response );

	let good = response.result.good_uploads;
	let bad = response.result.failed_uploads;
	let totalBitsProcessed = 0;

	if ( Array.isArray( good ) && good.length ) {
		let temp = '';
		good.forEach( ( image ) => {
			temp += `<p id="${image.new_ruid}">${image.name}, ${image.size}, ${image.mime}, ${image.lastModified}</p>`;
			totalBitsProcessed += image.size;
		} );
		successfulUploads.insertAdjacentHTML( 'beforeend', temp );
	}

	if ( Array.isArray( bad ) && bad.length ) {
		let temp = '';
		bad.forEach( ( image ) => {
			temp += `<p id="${image.name}">${image.name}, ${image.size}, ${image.mime}, ${image.hash}</p>`;
			totalBitsProcessed += image.size;
		} );
		failedUploads.insertAdjacentHTML( 'beforeend', temp );
	}

	progressBarFiles.value += good.length + bad.length;
	progressBarBits.value += totalBitsProcessed;
}

/**************************************************
 * "Main" code
 **************************************************/
let maxBatchSize = 10 * MB;

let uploadForm = document.getElementById( 'upload-form' );
let fileInputpluslabel = document.getElementById( 'fileinput-label' );
let fileInput = document.getElementById( 'file-input' );
let submitButton = document.getElementById( 'submit-button' );

let progressBars = document.getElementById( 'progress-bar-container' );
let progressBarFiles = document.getElementById( 'progress-files' );
let progressBarBits = document.getElementById( 'progress-bits' );

let filesInfo = document.getElementById( 'files-info' );
let successfulUploads = document.getElementById( 'successful-uploads' );
let failedUploads = document.getElementById( 'failed-uploads' );

fileInput.onchange = () => {
	fileInputpluslabel.hidden = true;
	submitButton.hidden = false;

	let temp = '';
	let totalFileSize = 0;
	Array.from( fileInput.files ).forEach( ( file ) => {
		temp += `<p id="${file.name}">${file.name}, ${file.size}, ${file.type}, ${file.lastModified}</p>`;
		totalFileSize += file.size;
	} );

	filesInfo.insertAdjacentHTML( 'beforeend', temp );
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
	failedUploads.hidden = false;
	successfulUploads.hidden = false;
	filesInfo.hidden = true;

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
			// Some debuggin logs...
			console.log( `File ${fileInput.files[i].name} added to batch ${currentBatchIndx}` );
			// Adding the image to current batch (this gets emptied after request/batch is sent)
			formData.append( 'images[]', fileInput.files[i] );
			// Current batch size is used to determine whenthe batch is full and when to leave the while-loop
			currentBatchSize += fileInput.files[i].size;
			// Remove element from the "Waiting uploads"-list, for some better user-feedback
			document.getElementById( fileInput.files[i].name ).remove();
			i++; // Advance for-loop, inside said for-loop
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

			failedUploads.insertAdjacentHTML(
				'beforeend',
				`<p>${fileInput.files[i].name},${fileInput.files[i].size}</p>`
			);
		}
	}
};
