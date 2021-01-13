<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !empty($_POST) ) {
	$controller = new ImageController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result['success'] ) {
		$_SESSION['feedback'] = "<p class='success'>{$lang->EDIT_SUCCESS}</p>";
	}
	elseif ( $controller->result['error'] ) {
		$_SESSION['feedback'] = "<p class='error'>{$controller->result['errMsg']}</p>";
	}

	header( "Location: ./edit-image.php?id={$_POST['image']}" );
	exit();
}

//$feedback = Utils::checkFeedbackAndPOST();

$image = Image::fetchImageByRUID( $db, $_GET['id'] );
$collection = Collection::fetchCollectionByID( $db, $image->collection_id );
if ( !$image ) {
	$_SESSION['feedback'] = "<p class='error'>GPS-edit: invalid ID.</p>";
	header( "Location:index.php" );
	exit();
}

array_push(
   $breadcrumbs_navigation,
   [ 'User', WEB_PATH . '/collections.php' ],
   [ 'Collection', WEB_PATH . '/collection.php?id=' . $collection->random_uid ],
   [ 'Image', WEB_PATH . '/edit-image.php?id=' . $image->random_uid ]
);
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid margins-off">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<!-- Contains the form inputs for entering GPS coordinates -->
	<section class="form-section">
		<form method="post" class="coordinate-form margins-off">
			<!-- Latitude coordinate -->
			<label class="margins-off" id="lat-label">
				<span class="label required">Lat:</span>
				<input type="text" value="<?= $image->latitude ?>" id="lat" name="lat" required readonly>
			</label>

			<!-- Longitude coordinate -->
			<label class="margins-off" id="long-label">
				<span class="label required">Lng:</span>
				<input type="text" value="<?= $image->longitude ?>" id="long" name="long" required readonly>
			</label>

			<!-- Hidden stuff for server-side handler -->
			<input type="hidden" name="image" value="<?= $image->random_uid ?>">
			<input type="hidden" name="class" value="image">
			<input type="hidden" name="request" value="edit_gps">

			<!-- Submit button -->
			<input type="submit" id="submit-button" value="<?= $lang->SUBMIT ?>" class="button">
		</form>
	</section>

	<!-- Map section -->
	<section id="googleMap" class="map margins-initial">
		<!-- Google Map goes here. `margins-initial`-class necessary to not break Google's own styling -->
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
	let mapCentre = { lat: 62.25, lng: 26.39 };
	let initialZoom = 5;
	let initialMarker = false;

	<?php if ( isset( $image->latitude ) and isset( $image->longitude ) ) : ?>
		mapCentre.lat = <?= $image->latitude ?>;
		mapCentre.lng = <?= $image->longitude ?>;
		initialZoom = 13;
		initialMarker = true;
	<?php endif; ?>
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>"></script>

</body>
</html>
