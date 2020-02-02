<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !empty( $_POST ) ) {
	$controller = new UserController();
	$controller->handleRequest( $db, $user, $_POST );

	if ( $controller->result['success'] ) {
		$_SESSION['feedback'] = "<p class='success'>{$lang->NEW_USER_CREATED}</p>";
		header( "Location: ./index.php" );
		exit();
	}
	elseif ( $controller->result['error'] ) {
		switch ( $controller->result['err'] ) {
			case -2:
				$_SESSION['feedback'] = "<p class='error'>{$lang->USERNAME_NOT_AVAILABLE}</p>";
				break;
			case -1:
				$_SESSION['feedback'] = "<p class='error'>{$lang->STRING_LEN_ERROR}</p>";
				break;
			default:
				$_SESSION['feedback'] = "<p class='error'>Error, unkown error</p>";
		}
	}
}

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
	if ( $user ) {
		require 'html-edit-user.php';
	} else {
		require 'html-create-user.php';
	}
	?>

	<?php if ( $user ) : ?>
		<!-- Collections  -->
		<div class="box">
			<a href="collections.php?user=<?= $user->random_uid ?>" class="button">
				<?= $lang->COLLECTIONS_LINK ?>
			</a>
		</div>
	<?php endif; ?>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
