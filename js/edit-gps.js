'use strict';

/********************************************
 * Functions
 ********************************************/

function getClickCoordinate ( event ) {
	let latitude = event.latLng.lat();
	let longitude = event.latLng.lng();
	console.log( latitude + ', ' + longitude );

	// Center of map
	map.panTo( event.latLng );
	latInput.value = latitude;
	longInput.value = longitude;
	marker.setPosition( event.latLng )
}

function initGoogleMap () {
	map = new google.maps.Map( mapDiv, {
		center: mapCentre,
		zoom: initialZoom,
		minZoom: 3,
		maxZoom: 20,
	} );

	marker = new google.maps.Marker({
		map: map,
		title: 'GSP coordinates'
	});

	if ( initialMarker ) {
		marker.setPosition( mapCentre )
	}

	google.maps.event.addListener( map, "click", getClickCoordinate );
}

/********************************************
 * Main code
 ********************************************/

let latInput = document.getElementById( 'lat' );
let longInput = document.getElementById( 'long' );

let mapDiv = document.getElementById( "googleMap" );
let map;
let marker;

initGoogleMap();