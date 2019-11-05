<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang Language
 */

if ( empty($_GET['id']) ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->COLL_ID_REQ}</p>";
	header('location: index.php');
	exit();
}

$feedback = check_feedback_POST();

$collection = Collection::fetchCollection( $db, $_GET['id']);

if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->COLL_ID_REQ}</p>";
	header( 'location: index.php' );
	exit();
}
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
	<a href="collection.php?id=<?= $collection->random_uid ?>" class="button">
		<i class="material-icons">arrow_back</i>
		<?= $lang->BACK_TO_COLL ?>
	</a>

	<!-- The form itself.
	    Contains <input type=file>-tag, and two hidden input-tags (request and collection-ID)
	    The submit is handled by javascript, since we send one file at a time (very large uploads).
	-->
    <form method="post" enctype='multipart/form-data' id="uploadForm">
	    <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
        <label>
	        <span class="label"><?= $lang->FILE_INPUT ?></span>:
            <input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple" required>
	        <!-- The input also has some english text which cannot be styles or changed. -->
        </label>

        <input type="hidden" name="request" value="addImagesToCollection">
	    <input type="hidden" name="collection-uid" value="<?= $collection->random_uid ?>">
        <input type="submit" value="Submit images" class="button" id="submitButton">

        <p class="side-note"><?= $lang->DRAG_DROP ?></p>
    </form>

    <div id="filesInfo">
        <!-- For info on files to be uploaded. -->
    </div>
</main>

<dialog id="modal">
	<header>
		<h2 id="upload-modal-title">
			<?= $lang->MODAL_TITLE ?>
		</h2>

		<button id="modal-close">❌</button>
	</header>

	<div id="upload-modal-content">
		<label>
			<span class="label">Number of files uploaded TRANSLATION</span>
			<progress id="upload-progress-bar-files"></progress>
		</label>
		<label>
			<span class="label">... bytes uploaded TRANSLATION</span>
			<progress id="upload-progress-bar-bytes"></progress>
		</label>
		<table>
			<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Size</th>
				<th>Date</th>
				<th>Success?</th>
			</tr>
			</thead>
			<tbody id="progress-upload-table-body">
			</tbody>
		</table>
	</div>
</dialog>


<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
