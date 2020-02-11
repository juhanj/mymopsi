<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

// TODO: Fix this whole page of a mess. Complete rewrite.
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

	<section class="box">
		<p>Nothing to see here yet.
	</section>

	<form class="box" method="post">
		<label>
			<span class="label required"><?= $lang->NAME ?></span>
			<input type="text" name="name" value="<?= $collection->name ?>" required>
		</label>
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="method" value="edit-name">
	</form>

	<form class="box" method="post">
		<label> <input type="text" name="description" value="<?= $collection->description ?>"> </label>
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="method" value="edit-description">
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
