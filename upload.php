<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

// Valid user logged in
if ( !$user ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->NOT_LOGGED_IN}</p>";
	header( 'location: index.php' );
	exit();
}
// Valid collection RUID provided via GET
if ( empty( $_GET[ 'id' ] ) ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->ID_MISSING}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}

$feedback = Common::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET[ 'id' ] );

// Valid collection found with given RUID
if ( !$collection ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->COLLECTION_INVALID}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}
// Check collection owner
if ( $collection->owner_id !== $user->id ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->NOT_COLL_OWNER}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}

// Breadcrumbs navigation for the header
array_push(
	$breadcrumbs_navigation,
	[ $user->username, WEB_PATH . 'collections.php' ],
	[ $collection->name ?? substr( $collection->random_uid, 0, 4 ), WEB_PATH . 'collection.php?id=' . $collection->random_uid ]
);
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<?php require 'html-back-button.php'; ?>

<main class="main-body-container">

	<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
	<div class="feedback" id="feedback"><?= $feedback ?></div>

	<!-- The form itself.
	    Contains <input type=file> -tag, and two hidden input-tags (request and collection-ID)
	    The submit is handled by javascript, since we send files in batches (images can get very large).
	-->
	<form method="post" enctype='multipart/form-data' id="upload-form" class="box">

		<!-- File type input -->
		<label id="fileinput-label" hidden>
			<span class="label center" id="file-input-label-text">
				<i class="material-icons">save_alt</i>
				<?= $lang->FILE_INPUT ?>
			</span>
			<input type="file" name="images[]" accept="image/*" id="file-input" multiple="multiple">
		</label>

		<!-- Server processing stuff -->
		<input type="hidden" name="MAX_FILE_SIZE" value="10048576">
		<input type="hidden" name="class" value="image">
		<input type="hidden" name="request" value="upload">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">

		<!-- Submit (hidden until files chosen) -->
		<button type="submit" class="button" id="submit-button" hidden>
			<?= $lang->SUBMIT ?>
			<span class="material-icons">publish</span>
		</button>
	</form>

	<!-- (More prominent) link to back to collection after upload finishes -->
	<section class="box upload-finished margins-off" id="uploadFinishedBox" hidden>
		<!-- Checkmark and number of files successul uploaded -->
		<div class="center big-green-checkmark">
			<span class="material-icons">check</span>
			<p id="finishedFilesUploaded"></p>
		</div>
		<div class="buttons center">
			<a href="./collection.php?id=<?= $collection->random_uid ?>" class="button">
				<?= $lang->FINISHED_UPLOAD_BUTTON ?>
			</a>
<!--			<button class="button return">--><?= ""//$lang->UPLOAD_MORE ?><!--</button>-->
		</div>
	</section>

	<!-- Progress bars, one for files, one for bits -->
	<section class="box progress-bar-container margins-off" id="progress-bar-container" hidden>
		<div class="loading" style="margin: auto 0;"></div>
		<!-- Progress number of files -->
		<label class="progress-bar-label">
			<span class="label"><?= $lang->PROGRESS_FILES ?></span>
			<progress id="progress-files" value="0"></progress>
			<span class="exact-numbers">
				<span id="doneFiles">0</span> / <span id="totalFiles"></span>
			</span>
		</label>

		<!-- Progress of bits, filesize -->
		<label class="progress-bar-label">
			<span class="label"><?= $lang->PROGRESS_BITS ?></span>
			<progress id="progress-bits" value="0"></progress>
			<span class="exact-numbers">
				<span id="doneBytes">0 B</span> / <span id="totalBytes"></span>
			</span>
		</label>

		<!-- Progress of batches sent/received -->
		<label class="progress-bar-label">
			<span class="label"><?= $lang->PROGRESS_BATCHES ?></span>
			<progress id="progress-batches" value="0"></progress>
			<span class="exact-numbers">
				<span id="doneBatches">0</span> / <span id="totalBatches"></span>
			</span>
		</label>
	</section>


	<!-- Selected files by the user will be shown here before uploading -->
	<div class="box" id="files-info" hidden>
		<h2 class="center"><?= $lang->SELECTED_FILES_HEADER ?></h2>
		<table>
			<thead>
			<tr>
				<th class="text"><span class="material-icons-outlined">check_circle</span></th>
				<th class="text">Name</th>
				<th class="number">Size</th>
				<th class="center">Type</th>
				<th class="number">Time</th>
				<th class="center">Location</th>
			</tr>
			</thead>
			<tbody id="selectedTableBody">
			</tbody>
		</table>
	</div>

	<hr>

	<section class="box" id="otherUploadMethods">
		<p>
			Other means of uploading:
		</p>
		<a href="upload-csv.php?id=<?= $collection->random_uid ?>">Upload a CSV file with photo IDs</a>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
