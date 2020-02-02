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
if ( !$user ) {
	$_SESSION['feedback'] = "<p class='error'>{$lang->NOT_LOGGED_IN}</p>";
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
	<section class="box" id="upload-form-box">
		<form method="post" enctype='multipart/form-data' id="upload-form">
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>
			<label>
				<span class="label"><?= $lang->FILE_INPUT ?></span>:
				<input type="file" name="images[]" accept="image/*" id="file-input" multiple="multiple" required>
				<!-- The input also has some english text which cannot be styled or changed. -->
			</label>

			<input type="hidden" name="class" value="image">
			<input type="hidden" name="request" value="upload">
			<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">

			<input type="submit" value="<?= $lang->SUBMIT ?>" class="button" id="submit-button" hidden>
		</form>

		<section class="progress-bar-container" id="progress-bar-container" hidden>
			<label>
				<span class="label"><?= $lang->PROGRESS_FILES ?>></span>
				<progress id="progress-files"></progress>
			</label>
			<label>
				<span class="label"><?= $lang->PROGRESS_BITS ?>></span>
				<progress id="progress-bits"></progress>
			</label>
		</section>
	</section>

	<div class="box" id="files-info" hidden>
		<!-- For info on files to be uploaded. -->
	</div>

	<div class="box" id="successful-uploads"></div>
	<div class="box" id="failed-uploads"></div>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
