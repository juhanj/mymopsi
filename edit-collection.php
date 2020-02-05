<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !$user ) {
	header( 'location: index.php' );
	$_SESSION['feedback'] = "<p class='warning'>{$lang->LOGIN_REQUIRED}</p>";
	exit();
}

if ( empty($_GET['id']) ) {
	header( 'location: index.php' );
	$_SESSION['feedback'] = "<p class='warning'>{$lang->INVALID_ID}</p>";
	exit();
}

if ( !empty( $_POST ) ) {
	$controller = new CollectionController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result['success'] ) {
		$_SESSION['feedback'] .= "<p class='success'>{$lang->EDIT_SUCCESS}</p>";
	} else {
		$_SESSION['feedback'] .= "<p class='error'>{$lang->EDIT_FAIL}</p>";
		$_SESSION['feedback'] .= "<p class='error'>{$controller->result['errMsg']}</p>";
	}
}

$feedback = Utils::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] );
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<form method="post" class="box">
		<!-- Name -->
		<label>
			<span class="label required"><?= $lang->NAME ?></span>
			<input type="text" name="name" value="<?= $collection->name ?>" required>
		</label>

		<!-- Description -->
		<label>
			<span class="label required"><?= $lang->DESCRIPTION ?></span>
			<input type="text" name="description" value="<?= $collection->description ?>" required>
		</label>

		<!-- Public -->
		<label>
			<input type="checkbox" name="public" <?= $collection->public ? 'checked' : '' ?> >
			<span class="label"><?= $lang->PUBLIC ?></span>
		</label>

		<!-- Editable -->
		<label>
			<input type="checkbox" name="editable" <?= $collection->editable ? 'checked' : '' ?> >
			<span class="label"><?= $lang->EDITABLE ?></span>
		</label>

		<!-- Hidden stuff for server-side handler -->
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="request" value="edit">

		<!-- Cancel & Save -->
		<div>
			<!-- Cancel -->
			<button><?= $lang->CANCEL ?></button>
			<!-- Save -->
			<input type="submit" name="<?= $lang->CANCEL ?>">
		</div>
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
