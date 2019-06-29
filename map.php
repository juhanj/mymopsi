<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

$coll = new Collection( $db, $_GET['id'] );
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<style>
	#googleMap {
		height: 50rem;
		width: 100%;
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<div id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</div>

</main>

<?php require 'html-footer.php'; ?>

<script defer
        src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>&callback=initGoogleMap">
</script>

<script defer src="./clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/mapFunctions.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/markerFunctions.js" type="text/javascript"></script>

<script>
	let collectionSize = <?= $coll->number_of_images ?>;
	let points = [
	<?php foreach ( $coll->imgs as $i => $img ) : ?>
		{
			id: <?= $i ?>,
			Lat: <?= $img->latitude ?>,
			Lng: <?= $img->longitude ?>,
			src: './img/img.php?cid=<?= $coll->uid ?>&iid=<?= $img->uid ?>',
			name: <?= $img->name ?>,
		},
	<?php endforeach; ?>
	];
</script>


</body>
</html>
