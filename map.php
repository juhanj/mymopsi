<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */

$coll = Collection::fetchCollection( $db, $_GET['cid'] );

if ( !$coll ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NO_COLLECTION_FOUND}</p>";
	header('location: index.php' );
	exit;
}

$coll->getCollectionImgs($db);
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<div id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</div>

</main>

<?php require 'html-footer.php'; ?>

<script>
	let collectionSize = <?= count($coll->imgs) ?>;
	let points = [
	<?php foreach ( $coll->imgs as $i => $img ) : ?>
		<?php if ( !$img->latitude ) { continue; } ?>
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
</script>

<script defer
        src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>&callback=initGoogleMap">
</script>

<script defer src="./clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/mapFunctions.js" type="text/javascript"></script>
<script defer src="./clusteringAPI/markerFunctions.js" type="text/javascript"></script>

</body>
</html>
