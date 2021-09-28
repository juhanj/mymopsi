"use strict";

import {
	Ajax,
	GMAP_MINZOOM, GMAP_MAXZOOM,
	GMAP_FINLAND, GMAP_INITZOOM
} from './modules/export.js';

/********************************************
 * Functions
 ********************************************/

function handleMapClickMarker ( event ) {
	markerPosition.lat =
	marker.setPosition( event.latLng )
	map.panTo( event.latLng );

	coordinateText.innerText = event.latLng.lat().toFixed(4) + ', '
		+ event.latLng.lng().toFixed(4);

	markerPosition = {
		lat: event.latLng.lat(),
		lng: event.latLng.lng(),
	}

	if ( saveButton.disabled ) {
		saveButton.disabled = false;
	}
}

function saveLocation () {
	saveButton.hidden = true;
	loadingIcon.hidden = false;

	let request = {
		'class' : 'image',
		'request' : 'edit_gps',
		'image' : imageID,
		'lat' : markerPosition.lat,
		'lng' : markerPosition.lng,
	};

	Ajax.sendJSON( request )
		.then( (response) => {
			saveButton.hidden = false;
			saveButton.disabled = true;
			loadingIcon.hidden = true;
			coordinateText.innerText += " ✔ Saved";
		} );
}

function initGoogleMap () {
	map = new google.maps.Map( mapDiv, {
		center: GMAP_FINLAND,
		zoom: GMAP_INITZOOM,
		minZoom: GMAP_MINZOOM,
		maxZoom: GMAP_MAXZOOM,
	} );

	marker = new google.maps.Marker( {
		map: map,
		title: 'GSP coordinate'
	} );

	if ( locationKnown ) {
		// Center and zoom closer
		map.setCenter( markerPosition );
		map.setZoom( map.getZoom() + 3 );
		// Create marker
		marker.setPosition( markerPosition );
	}

	google.maps.event.addListener( map, "click", handleMapClickMarker );
}

function deleteImage () {
	let imageID = deleteButton.dataset.image;
	if ( prompt( "Type 'delete' to cofirm", '' ) === 'delete' ) {
		console.log( "Deleting image..." );
		let request = {
			'class': 'image',
			'request': 'delete_image',
			'image': imageID,
		};
		Ajax.sendJSON( request )
			.then( ( response ) => {
				// Send back to previous page
				// This could be done better? But I dunno how for now.
				history.back();
			} )
	}
}

/********************************************
 * Main code
 ********************************************/

let deleteButton = document.getElementById( 'deleteButton' );
deleteButton.onclick = deleteImage;

let mapDiv = document.getElementById( "googleMap" );
let map;
let marker;
let coordinateText = document.getElementById( 'coordinateText' );
let saveButton = document.getElementById( 'saveLocationButton' );
let loadingIcon = document.getElementById( 'loadingIcon' );

initGoogleMap();
saveButton.onclick = saveLocation;