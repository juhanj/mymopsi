'use strict';

import {Common, Ajax, SEC} from "./modules/export.js";

/* **************************************
	Functions
 * **************************************/

function openOverlay ( listItem ) {
	overlayEditLink.href = `./edit-image.php?id=${listItem.dataset.id}`;
	overlayMapLocationLink.href = `./map.php?cid=${collectionRUID}&iid=${listItem.dataset.id}`;
	overlayImageElement.src = `./img/img.php?id=${listItem.dataset.id}&full`;

	let location = "";
	if ( listItem.dataset.lat ) {
		location = Common.fGPSDecimalToDMS( { lat: listItem.dataset.lat, lng: listItem.dataset.lng } );
	}

	// Overlay title parts
	overlayTitleName.innerText = listItem.dataset.name;
	let modifiedLoadingIcon = `<span class='loading' 
		style='display: inline-block; margin-left: .5rem; width: 0; height: 0;'></span>`;
	overlayTitleLocation.innerHTML = location + modifiedLoadingIcon;

	// Overlay image info (below image)
	overlayImageDescription.innerText = listItem.dataset.description || '--';
	overlayImageLocation.innerText = location || '--';
	overlayImageAddress.innerText = '--';
	if ( !listItem.dataset.created || listItem.dataset.created === '0000-00-00 00:00:00' ) {
		overlayImageDateCreated.innerText = '--';
	} else {
		overlayImageDateCreated.innerText = listItem.dataset.created;
	}
	overlayImageDateAdded.innerText = listItem.dataset.added || '--';


	// After everything loaded, show overlay
	//TODO: a loading icon before? --jj 22-07-28
	overlay.hidden = false;
	overlay.classList.remove( 'hidden' );

	let currentIndex = listItem.dataset

	// Set a timeout for when user is just quickly scrolling through the images
	//  so that it doesn't slow the UI loading in the background.
	setTimeout( ( imageElement ) => {
		if ( Number( imageElement.dataset.index ) !== activeIndex ) {
			return;
		}
		let request = {
			'class': 'image',
			'request': 'image_reverse_geocoding_get_address',
			'image': imageElement.dataset.id
		};
		Ajax.sendJSON( request )
			.then( ( response ) => {
				console.log( response );
				let country = response.result.address.country;
				let city = response.result.address.city ?? response.result.address.address_array.town;
				let street = response.result.address.street
					?? response.result.address.address_array.pedestrian
					?? response.result.address.address_array.neighbourhood
					?? response.result.address.address_array.cycleway
					?? response.result.address.address_array.suburb
				;
				overlayTitleLocation.innerText = `${country}, ${city}, ${street}`;
				overlayImageAddress.innerText = `${country}, ${city}, ${street}`;

			} );
	}, 2 * SEC, listItem );
}

function closeOverlay () {
	overlay.hidden = true;
	overlay.classList.add( 'hidden' );

	overlayEditLink.href = '';
	overlayTitleName.innerText = '';
	overlayTitleLocation.innerText = '';
	overlayMapLocationLink.href = '';
	overlayImageElement.src = '';
}

function keyboardHandling ( event ) {
	let key = event.key;

	switch ( key ) {
		// More to previous element in list of images
		case 'ArrowLeft':
			activeElement.classList.remove( activeCSSClassName );
			--activeIndex;

			// If hit end of list, loop to other end
			// (in this case, from start to end)
			if ( activeIndex < 0 ) {
				activeIndex = listItems.length - 1;
			}


			activeElement = listItems[activeIndex];
			activeElement.classList.add( activeCSSClassName );

			if ( !overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Move to next element in list of images
		case 'ArrowRight':
			activeElement.classList.remove( activeCSSClassName );
			++activeIndex;

			// If hit end of list, loop to other end
			// (in this case, from end to start)
			if ( activeIndex >= listItems.length ) {
				activeIndex = 0;
			}

			activeElement = listItems[activeIndex];
			activeElement.classList.add( activeCSSClassName );

			if ( !overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Open overlay of active element in list (do nothing if overlay open)
		case ' ': // space
			// Space used to open overlay, but it has default browser
			// functionality, and as such I don't want to overwrite it,
			// because I have no good reason to do so.
			break;

		case 'Enter':
			// Prevent the pagination form input action overlapping
			if ( document.activeElement.id === 'pageSelectInput' ) {
				//This is a hack because I couldn't be bothered to fix the issue
				paginationForm.submit();
			} else if ( overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Close overlay (do nothing if overlay closed)
		case 'Escape':
			closeOverlay();
			activeElement.classList.remove( activeCSSClassName );
			break;
	}
}

/* **************************************
	Main code
 * **************************************/

// Breadcrumb navigation, link to edit-page:
let headerCollectionNameLink = document.getElementById( 'header-coll-link' );
let headerCollectionNameName = document.getElementById( 'header-coll-name' );
headerCollectionNameName.innerText = collectionName;

if ( owner ) {
	headerCollectionNameLink.href = `edit-collection.php?id=${collectionRUID}`;
}

// Pagination elements
let paginationForm = document.getElementById( 'paginationForm' );

// Image list elements
let imageList = document.getElementById( 'imageList' );
let listItems = imageList.getElementsByTagName( "li" );
let imageElements = imageList.querySelectorAll( 'img.img-thumb' );
// Active element is for keyboard events
// Defaulted to first, instead of null for less if-else checks
let activeIndex = -1;
let activeElement = listItems[0];
let activeCSSClassName = 'active-list-item';

// Overlay elements:
let overlay = document.getElementById( 'overlay' );
let overlayEditLink = document.getElementById( 'imageEditLink' );
let overlayTitleName = document.getElementById( 'imageName' );
let overlayTitleLocation = document.getElementById( 'imageLocationAddress' );
let overlayMapLocationLink = document.getElementById( 'imageMapLink' );
let overlayClose = document.getElementById( 'closeOverlay' );
let overlayImageElement = document.getElementById( 'imageFull' );
// Overlay DL list Image info elements
let overlayImageDescription = document.getElementById( 'overlayDlImgDescription' );
let overlayImageLocation = document.getElementById( 'overlayDlImgLocation' );
let overlayImageAddress = document.getElementById( 'overlayDlImgAddress' );
let overlayImageDateCreated = document.getElementById( 'overlayDlImgDateAdded' );
let overlayImageDateAdded = document.getElementById( 'overlayDlImgDateCreated' );

// If full-sized image fails to load (e.g. file type not supported)
overlayImageElement.onerror = () => {
	overlayImageElement.src = './img/mopsi.ico';
}

// When image is clicked, open fullscreen overlay
imageList.onclick = ( event ) => {
	if ( event.target && event.target.tagName === 'LI' ) {
		activeIndex = Number( event.target.dataset.index );
		activeElement = listItems[activeIndex];
		activeElement.classList.add( activeCSSClassName );

		openOverlay( event.target );
	}
}

// Overlay close:
overlayClose.onclick = closeOverlay;

// Keyboard events, for handling overlay
document.addEventListener( 'keyup', keyboardHandling )