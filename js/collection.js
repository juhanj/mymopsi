'use strict';

/* **************************************
	Functions
 * **************************************/

function openOverlay ( listItem ) {
	// let image = collectionImages.listIn
	overlay.hidden = false;
	overlay.classList.remove( 'hidden' );

	imageEditLink.href = `./edit-image.php?id=${listItem.dataset.id}`;
	imageNameTitle.innerText = listItem.dataset.name;
	imageMapLocationLink.href = `./map.php?cid=${collectionRUID}&iid=${listItem.dataset.id}`;

	imageElement.src = `./img/img.php?id=${listItem.dataset.id}&full`;
}

/* **************************************
	Main code
 * **************************************/

// Breadcrumb navigation, link to edit-page:
let headerCollectionNameLink = document.getElementById( 'header-coll-link' );
let headerCollectionNameName = document.getElementById( 'header-coll-name' );
headerCollectionNameName.innerText = collectionName;
headerCollectionNameLink.href = `edit-collection.php?id=${collectionRUID}`;

// Overlay code:
let imageList = document.getElementById( 'imageList' );
let overlay = document.getElementById( 'overlay' );
let imageEditLink = document.getElementById( 'imageEditLink' );
let imageNameTitle = document.getElementById( 'imageName' );
let imageMapLocationLink = document.getElementById( 'imageMapLink' );
let overlayClose = document.getElementById( 'closeOverlay' );
let imageElement = document.getElementById( 'imageFull' );

imageList.onclick = (event) => {
	let element = event.target;

	if ( element && element.tagName === 'IMG' ) {
		openOverlay( element );
	}
}

overlayClose.onclick = () => {
	overlay.hidden = true;
	overlay.classList.add( 'hidden' );

	imageEditLink.href = '';
	imageNameTitle.innerText = '';
	imageMapLocationLink.href = '';
	imageElement.src = '';
}