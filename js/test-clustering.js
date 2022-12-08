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
		streetViewControl: false,
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
}

/* **************************************
	Main code
 * **************************************/

let map;
let mapDiv = document.getElementById( "googleMap" );

window.onload = () => {
	initGoogleMap();
}