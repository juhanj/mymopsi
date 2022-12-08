"use strict";

import {MB, Ajax as req, Common} from './modules/export.js';

/* *************************************************
 * Functions
 * *************************************************/

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
	console.log( `Received response for batch ${response.request.batchIndex}`, response );

	let good = response.result.good_uploads || [];
	let bad = response.result.failed_uploads || [];

	let batchBitsProcessed = 0;

	let checkmark = `<span class='material-icons' style="color: green;">check</span>`;
	let failedMark = `<span class='material-icons' style="color: red;">close</span>`;

	// Successful uploaded images
	good.forEach( ( image ) => {
		let row = document.querySelector(`[data-name='${image.name}']`);
		row.querySelector("td.success-indicator").innerHTML = checkmark;

		if ( image.latitude ) {
			row.querySelector("td.location-indicator").innerHTML = checkmark;
		}

		batchBitsProcessed += image.size;
		++successfulUploadsNumber;
	} );

	// Failed uploads
	bad.forEach( ( image ) => {
		let row = document.querySelector(`[data-name='${image.name}']`);
		row.querySelector("td.success-indicator").innerHTML = failedMark;

		batchBitsProcessed += image.size;
	} );

	progressBarFiles.value += (good.length + bad.length);
	progressBarBits.value += batchBitsProcessed;
	progressBarBatches.value++;

	doneFilesNumber.innerText = progressBarFiles.value;
	doneBytesNumber.innerText = Common.fFileSize(progressBarBits.value);
	doneBatchesNumber.innerText = progressBarBatches.value;

	if ( progressBarFiles.value === progressBarFiles.max ) {
		progressBars.hidden = true;
		progressBars.style.display = 'none';
		let finishedContainer = document.getElementById("uploadFinishedBox");
		let finishedFilesUploaded = document.getElementById("finishedFilesUploaded")
		finishedFilesUploaded.innerText = successfulUploadsNumber + " / " + totalFilesNumber.innerText;
		finishedContainer.hidden = false;
		finishedContainer.style.display = "flex";
	}
}

/**
 * When submit button is called, the images are sent in batches to the server
 * @param {Event} event
 */
function submitFiles ( event ) {
	// Prevent default browser behaviour, in this case submitting a form normally (causing page-load)
	event.preventDefault();

	uploadForm.hidden = true;
	progressBars.hidden = false;
	progressBars.style.display = 'flex';

	/*
	 * while-true loop for sending files in batches
	 * create new formdata
	 * add to formdata as the loop goes through array.
	 * send formdata when maxBatchSize reached
	 * empty formdata, start loop
	 */
	let formData = new FormData( uploadForm );
	let currentBatchIndx = 1;

	for ( let i = 0; i < fileInput.files.length; ) {
		// set current batch size to 0
		let currentBatchSizeBytes = 0;
		let currentBatchSizeFiles = 0;

		// Delete images[] that was automatically added when creating FormData
		// I will manually add the images one by one, to be sent in batches
		formData.delete( 'images[]' );

		// Looping through fileInput.files, also moving the for-loop forwards
		// adding files to the formData, according to batch size
		// This while loop will keep adding files to formData, until batch is full
		while ( fileInput.files[i]
			&& ((currentBatchSizeBytes + fileInput.files[i].size) < maxBatchSize)
			&& (currentBatchSizeFiles < maxBatchFiles)
		) {
			// Adding the image to current batch (this gets emptied after request/batch is sent)
			formData.append( 'images[]', fileInput.files[i] );
			// Current batch size is used to determine whenthe batch is full and when to leave the while-loop
			currentBatchSizeBytes += fileInput.files[i].size;
			++currentBatchSizeFiles;

			++i; // Advance for-loop, inside said for-loop
		}

		// Check that we are actually sending something, and not just empty requests.
		if ( currentBatchSizeBytes > 0 ) {
			console.log( `Batch ${currentBatchIndx} sent, and waiting for response..`,
				`${currentBatchSizeBytes} | ${currentBatchSizeFiles}` );
			console.info( `Batch ${currentBatchIndx}: `, formData );

			// Add batch-size and index to the request data. These are not used by
			//  server, just for debuggin purposes
			formData.set( 'batchSizeBytes', currentBatchSizeBytes.toString() );
			formData.set( 'batchIndex', currentBatchIndx.toString() );
			req.sendForm( formData )
				.then( handleRequestResponse );
			currentBatchIndx++;
		} else {
			console.log( `File ${fileInput.files[i].name} too big to send, skipping...` );
			progressBarFiles.value++;
			progressBarBits.value += fileInput.files[i].size;

			doneFilesNumber.innerText = progressBarFiles.value;
			doneBytesNumber.innerText = Common.fFileSize(progressBarBits.value);


			// let row = document.querySelector(`[data-name='${image.name}']`);
			// row.querySelector("td.success-indicator").innerHTML = failedMark;

			let row = document.querySelector(`[data-name='${fileInput.files[i].name}']`);
			row.querySelector("td.success-indicator").innerHTML =
				`<span class='material-icons' style="color: red;">close</span>`;

			++i; // Advance for-loop, inside said for-loop
		}

		progressBarBatches.max = currentBatchIndx-1;
		totalBatchesNumber.innerText = (currentBatchIndx-1).toString();
	}
}

