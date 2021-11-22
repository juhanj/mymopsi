<?php declare(strict_types=1);
require './components/_start.php';
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
	[ $user->username, WEB_PATH . 'collections.php' ],
	[ $collection->name ?? "Collection", WEB_PATH . 'collection.php?id=' . $collection->random_uid ]
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
	<section class="image-container">
		<img src="./img/img.php?id=<?= $image->random_uid ?>" class="image"
		     alt="<?= $image->name ?>">
	</section>

	<!-- Name -->
	<form class="box" method="post">
		<!-- Input -->
		<label>
			<span class="label"><?= $lang->NAME ?></span>
			 <input type="text" name="name" value="<?= $image->name ?>" required
			        id="nameInput">
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="edit_name">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">
		<input type="hidden" name="image" value="<?= $image->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>"
		       id="nameSubmit">
	</form>

	<!-- Description -->
	<form class="box" method="post">
		<!-- Image -->
		<label>
			<span class="label"><?= $lang->DESCRIPTION ?></span>
			<textarea name="description" cols="30" rows="4" required
			          id="descriptionInput"><?= $image->description ?></textarea>
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="edit_description">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">
		<input type="hidden" name="image" value="<?= $image->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>"
		       id="descriptionSubmit">
	</form>

	<!-- GPS editing -->
	<section class="box">
		<span><?= $lang->LOCATION_TITLE ?></span>
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
			💾
		</button>
	</section>

	<!-- Image file metadata -->
	<section class="box">
		<style>
			#metadataTextArea {
				font-family: 'Cascadia Code', SFMono-Regular, Consolas, 'Liberation Mono', Menlo, Courier, monospace;
				width: 100%;
				resize: vertical;
				height: 25rem;
			}
		</style>

		<label>
			<span><?= $lang->METADATA_LABEL ?></span>
			<textarea id="metadataTextArea" hidden><?= $image->getFileMetadata($lang->lang) ?></textarea>
		</label>
		<button id="buttonShowMetadata" class="button"><?= $lang->SHOW_METADATA ?></button>
		<script>
			document.getElementById("buttonShowMetadata").onclick = () => {
				document.getElementById("metadataTextArea").hidden = false;
				document.getElementById("buttonShowMetadata").hidden = true;
			}
		</script>
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

<script src="https://maps.googleapis.com/maps/api/js?key=<?= INI['Misc']['gmaps_api_key'] ?>"></script>
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
