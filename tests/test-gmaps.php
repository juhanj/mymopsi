<?php declare(strict_types=1);
require '../components/_start.php';
/**
 * @var DBConnection $db
 */

$collection_id = 1;
$collection = Collection::fetchCollectionByID( $db, $collection_id );
$collection->getImages( $db );
$listImages = [];
$thumbnailPath = 'http://cs.uef.fi/mopsi_dev/mymopsi/tests/temp/gmaps_test_thumbs/thumb/';
foreach ( $collection->images as $image ) {
	if ( !$image->longitude ) continue;
	$listImages[] = [
		'lat' => $image->latitude,
		'lng' => $image->longitude,
		'src' => $thumbnailPath . "thumb-{$image->random_uid}",
	];
}

$length = count( $listImages );
$filteredLength = $length / 10;
$filteredListImages = [];
$listOfRNGs = [];
$rng = 0;

for ( $i = 0; $i < $filteredLength; $i++ ) {
	do {
		$rng = rand( 0, $length-1 );
	} while ( in_array($rng, $listOfRNGs) );

	$filteredListImages[] = $listImages[ $rng ];

	$listOfRNGs[] = $rng;
}
//$listImages = $filteredListImages;
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
<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI[ 'Misc' ][ 'gmaps_api_key' ] ?>"></script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

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

		gMarkers = [];

		// init test markers
		for ( let marker of points ) {
			let icon = {
				url: marker.src, // url
				size: new google.maps.Size( 128, 128 ),
				scaledSize: new google.maps.Size( 50, 50 ), // scaled size
				origin: new google.maps.Point( 0, 0 ), // origin
				anchor: new google.maps.Point( 0, 0 ) // anchor
			};
			gMarkers.push(
				new google.maps.Marker( {
					position: marker,
					map: map,
					// icon: icon
				} )
			);
		}

		// markerCluster = new markerClusterer.MarkerClusterer( {
		// 		map: map,
		// 		markers: gMarkers,
		// 	} );
	}

	let points = JSON.parse( '<?= json_encode( $listImages ) ?>' );
	let validImages = points.length;

	console.log( points );

	let mapCentre = { lat: 62.6, lng: 29.75 };
	let initialZoom = 14;

	let map;
	let gMarkers;
	let markerCluster;
	let mapDiv = document.getElementById( "googleMap" );
	window.onload = () => {
		initGoogleMap();
	}
</script>
</body>
</html>
