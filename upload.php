<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( empty( $_GET['id'] ) ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->COLL_ID_REQ}</p>";
	header( 'location: index.php' );
	exit();
}

$feedback = Utils::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] );

if ( !$collection ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->COLL_ID_REQ}</p>";
	header( 'location: index.php' );
	exit();
}
if ( (!$collection->public and !$collection->editable) and ($collection->owner_id !== $user->id) ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NOT_COLL_OWNER}</p>";
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
	<section class="box">
		<form method="post" enctype='multipart/form-data' id="uploadForm">
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>
			<label>
				<span class="label"><?= $lang->FILE_INPUT ?></span>:
				<input type="file" name="images[]" accept="image/*" id="fileInput" multiple="multiple" required>
				<!-- The input also has some english text which cannot be styles or changed. -->
			</label>

			<input type="hidden" name="request" value="addImagesToCollection">
			<input type="hidden" name="collection-uid" value="<?= $collection->random_uid ?>">

			<input type="submit" value="<?= $lang->SUBMIT ?>" class="button" id="submitButton" hidden>
			<button class="button" id="changeFiles" hidden>
				<?= $lang->CHANGE_FILES ?>
			</button>
		</form>
	</section>

	<div id="filesInfo">
		<!-- For info on files to be uploaded. -->
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
