'use strict';

/* **************************************
	Functions
 * **************************************/
import {Common} from "./modules/common.class.js";

function initMopsiClustering () {
	let options = {
		clusteringMethod: "gridBased",
		serverClient: "client", // client | server
		markerStyle: 'marker1',
		markerColor: "white", // CSS color
		representativeType: "mean", // mean | first | middleCell
		// Single marker height and width
		markerSingleHeight: 39,
		markerSingleWidth: 48,
		// Cluster height and width
		markerClusterHeight: 39,
		markerClusterWidth: 48,
		// [top|center|bottom] - [left|center|right]
		//   [ 0 | 1 | 2 ]     -    [ 0 | 1 | 2 ]
		// e.g. int[] = [ 2 , 0 ]
		thumbPosition: 'top-left'
	};

	let clusteringObj = new mopsiMarkerClustering( map, options, mapDiv );

	// location points with the strict format {id, Lat, Lng}
	clusteringObj.addLocations( points );

	// path of the image/icon that is to be displayed at each data point
	clusteringObj.addSingleMarkerIcons( points );

	clusteringObj.cluster();
}

function initGoogleMap () {
	map = new google.maps.Map( document.getElementById( 'googleMap' ), {
		center: mapCentre,
		zoom: initialZoom,
		minZoom: 3,
		maxZoom: 20,
		styles: [
			{
				featureType: "poi",
				elementType: "labels",
				stylers: [ { visibility: "off" } ]
			}
		]
	} );

	google.maps.event.addListenerOnce( map, 'tilesloaded', function () {
		initMopsiClustering();
	} );

	document.addEventListener( "clustering_done", function ( event ) {
		event.clusteringObj.display();
	} );

	// the following listeners returns these objects.
	// These information can be used for whatever purpose you need it for.

	/**
	 * {Object} event
	 *      .marker - google marker object
	 *      .clusteringObj - clustering object
	 *      .Lat
	 *      .Lng
	 *      .clusterSize
	 *      .id - the ID of the single data point object
	 */
	document.addEventListener( "click_single", function ( event ) {
		// alert( `You clicked a single image, ${points[event.id].name}.` );
		console.log( event );
		console.log( points[event.id] );
		openOverlay( points[event.id] );
	} );
	document.addEventListener( "rightclick_single", function ( event ) {
		alert( "A right click has happened!" );
		console.log( event );
	} );

	document.addEventListener( "click_cluster", function ( event ) {
		alert( "A CLUSTER!" );
		console.log( event );
	} );
	document.addEventListener( "rightclick_cluster", function ( event ) {
		console.log( event );
	} );
}

function openOverlay ( imageMarker ) {
	overlayEditLink.href = `./edit-image.php?id=${imageMarker.ruid}`;
	overlayMapLocationLink.href = `./map.php?cid=${collectionRUID}&iid=${imageMarker.ruid}`;
	overlayImageElement.src = `./img/img.php?id=${imageMarker.ruid}&full`;

	let location = Common.fGPSDecimalToDMS( {lat:imageMarker.Lat,lng:imageMarker.Lng} );
	overlayNameTitle.innerHTML = imageMarker.name + "<br>" + location;

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

let map;
let mapDiv = document.getElementById( "googleMap" );

window.onload = () => {
	initGoogleMap();
}

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

// Overlay close:
overlayClose.onclick = closeOverlay;
