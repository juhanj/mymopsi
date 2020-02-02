<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$collection = Collection::fetchCollectionByRUID( $db, $_GET['cid'] );

if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NO_COLLECTION_FOUND}</p>";
	header( 'location: index.php' );
	exit;
}

$collection->getImages( $db );

if ( !empty( $_GET['iid'] ) ) {
	foreach ( $collection->images as $img ) {
		if ( $img->random_uid === $_GET['iid'] ) {
			$focus = [ (float)$img->latitude, (float)$img->longitude ];
		}
	}
}
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid margins-off">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<div id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</div>

</main>

<?php require 'html-footer.php'; ?>

<script>
	let collectionSize = <?= count( $collection->images ) ?>;
	let points = [
		<?php foreach ( $collection->images as $i => $img ) : ?>
		<?php if ( !$img->latitude ) {
		continue;
	} ?>
		{
			id: <?= $i ?>,
			Lat: '<?= $img->latitude ?>',
			Lng: '<?= $img->longitude ?>',
			src: './img/img.php?id=<?= $img->random_uid ?>',
			name: '<?= $img->name ?>',
		},
		<?php endforeach; ?>
	];
	let validImages = points.length;

	let mapCentre = { lat: 62.25, lng: 26.39 };
	let initialZoom = 5;

	<?php if ( !empty( $_GET['iid'] ) ) : ?>
		mapCentre.lat = <?= $focus[0] ?>;
		mapCentre.lng = <?= $focus[1] ?>;
		initialZoom = 13;
	<?php endif; ?>
</script>

<script
	src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
	integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
	crossorigin="anonymous"></script>

<script defer
        src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>&callback=initGoogleMap">
</script>

<script defer src="./clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/mapFunctions.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/markerFunctions.js" type="text/javascript"></script>

</body>
</html>
