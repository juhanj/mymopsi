<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

$feedback = Common::checkFeedbackAndPOST();

$image = Image::fetchImageByRUID( $db, $_GET[ 'id' ] );
if ( !$image ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>No Image found with given ID.</p>";
	header( "Location:index.php" );
	exit();
}

$collection = Collection::fetchCollectionByID( $db, $image->collection_id );

if ( ($user->id !== $collection->owner_id) and !$collection->public ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>No access to collection.</p>";
	header( "Location:index.php" );
	exit();
}

array_push(
	$breadcrumbs_navigation,
	[ 'User', WEB_PATH . '/collections.php' ],
	[ 'Collection', WEB_PATH . '/collection.php?id=' . $collection->random_uid ],
);
?>

<!DOCTYPE html>
<html lang="fi">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<div class="feedback" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<!-- Image -->
	<section class="box image-container">
		<h2>Thumbnail</h2>
		<img src="./img/img.php?id=<?= $image->random_uid ?>&thumb" class="image thumbnail" alt="<?= $image->name ?> thumbnail"
		     style="">

		<h2>Actual image</h2>
		<img src="./img/img.php?id=<?= $image->random_uid ?>" class="image" alt="<?= $image->name ?>">
	</section>

	<!-- Name -->
	<form class="box" method="post">
		<!-- Input -->
		<label>
			<span class="label"><?= $lang->NAME ?></span>
			 <input type="text" name="name" value="<?= $image->name ?>" required>
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="edit_name">
		<input type="hidden" name="collection" value="<?= $image->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
	</form>

	<!-- Description -->
	<form class="box" method="post">
		<!-- Image -->
		<label>
			<span class="label"><?= $lang->DESCRIPTION ?></span>
			<textarea name="description" cols="30" rows="4" required><?= $image->description ?></textarea>
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="edit_description">
		<input type="hidden" name="collection" value="<?= $image->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
	</form>

	<!-- GPS editing -->
	<section class="box">
		<h2>Location</h2>
		<!-- Map -->
		<section id="googleMap" class="map margins-initial">
			<!-- Google Map goes here. `margins-initial`-class necessary
				to not break Google's own styling -->
		</section>

		<p style="width: 100%; text-align: center;">
			<span id="coordinateText"><?= $image->latitude ?>, <?= $image->longitude ?></span>
		</p>

		<p class="loading" id="loadingIcon" style="margin: 2rem auto auto" hidden></p>

		<!-- To save new coordinate (enabled when coordinate changes) -->
		<button class="button" id="saveLocationButton"
		        data-id="<?= $image->random_uid ?>" disabled>
			Save?
		</button>
	</section>

	<hr>

	<!-- Deleting image -->
	<section class="box warning">
		<p>
			<?= $lang->DANGER_DELETE_INFO ?>
		</p>
		<button class="button red" id="deleteButton"
		        data-image="<?= $image->random_uid ?>">
			<?= $lang->DELETE_BUTTON ?>
		</button>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
	let imageID = '<?= $image->random_uid ?>';
	let locationKnown = <?= var_export( isset( $image->latitude ) ) ?>;
	let markerPosition = {
		lat: <?= $image->latitude ?? 'null' ?>,
		lng: <?= $image->longitude ?? 'null' ?>,
	}
</script>

</body>
</html>
