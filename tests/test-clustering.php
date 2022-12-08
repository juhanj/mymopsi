<?php declare(strict_types=1);
require '../components/_start.php';
/**
 * @var DBConnection $db
 */

$collection_id = 1;
$collection = Collection::fetchCollectionByID( $db, $collection_id );
$collection->getImages( $db );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
</style>

<body class="grid">

<main class="main-body-container margins-off">

	<section id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</section>

</main>

<script>
	let points = [
		<?php
		$index_counter = 0;
		foreach ( $collection->images as $img ) :
		if ( !$img->latitude ) :
			continue;
		endif;
		?>
		{
			id: <?= $index_counter++ ?>,
			ruid: '<?= $img->random_uid ?>',
			Lat: '<?= $img->latitude ?>',
			Lng: '<?= $img->longitude ?>',
			src: '../img/img.php?id=<?= $img->random_uid ?>',
			name: '<?= $img->name ?>',
		},
		<?php endforeach; ?>
	];
	let validImages = points.length;

	let mapCentre = { lat: 62.6, lng: 29.75 };
	let initialZoom = 14;
</script>


<!-- Google maps API (init is done in map.js, hence no callback) -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>">
</script>

<!-- jQuery is used by clustering API -->
<script
	src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
	integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
	crossorigin="anonymous"></script>

<!-- Clustering API files -->
<script src="../clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
<script src="../clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
<script src="../clusteringAPI/mapFunctions.js" type="text/javascript"></script>
<script src="../clusteringAPI/markerFunctions.js" type="text/javascript"></script>

</body>
</html>
