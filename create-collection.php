<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

// If no user logged-in, we send back to front page with error message
if ( !$user ) {
	header( 'location: index.php' );
	$_SESSION[ 'feedback' ] = "<p class='warning'>{$lang->LOGIN_REQUIRED}</p>";
	exit();
}

// After form submit, send request to CollectionController for processing
if ( !empty( $_POST ) ) {
	$controller = new CollectionController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result[ 'success' ] ) {
		$_SESSION[ 'feedback' ] .= "<p class='success'>{$lang->NEW_COLL_SUCCESS}</p>";
		header( "Location:./collection.php?id={$controller->result['collection_uid']}" );
		exit;
	}
	else {
		$_SESSION[ 'feedback' ] .= "<p class='error'>{$lang->COLL_FAIL}</p>";
		$_SESSION[ 'feedback' ] .= "<p class='error'>{$controller->result['errMsg']}</p>";
	}
}

$feedback = Common::checkFeedbackAndPOST();

array_push(
	$breadcrumbs_navigation,
	[ 'Collections', WEB_PATH . 'collections.php' ]
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

	<!-- One single <form> -->
	<form method="post" class="box">
		<!-- Name -->
		<label>
			<span class="label"><?= $lang->NAME ?></span>
			<input type="text" name="name">
		</label>

		<!-- Description -->
		<label>
			<span class="label"><?= $lang->DESCRIPTION ?></span>
			<textarea name="description" rows="3"></textarea>
		</label>

		<!-- Public -->
		<label class="checkbox-grid margins-off">
			<input type="checkbox" name="public">
			<span class="label"><?= $lang->PUBLIC ?></span>
			<span class="label-info"><?= $lang->PUBLIC_INFO ?></span>
		</label>

		<!-- Editable (hidden for now, might not ever finish) -->
		<label hidden>
			<input type="checkbox" name="editable">
			<span class="label"><?= $lang->EDITABLE ?></span>
			<span><?= $lang->EDITABLE_INFO ?></span>
		</label>

		<!-- Hidden stuff for server-side handler -->
		<input type="hidden" name="class" value="collection">
		<input type="hidden" name="request" value="new_collection">


		<p class="required-input side-note">
			<span class="required"></span> = <?= $lang->REQUIRED_INPUT ?>
		</p>

		<!-- Cancel & Save -->
		<div>
			<!-- Save -->
			<input type="submit" value="<?= $lang->SUBMIT ?>" class="button">
		</div>
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
