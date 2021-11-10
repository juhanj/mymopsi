<?php declare(strict_types=1);
require './components/_start.php';

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

// For public/private change
//TODO: change, too lazy to do now --jj 211109
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
   ['User', WEB_PATH . 'collections.php' ],
   ['Collection', WEB_PATH . 'collection.php?id=' . $collection->random_uid ]
);
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container medium-width">

	<!-- Edit name -->
	<form class="box" method="post">
		<!-- Input field -->
		<label>
			<span class="label"><?= $lang->NAME ?></span>
			<input type="text" name="name" value="<?= $collection->name ?>"
			       placeholder="Write new name here" required
			       id="nameInput">
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="request" value="edit_name">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>" id="nameSubmit">
	</form>

	<!-- Edit description -->
	<form class="box" method="post">
		<!-- Input field -->
		<label>
			<span class="label"><?= $lang->DESCRIPTION ?></span>
			<?php // No line breaks for textarea, because it shows in HTML output to user ?>
			<textarea name="description" cols="30" rows="4" id="descriptionInput"><?= $collection->description ?></textarea>
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="request" value="edit_description">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">
		<!-- Submit -->
		<input type="submit" class="button" value="<?= $lang->SUBMIT ?>" id="descriptionSubmit">
	</form>

	<!-- Edit visibiblity (public / private)-->
	<form class="box" method="post">
		<!-- Input field -->
		<label class="checkbox-grid margins-off">
			<input type="checkbox" name="public" <?= ($collection->public) ? 'checked' : '' ?>
			       onchange="this.form.submit()">
			<span class="label"><?= $lang->PUBLIC ?></span>
		</label>
		<!-- Server stuff for PHP request handling -->
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="request" value="edit_public">
		<input type="hidden" name="collection" value="<?= $collection->random_uid ?>">
		<!-- Submit is done automatically when input changed -->
	</form>

	<hr>

	<section class="box warning">
		<p>
			<?= $lang->DANGER_DELETE_INFO ?>
		</p>
		<button class="button red" id="deleteButton"
		        data-collection="<?= $collection->random_uid ?>">
			<?= $lang->DELETE_BUTTON ?>
		</button>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
