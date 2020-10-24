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

$feedback = Common::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] );

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

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<form class="box" method="post">
		<label>
			<span class="label required"><?= $lang->NAME ?></span>
			<input type="text" name="name" value="<?= $collection->name ?>"
			       placeholder="Write new name here" required>
		</label>
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="method" value="edit_name">
	</form>

	<form class="box" method="post">
		<label>
			<span class="label required"><?= $lang->DESCRIPTION ?></span>
			<?php // No line breaks for textarea, because it shows in HTML output to user ?>
			<textarea name="description" cols="30" rows="4"><?= $collection->description ?></textarea>
		</label>
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="method" value="edit_description">
	</form>

	<form class="box" method="post">
		<!-- Public -->
		<label class="checkbox-grid margins-off">
			<input type="checkbox" name="public">
			<span class="label"><?= $lang->PUBLIC ?></span>
		</label>
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="method" value="edit_public">
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
