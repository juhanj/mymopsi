"use strict";

/**
 * if file.size > 15, then ❌ Size (large image error)
 * if file.si<e > 5, ⚠ Size (large image warning)
 * if file.name len > 200, then ⚠ (large name warning, may be cut off) (255 absolute limit, will be cut on server side)
 * @param file
 * @returns {string}
 */
function checkFileForProblems ( file ) {
	let tempHTML = '';

	if ( file.size > maxFileSizeLimit ) {
		tempHTML += `<span title="Image too large">❌</span>`;
	}
	else if ( file.size > largeImgSizeWarningLimit ) {
		tempHTML += `<span title="Large image">⚠</span>`;
	}

	if ( file.name.length > maxFileNameLengthLimit ) {
		tempHTML += `<span title="Name too long">⚠</span>`;
	}

	return tempHTML;
}

/**
 * file.size / 1024 (KB)
 * file.size / 1024 / 1024 (MB) > 10, then
 * @param file
 * @returns {string}
 */
function printFileSize ( file ) {
	if ( file.size > MB ) {
		return `${Math.round(file.size/MB)} MB`;
	}
	else if ( file.size > KB ) {
		return `${Math.round(file.size/KB)} KB`;
	}
}

/**
 * Create a HTML-table of files to-be-uploaded and prints to user, before uploading.
 */
function createHTMLTableOfFiles () {
	let tempHTMLTable =
		`<table>
			<thead>
				<tr>
					<th>✔/⚠/❌</th>
					<th>Name</th>
					<th>Size</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>`;

	Array.from(fileInput.files).forEach( (file, i) => {
		tempHTMLTable +=
			`<tr id="${i}">
				<th>${checkFileForProblems(file)}</th>
				<td>${file.name}</td>
				<td>${printFileSize(file)}</td>
				<td>${new Date(file.lastModifiedDate).toLocaleString()}</td>
			</tr>`;
	});

	tempHTMLTable += "</tbody></table>";
	filesInfo.innerHTML = tempHTMLTable;
}

/**
 * @param {Object} response
 * @param {Object} response.request
 * @param {string} response.request.batchSizeBytes
 * @param {Object} response.result
 * @param {Object[]} response.result.success
 * @param {Object[]} response.result.errors
 */
function handleRequestResponse ( response ) {
	console.log(response);

	progressBarFiles.value += response.result.success.length + response.result.errors.length;
	progressBarBytes.value += Number(response.request.batchSizeBytes);

	response.result.errors.forEach( ( file, i ) => {
		let row = document.createElement( 'tr' );
		row.innerHTML = `<td>❌</td>
			<td>${file.name}</td>
			<td>${printFileSize(file)}</td>
			<td>${file.date}</td>
			<td>❌</td>`;
		finishedUploadsTableBody.appendChild( row );
	});

	response.result.success.forEach( ( file, i ) => {
		let row = document.createElement( 'tr' );
		row.innerHTML = `<td>✔</td>
			<td>${file.name}</td>
			<td>${printFileSize(file)}</td>
			<td>${file.date}</td>
			<td>✔</td>`;
		finishedUploadsTableBody.appendChild( row );
	});
}

let feedback = document.getElementById('feedback');
let uploadForm = document.getElementById('uploadForm');
let fileInput = document.getElementById('fileInput');
let submitButton = document.getElementById('submitButton');
let filesInfo = document.getElementById('filesInfo');

let modal = document.getElementById('modal');
let modalClose = document.getElementById('modal-close');
let progressBarFiles = document.getElementById('upload-progress-bar-files');
let progressBarBytes = document.getElementById('upload-progress-bar-bytes');
let finishedUploadsTableBody = document.getElementById( 'progress-upload-table-body' );

let uploadInProgress = false;

let filesArray = null;
let totalUploadSize = null;
let biggestImgSize = null;
let longestName = null;

let maxFileSizeLimit = 10 * MB;
let largeImgSizeWarningLimit = 5 * MB;
let maxFileNameLengthLimit = 200;
let maxBatchSize = 19 * MB;

fileInput.onchange = () => {
	filesArray = Array.from(fileInput.files);
	totalUploadSize = filesArray.reduce((total, file) => total += file.size, 0);
	biggestImgSize = filesArray => Math.max(...filesArray.size);
	longestName = filesArray => Math.max(...filesArray.name.length);

	if ( biggestImgSize > maxFileSizeLimit ) {
		feedback.innerHTML += `<p class="error">One or more images are too big to be uploaded.
			Max image size is ${maxFileSizeLimit} MB. Check list below.</p>`;
	}

	if ( biggestImgSize > largeImgSizeWarningLimit ) {
		feedback.innerHTML += `<p class="warning">Some images rather large. Please be careful, we don't have infinite memory, y'know.</p>`;
	}

	if ( longestName > maxFileNameLengthLimit ) {
		feedback.innerHTML += `<p class="warning">One or more images have too long names.
			Max length is ${maxFileNameLengthLimit} characters.	Names longer than that may be cut.</p>`;
	}
	createHTMLTableOfFiles();
};

/**
 * TODO: stuff
 */
uploadForm.onsubmit = (event) => {
	// Prevent default browser behaviour, in this case submitting a form normally (causing page-load)
	event.preventDefault();
	// Check that an upload isn't already in progress. We don't want to sent the same upload twice.
	if ( uploadInProgress ) { return; }
	if ( !filesArray ) { return; }

	modal.showModal();
	uploadInProgress = true;

	progressBarFiles.max = filesArray.length;
	progressBarBytes.max = totalUploadSize;

	/**
	 * while-true loop for sending files in batches
	 * create new formdata
	 * add to formdata as the loop goes through array.
	 * send formdata when maxBatchSize reached
	 * empty formdata, start loop
	 */
	let formData = new FormData( uploadForm );

	let indx = 0;
	while ( indx in filesArray ) {
		formData.delete('images[]');

		let currentBatchSize = 0;

		while ( indx in filesArray && (currentBatchSize+filesArray[indx].size) < maxBatchSize ) {
			formData.append( 'images[]', filesArray[indx] );
			currentBatchSize += filesArray[indx].size;
			indx++;
		}

		formData.set( 'batchSizeBytes', currentBatchSize.toString() );

		/** Check that we are actually sending something, and not just empty requests. */
		if ( currentBatchSize > 0 ) {
			sendForm( formData )
				.then( handleRequestResponse );
		}
		else {
			console.log('File too big to send, skipping...');
			indx++;
			progressBarFiles.value++;
			progressBarBytes.max += filesArray[indx].size;

			let row = document.createElement( 'tr' );
			row.innerHTML = `<td>❌</td>
				<td>${filesArray[indx].name}</td>
				<td>${printFileSize(filesArray[indx])}</td>
				<td>${filesArray[indx].date}</td>
				<td>❌</td>`;
			finishedUploadsTableBody.appendChild( row );
		}
	}

	// Warning if adding a lot of images, or big collection?

	uploadInProgress = false;
};

modalClose.onclick = () => {
	modal.close();
};
