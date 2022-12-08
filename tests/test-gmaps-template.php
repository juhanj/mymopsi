<?php declare(strict_types=1);
require '../components/_start.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	.main-body-container,
	.map {
		max-width: 100%;
		height: 100%;
	}
</style>

<body class="grid">

<main class="main-body-container margins-off">

	<section id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</section>

</main>

<script>
</script>

<!-- Google maps API (init is done in map.js, hence no callback) -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>">
</script>

<script>
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

		// let image = "URL";
		// let icon = {
		// 	url: image, // url
		// 	size: // size in pixels
		// 	scaledSize: new google.maps.Size(50, 50), // scaled size
		// 	origin: new google.maps.Point(0,0), // origin
		// 	anchor: new google.maps.Point(0, 0) // anchor
		// };
		let marker = new google.maps.Marker( {
			position: { lat: 62.6, lng: 29.756 },
			map: map,
			// icon: icon
		} );
	}

	let mapCentre = { lat: 62.6, lng: 29.75 };
	let initialZoom = 14;

	let map;
	let mapDiv = document.getElementById( "googleMap" );
	window.onload = () => {
		initGoogleMap();
	}
</script>
</body>
</html>
