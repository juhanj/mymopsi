<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */
?>
<!DOCTYPE html>
<html lang="fi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>MAP TEST</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/modern-normalize.css">
	<link rel="stylesheet" href="<?= WEB_PATH ?>/css/main.css">
	<script defer src="<?= WEB_PATH ?>/js/main.js"></script>

	<script src="https://code.jquery.com/jquery-3.4.0.slim.min.js"
			integrity="sha256-ZaXnYkHGqIhqTbJ6MB4l9Frs/r7U4jlx7ir8PJYBqbI="
			crossorigin="anonymous"></script>

	<script defer
	        src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key']?>&callback=initGoogleMap">
	</script>

	<script defer src="../clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
	<script defer src="../clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
	<script defer src="../clusteringAPI/mapFunctions.js" type="text/javascript"></script>
	<script defer src="../clusteringAPI/markerFunctions.js" type="text/javascript"></script>

</head>

<style>
	#googleMap {
		height: 50rem;
		width: 100%;
	}
	.dot {
		height: 25px;
		width: 25px;
		background-color: #FFFF00;
		align: center;
		border-radius: 50%;
		display: inline-block;
	}
</style>

<body class="grid">

<main class="main-body-container">

	<div id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</div>

</main>

<script>
	'use strict';
	let map;
	let mapDiv = document.getElementById("googleMap");
	let center = {lat: 62.60393, lng: 29.74413};
	let testPoints = [
		{
			id: 1,
			Lat: '62.606',
			Lng: '29.749',
			src: './img/map-test/test1.png',
			name: 'Test',
		},
		{
			id: 2,
			Lat: '62.604',
			Lng: '29.750',
			src: './img/map-test/test2.png',
			name: 'Test',
		},
		{
			id: 3,
			Lat: '62.602',
			Lng: '29.73',
			src: './img/map-test/test3.png',
			name: 'Test',
		}
	];
	let clusteringObj;

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

		clusteringObj = new mopsiMarkerClustering(map, options, mapDiv);

		// location points with the strict format {id, Lat, Lng}
		clusteringObj.addLocations(testPoints);

		// path of the image/icon that is to be displayed at each data point
		clusteringObj.addSingleMarkerIcons(testPoints);

		clusteringObj.cluster();
	}

	function initGoogleMap() {
		map = new google.maps.Map(document.getElementById('googleMap'), {
			zoom: 15,
			center: center,
			minZoom: 7,
			maxZoom: 19,
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
</script>


</body>
</html>
