'use strict';

/* **************************************
	Functions
 * **************************************/

function openOverlay ( listItem ) {
	// let image = collectionImages.listIn
	overlay.hidden = false;
	overlay.classList.remove( 'hidden' );

	overlayImageEditLink.href = `./edit-image.php?id=${listItem.dataset.id}`;
	overlayImageNameTitle.innerText = listItem.dataset.name;
	overlayImageMapLocationLink.href = `./map.php?cid=${collectionRUID}&iid=${listItem.dataset.id}`;

	overlayImageElement.src = `./img/img.php?id=${listItem.dataset.id}&full`;
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
let imageElements = imageList.querySelectorAll( 'img.img-thumb' );

// Overlay elements:
let overlay = document.getElementById( 'overlay' );
let overlayImageEditLink = document.getElementById( 'imageEditLink' );
let overlayImageNameTitle = document.getElementById( 'imageName' );
let overlayImageMapLocationLink = document.getElementById( 'imageMapLink' );
let overlayClose = document.getElementById( 'closeOverlay' );
let overlayImageElement = document.getElementById( 'imageFull' );

overlayImageElement.onerror = () => {
	overlayImageElement.src='./img/mopsi.ico';
}

// When image is clicked, open fullscreen overlay
imageList.onclick = (event) => {
	if ( event.target && event.target.tagName === 'IMG' ) {
		openOverlay( event.target );
	}
	else if ( event.target && event.target.tagName === 'LI' ) {
		openOverlay( event.target.children[0] );
	}
}
// Overlay close:
overlayClose.onclick = () => {
	//TODO: separate into own function so can use for ESC keyup as well
	overlay.hidden = true;
	overlay.classList.add( 'hidden' );

	overlayImageEditLink.href = '';
	overlayImageNameTitle.innerText = '';
	overlayImageMapLocationLink.href = '';
	overlayImageElement.src = '';
}

//TODO: Add escape keyup eventlistener, for closing overlay (if open)
// Check for overlay.hidden. No need for separate variable.