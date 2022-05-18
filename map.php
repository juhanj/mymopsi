<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

function allCollections () {}
function allImages () {}
function userImages () {}
function userCollections () {}

function collectionImages ( $db, $coll_ruid ) {
	$collection = Collection::fetchCollectionByRUID( $db, $coll_ruid );
	$collection->getImages( $db );

	return $collection;
}

// Get mode - 'coll' or 'all' or 'user' ('all' only for admins, 'user' only for logged-in/admins)
$mode = $_GET['mode'] ?? 'coll_img';
$coll_ruid = $_GET['cid'] ?? null;
$image_focus = $_GET['iid'] ?? null;

switch ( $mode ) {
	case 'all_coll' :
		// Get all collections, like literally all from all users
		//TODO: all collections and represanttavive
		break;
	case 'all_img' :
		// Get all images, like literally all from all users
		//TODO: All images
		break;
	case 'user_coll':
		// Get all collections from one user
		//TODO: get collections with representative img
		break;
	case 'user_img':
		// Get all images from one user
		//TODO: get all images from user, from all collections
		break;
	case 'coll_img':
	default:
		// Get images from one collection
		$collection = collectionImages( $db, $coll_ruid );
		break;
}

if ( isset( $image_focus ) ) {
	foreach ( $collection->images as $img ) {
		if ( $img->random_uid === $image_focus ) {
			$image_focus = $img;
			break;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid margins-off">

<main class="main-body-container margins-off">

	<section class="sidebar">
		<?php require 'html-back-button.php'; ?>
		<ol class="sidebar-list">
			<?php foreach ( $collection->images as $img ) : ?>
			<li class="sidebar-list-item margins-off">
				<img src="./img/img.php?id=<?= $img->random_uid ?>&thumb"
				     class="list-img-thumb"
				     alt="<?= $img->name ?>"
				     onerror="this.onerror=null;this.src='./img/mopsi.ico';">
				<!-- Inline onerror because otherwise it won't trigger
						(image loads before listener is registered) -->
				<p class="list-item-name"><?= $img->name ?></p>
				<p class="list-item-info"><?= ($img->latitude) ? '' : '⚠' ?></p>
			</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<section id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</section>

</main>

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
			src: './img/img.php?id=<?= $img->random_uid ?>',
			name: '<?= $img->name ?>',
		},
		<?php endforeach; ?>
	];
	let validImages = points.length;

	let mapCentre = { lat: 62.25, lng: 26.39 };
	let initialZoom = 5;

	<?php if ( isset( $image_focus ) ) : ?>
	mapCentre.lat = <?= $image_focus->latitude ?>;
	mapCentre.lng = <?= $image_focus->longitude ?>;
	initialZoom = 17;
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
