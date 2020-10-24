<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

// TODO: fix this page of a mess
// submit button label
// confirmation on complete
// feedback on button
// coordinate formatting 12.34N 32.10E

// Valid user logged in
if ( !$user ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NOT_LOGGED_IN}</p>";
	header( 'location: index.php' );
	exit();
}
// Valid collection RUID provided via GET
if ( empty( $_GET['id'] ) ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->ID_MISSING}</p>";
	header( 'location: ' . $_SERVER['HTTP_REFERER'] );
	exit();
}

$feedback = Common::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] );

// Valid collection found with given RUID
if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->COLLECTION_INVALID}</p>";
	header( 'location: ' . $_SERVER['HTTP_REFERER'] );
	exit();
}
// Check either collection owner, public&editable collection (not implemented), or admin user
if ( (!$collection->public and !$collection->editable) and ($collection->owner_id !== $user->id) ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NOT_COLL_OWNER}</p>";
	header( 'location: ' . $_SERVER['HTTP_REFERER'] );
	exit();
}

// Breadcrumbs navigation for the header
array_push(
   $breadcrumbs_navigation,
   ['User', WEB_PATH . '/collections.php' ],
   ['Collection', WEB_PATH . '/collection.php?id=' . $collection->random_uid ]
);
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
	<div class="feedback" id="feedback"><?= $feedback ?></div>

	<!-- Link back to collection we're adding images to -->
	<a href="collection.php?id=<?= $collection->random_uid ?>" class="button return">
		<i class="material-icons">arrow_back</i>
		<?= $lang->BACK_TO_COLL ?>
	</a>

	<!-- Selected files by the user will be shown here before uploading -->
	<div class="box" id="files-info" hidden>
		<h2><?= $lang->SELECTED_FILES_HEADER ?></h2>
	</div>

	<!-- The form itself.
	    Contains <input type=file> -tag, and two hidden input-tags (request and collection-ID)
	    The submit is handled by javascript, since we send files in batches (images can get very large).
	-->
	<section class="box" id="upload-form-box">
		<!-- Form -->
		<form method="post" enctype='multipart/form-data' id="upload-form">
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>
			<!-- File type input -->
			<label id="fileinput-label">
				<span class="label"><?= $lang->FILE_INPUT ?></span>:
				<input type="file" name="images[]" accept="image/*" id="file-input" multiple="multiple">
				<!-- The input also has some english text which cannot be styled or changed. -->
				<span class="label-info"><?= $lang->UPLOAD_LABEL_INFO ?></span>
			</label>

			<!-- Server processing stuff -->
			<input type="hidden" name="class" value="image">
			<input type="hidden" name="request" value="upload">
			<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">

			<button type="submit" class="button" id="submit-button" hidden>
				<?= $lang->SUBMIT ?>
				<?= file_get_contents('./img/upload.svg') ?>
			</button>
		</form>

		<!-- Progress bars, one for files, one for bits -->
		<section class="progress-bar-container" id="progress-bar-container" hidden>
			<!-- Progress number of files -->
			<label>
				<span class="label"><?= $lang->PROGRESS_FILES ?>></span>
				<progress id="progress-files" value="0" ></progress>
			</label>
			<!-- Progress of bits, filesize -->
			<label>
				<span class="label"><?= $lang->PROGRESS_BITS ?>></span>
				<progress id="progress-bits" value="0"></progress>
			</label>
		</section>
	</section>

	<!-- Successful uploads will be listed here after submitting -->
	<section class="box" id="successful-uploads" hidden>
		<h2><?= $lang->SUCCESS_UPLOAD_HEADER ?></h2>
	</section>

	<!-- Failed uploads will be listed here after submitting -->
	<section class="box" id="failed-uploads" hidden>
		<h2><?= $lang->FAILED_UPLOAD_HEADER ?></h2>
	</section>

	<hr>

	<section class="box">
		<p>
			Upload Mopsi photos:
		</p>
		<a href="upload-csv.php?id=<?= $collection->random_uid ?>">Upload a CSV file with photo IDs</a>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
