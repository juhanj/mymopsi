'use strict';

import {Common} from "./modules/export.js";

/* **************************************
	Functions
 * **************************************/

function openOverlay ( listItem ) {
	overlayEditLink.href = `./edit-image.php?id=${listItem.dataset.id}`;
	overlayMapLocationLink.href = `./map.php?cid=${collectionRUID}&iid=${listItem.dataset.id}`;
	overlayImageElement.src = `./img/img.php?id=${listItem.dataset.id}&full`;

	let location = "";
	if ( listItem.dataset.lat ) {
		location = Common.fGPSDecimalToDMS( {lat:listItem.dataset.lat,lng:listItem.dataset.lng} );
	}
	overlayNameTitle.innerHTML = listItem.dataset.name + "<br>" + location;

	overlay.hidden = false;
	overlay.classList.remove( 'hidden' );
}

function closeOverlay () {
	overlay.hidden = true;
	overlay.classList.add( 'hidden' );

	overlayEditLink.href = '';
	overlayNameTitle.innerText = '';
	overlayMapLocationLink.href = '';
	overlayImageElement.src = '';
}

/* **************************************
	Main code
 * **************************************/

// Breadcrumb navigation, link to edit-page:
let headerCollectionNameLink = document.getElementById( 'header-coll-link' );
let headerCollectionNameName = document.getElementById( 'header-coll-name' );
headerCollectionNameName.innerText = collectionName;
headerCollectionNameLink.href = `edit-collection.php?id=${collectionRUID}`;

// Image list elements
let imageList = document.getElementById( 'imageList' );
let listItems = imageList.getElementsByTagName("li");
let imageElements = imageList.querySelectorAll( 'img.img-thumb' );
// Active element is for keyboard events
// Defaulted to first, instead of null for less if-else checks
let activeIndex = -1;
let activeElement = listItems[0];
let activeCSSClassName = 'active-list-item';

// Overlay elements:
let overlay = document.getElementById( 'overlay' );
let overlayEditLink = document.getElementById( 'imageEditLink' );
let overlayNameTitle = document.getElementById( 'imageName' );
let overlayMapLocationLink = document.getElementById( 'imageMapLink' );
let overlayClose = document.getElementById( 'closeOverlay' );
let overlayImageElement = document.getElementById( 'imageFull' );

// If full-sized image fails to load (e.g. file type not supported)
overlayImageElement.onerror = () => {
	overlayImageElement.src='./img/mopsi.ico';
}

// When image is clicked, open fullscreen overlay
imageList.onclick = (event) => {
	if ( event.target && event.target.tagName === 'LI' ) {
		openOverlay( event.target );
	}
}

// Overlay close:
overlayClose.onclick = closeOverlay;

// Keyboard events, for handling overlay
document.addEventListener( 'keyup', (event) => {
	let key = event.key;

	switch ( key ) {
		// More to previous element in list of images
		case 'ArrowLeft':
			activeElement.classList.remove(activeCSSClassName);
			--activeIndex;

			// If hit end of list, loop to other end
			// (in this case, from start to end)
			if ( activeIndex < 0 ) {
				activeIndex = listItems.length - 1;
			}

			activeElement = listItems[activeIndex];
			activeElement.classList.add(activeCSSClassName);

			if ( !overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Move to next element in list of images
		case 'ArrowRight':
			activeElement.classList.remove(activeCSSClassName);
			++activeIndex;

			// If hit end of list, loop to other end
			// (in this case, from end to start)
			if ( activeIndex >= listItems.length ) {
				activeIndex = 0;
			}

			activeElement = listItems[activeIndex];
			activeElement.classList.add(activeCSSClassName);

			if ( !overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Open overlay of active element in list (do nothing if overlay open)
		case ' ': // space
		case 'Enter':
			if ( overlay.hidden ) {
				openOverlay( activeElement );
			}
			break;

		// Close overlay (do nothing if overlay closed)
		case 'Escape':
			closeOverlay();
			activeElement.classList.remove(activeCSSClassName);
			break;
	}
	console.log(event);
} )