/**
 * Called when files are dropped into the file-input
 * Hides input, shows submit-button, prints file-info, and prepares the progressbar
 */
function handleFileInputChange () {
	otherUploadMethodsContainer.hidden = true;
	fileInputLabelText.hidden = true;
	fileInputLabelText.style.display = 'none';
	fileInput.style.marginTop = '0';
	fileInput.style.height = 'auto';
	submitButton.hidden = false;

	let sizeWarning = `<span class='material-icons' style="color: grey;">warning_amber</span>`;

	let temp = '';
	let totalFileSize = 0;
	let i = 1;
	Array.from( fileInput.files ).forEach( ( file ) => {
		console.log( file );

		let filetype = file.type.split( '/', 2 );
		filetype = (filetype.length > 1 && filetype[0] === 'image')
			? filetype[1].toUpperCase()
			: "?!";

		let nameWithoutExtension = file.name.replace(/\.[^/.]+$/, "");
		let lastModifiedFormattedDate = dayjs(file.lastModified).format('YYYY-MM-DD HH:mm');

		let sizeFormattedWithUnit = Common.fFileSize(file.size);
		if (file.size > maxBatchSize) {
			sizeFormattedWithUnit = sizeWarning + sizeFormattedWithUnit;
		}

		temp +=
			`<tr id="${i}" data-name="${file.name}">
				<td class="text success-indicator"></td>
				<td class="text">${nameWithoutExtension}</td>
				<td class="number">${sizeFormattedWithUnit}</td>
				<td class="center">${filetype}</td>
				<td class="number">${lastModifiedFormattedDate}</td>
				<td class="center location-indicator"></td>
			</tr>`;
		totalFileSize += file.size;
		file.HTMLTableRowID = i++;
	} );

	waitingFilesTableBody.innerHTML = temp;
	waitingFilesContainer.hidden = false;

	progressBarFiles.max = fileInput.files.length;
	progressBarBits.max = totalFileSize;
	progressBarBatches.max = 1;

	totalFilesNumber.innerText = fileInput.files.length.toString();
	totalBytesNumber.innerText = Common.fFileSize(totalFileSize);
	totalBatchesNumber.innerText = (1).toString();
}

/* *************************************************
 * "Main" code
 * *************************************************/

let maxBatchSize = 10 * MB;
// Server-side limit, can't change without editing actual config-files
let maxBatchFiles = 20;

// Form elements
let uploadForm = document.getElementById( 'upload-form' );
let fileInputPluslabel = document.getElementById( 'fileinput-label' );
let fileInputLabelText = document.getElementById( 'file-input-label-text' );
let fileInput = document.getElementById( 'file-input' );
let submitButton = document.getElementById( 'submit-button' );

// Progress bars
// Probably too many of them...
// Container for them all:
let progressBars = document.getElementById( 'progress-bar-container' );
// Files:
let progressBarFiles = document.getElementById( 'progress-files' );
let doneFilesNumber = document.getElementById( 'doneFiles' );
let totalFilesNumber = document.getElementById( 'totalFiles' );
let successfulUploadsNumber = 0;
// Bytes / Bits (not sure which one, doesn't matter)
let progressBarBits = document.getElementById( 'progress-bits' );
let doneBytesNumber = document.getElementById( 'doneBytes' );
let totalBytesNumber = document.getElementById( 'totalBytes' );
// Batches (this is not necessary but for testing purposes)
let progressBarBatches = document.getElementById( 'progress-batches' );
let doneBatchesNumber = document.getElementById( 'doneBatches' );
let totalBatchesNumber = document.getElementById( 'totalBatches' );

// Upload files info (before / after (success&fail))
let waitingFilesContainer = document.getElementById( 'files-info' );
let waitingFilesTableBody = document.getElementById( 'selectedTableBody' );

let otherUploadMethodsContainer = document.getElementById( 'otherUploadMethods' );

fileInput.onchange = handleFileInputChange;

uploadForm.onsubmit = submitFiles;
