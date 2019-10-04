<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang
 * @var $user
 */

if ( !$user ) {
	header('location: index.php' );
	$_SESSION['feedback'] = "<p class='warning'>{$lang->LOGIN_REQUIRED}</p>";
}

if ( !empty( $_POST ) ) {

	$controller = new CollectionControllerNEW();

	if ( $_POST['type'] === 'new' ) {
		$controller->createNewCollection(
			$db, $user, $_POST['name'], $_POST['description'], $_POST['public'], $_POST['editable']
		);
	}
}

$collection = Collection::fetchCollection( $db, $_GET['uid'] ?? '' );

$feedback = check_feedback_POST();
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
