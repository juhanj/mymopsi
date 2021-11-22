<?php declare(strict_types=1);
require './components/_start.php';
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
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid margins-off">

<?php require 'html-header.php'; ?>

<main class="main-body-container margins-off">

	<section class="clustering-container" hidden>
		<label>
			Clustering
			<select>
				<option value="server">Server</option>
				<option value="client">Client</option>
			</select>
		</label>
	</section>

	<section id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</section>

</main>

<?php require 'html-footer.php'; ?>

<!-- Hidden fullscreen overlay code. When image thumbnail is clicked, this is shown -->
<div id="overlay" class="dark-overlay-bg hidden" hidden>
	<div class="overlay-container">
		<section class="overlay-header-container center margins-off">
			<a href="" class="button" id="imageEditLink">
				<?= $lang->EDIT ?>
				<span class="material-icons">edit</span>
			</a>
			<span class="image-name" id="imageName"></span>
			<a href="" class="button" id="imageMapLink">
				<?= $lang->MAP ?>
				<span class="material-icons">place</span>
			</a>
			<button class="button" id="closeOverlay">
				<span class="material-icons">close</span>
			</button>
		</section>

		<section class="overlay-image-container" id="overlayImageContainer">
			<img src="" class="image-full" id="imageFull" alt="">
		</section>
	</div>
</div>

<script>
	let collectionRUID = "<?= $collection->random_uid ?>";
	let collectionSize = <?= count( $collection->images ) ?>;
	let points = [
		<?php foreach ( $collection->images as $i => $img ) : ?>
		<?php if ( !$img->latitude ) {
		continue;
	} ?>
		{
			id: <?= $i ?>,
			ruid: '<?= $img->random_uid ?>',
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


<!-- Google maps API (init is done in map.js, hence no callback) -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>">
</script>

<!-- jQuery is used by clustering API -->
<script
	src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
	integrity="sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8="
	crossorigin="anonymous"></script>

<!-- Clustering API files -->
<script src="./clusteringAPI/clusteringInterface.js" type="text/javascript"></script>
<script src="./clusteringAPI/clusteringLogic.js" type="text/javascript"></script>
<script src="./clusteringAPI/mapFunctions.js" type="text/javascript"></script>
<script src="./clusteringAPI/markerFunctions.js" type="text/javascript"></script>

</body>
</html>
