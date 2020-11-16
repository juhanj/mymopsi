"use strict";
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
	console.log( `Received response for batch ${response.request.batchIndex}` );
	console.log( `\tSuccess: ${response.result.good_uploads.length}` );
	console.log( `\tFailed : ${response.result.failed_uploads.length}` );
	console.log( response );

	let good = response.result.good_uploads;
	let bad = response.result.failed_uploads;
	let batchBitsProcessed = 0;

	if ( Array.isArray( good ) && good.length ) {
		let temp = '';
		good.forEach( ( image ) => {
			let size = image.size;
			let lastModified = image.lastModified;
			let location = (image.latitude) ? '<i class="material-icons" style="color: green">check</i>' : '';
			let type = image.type.split('/',2);
			type = (type.length > 1 && type[0] === 'image' )
				? type[1].toUpperCase()
				: "?!";

			temp +=	`<tr>
				<td class="text">${image.name}</td>
				<td class="number">${size}</td>
				<td class="center">${type}</td>
				<td class="number">${lastModified}</td>
				<td class="number">${location}</td>
			</tr>`;
			batchBitsProcessed += image.size;

		} );
		successfulUploadsTableBody.innerHTML = temp;
	}

	if ( Array.isArray( bad ) && bad.length ) {
		let temp = '';
		bad.forEach( ( image ) => {
			let size = image.size;
			let type = image.type.split('/',2);
			type = (type.length > 1 && type[0] === 'image' )
				? type[1].toUpperCase()
				: "?!";

			temp +=	`<tr>
				<td class="text">${image.name}</td>
				<td class="number">${size}</td>
				<td class="center">${type}</td>
			</tr>`;
			batchBitsProcessed += image.size;
		} );
		failedUploadsTableBody.innerHTML = temp;
	}

	progressBarFiles.value += good.length + bad.length;
	progressBarBits.value += batchBitsProcessed;
}

/* *************************************************
 * "Main" code
 * *************************************************/
let maxBatchSize = 10 * MB;

// Form elements
let uploadForm = document.getElementById( 'upload-form' );
let fileInputpluslabel = document.getElementById( 'fileinput-label' );
let fileInputLabelText = document.getElementById( 'file-input-label-text' );
let fileInput = document.getElementById( 'file-input' );
let submitButton = document.getElementById( 'submit-button' );

// Progress bar
let progressBars = document.getElementById( 'progress-bar-container' );
let progressBarFiles = document.getElementById( 'progress-files' );
let progressBarBits = document.getElementById( 'progress-bits' );

// Upload files info (before / after (success&fail))
let waitingFilesContainer = document.getElementById( 'files-info' );
let waitingFilesTableBody = document.getElementById( 'selectedTableBody' );
let successfulUploadsContainer = document.getElementById( 'successful-uploads' );
let successfulUploadsTableBody = document.getElementById( 'successfulTableBody' );
let failedUploadsContainer = document.getElementById( 'failed-uploads' );
let failedUploadsTableBody = document.getElementById( 'failedTableBody' );


/**
 * Called when files are dropped into the file-input
 * Hides input, shows submit-button, prints file-info, and prepares the progressbar
 */
fileInput.onchange = () => {
	fileInputLabelText.hidden = true;
	fileInputLabelText.style.display = 'none';
	fileInput.style.marginTop = '0';
	fileInput.style.height = 'auto';
	submitButton.hidden = false;

	let temp = '';
	let totalFileSize = 0;
	let i = 1;
	Array.from( fileInput.files ).forEach( ( file ) => {
		let size = file.size;
		let type = file.type.split('/',2);
		type = (type.length > 1 && type[0] === 'image' )
			? type[1].toUpperCase()
			: "?!";
		let lastModified = file.lastModified;

		temp +=	`<tr id="${i}">
				<td class="center">${i}</td>
				<td class="text">${file.name}</td>
				<td class="number">${size}</td>
				<td class="center">${type}</td>
				<td class="number">${lastModified}</td>
			</tr>`;
		totalFileSize += file.size;
		file.HTMLTableRowID = i++;
	} );

	waitingFilesTableBody.innerHTML = temp;
	waitingFilesContainer.hidden = false;

	progressBarFiles.max = fileInput.files.length;
	progressBarBits.max = totalFileSize;
};

/**
 * When submit button is called, the images are sent in batches to the server
 * @param event
 */
uploadForm.onsubmit = ( event ) => {
	// Prevent default browser behaviour, in this case submitting a form normally (causing page-load)
	event.preventDefault();
	// Check that an upload isn't already in progress. We don't want to sent the same upload twice.
	// Also check that there are files selected, just in case.

	uploadForm.hidden = true;
	progressBars.hidden = false;
	failedUploadsContainer.hidden = false;
	successfulUploadsContainer.hidden = false;
	waitingFilesContainer.hidden = true;

	/*
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
			// document.getElementById( fileInput.files[i].name ).remove();
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

			failedUploadsContainer.insertAdjacentHTML(
				'beforeend',
				`<p>${fileInput.files[i].name},${fileInput.files[i].size}</p>`
			);
		}
	}
};
