<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !$user ) {
	header('location: index.php' );
	$_SESSION['feedback'] = "<p class='warning'>{$lang->LOGIN_REQUIRED}</p>";
}

if ( !empty( $_POST ) ) {
	$controller = new CollectionController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result['success'] ) {
		$_SESSION['feedback'] .= "<p class='success'>{$lang->NEW_COLL_SUCCESS}</p>";
		header( "Location:./collections.php?user={$user->random_uid}" );
		exit;
	} else {
		$_SESSION['feedback'] .= "<p class='error'>{$lang->COLL_FAIL}</p>";
		$_SESSION['feedback'] .= "<p class='error'>{$controller->result['errMsg']}</p>";
	}
}

$collection = Collection::fetchCollectionByRUID( $db, $_GET['id'] ?? '' );

$feedback = Utils::checkFeedbackAndPOST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<?php
	if ( $collection ) {
		require 'html-edit-collection.php';
	} else {
		require 'html-create-collection.php';
	}
	?>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
