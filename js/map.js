'use strict';
let map;
let mapDiv = document.getElementById("googleMap");

function initMopsiClustering() {
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
		// [top|center|bottom] - [right|center|left]
		thumbPosition: 'top-right'
	};

	let clusteringObj = new mopsiMarkerClustering(map, options, mapDiv);

	// location points with the strict format {id, Lat, Lng}
	clusteringObj.addLocations( points );

	// path of the image/icon that is to be displayed at each data point
	clusteringObj.addSingleMarkerIcons( points );

	clusteringObj.cluster();
}

function initGoogleMap() {
	map = new google.maps.Map(document.getElementById('googleMap'), {
		zoom: 15,
		center: {lat: 62.60393, lng: 29.74413},
		minZoom: 7,
		maxZoom: 50,
	});

	google.maps.event.addListenerOnce(map, 'tilesloaded', function () {
		initMopsiClustering();
	});


	document.addEventListener("clustering_done", function (event) {
		event.clusteringObj.display();
	});

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
	document.addEventListener("click_single", function (event) {});
	document.addEventListener("rightclick_single", function (event) {});

	document.addEventListener("click_cluster", function (event) {});
	document.addEventListener("rightclick_cluster", function (event) {});
}
