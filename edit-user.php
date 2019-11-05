<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

if ( !empty( $_POST ) ) {
	$controller = new UserController();
	$controller->handleRequest( $db, $_POST );

	switch ( $controller->result ) {
		case -2:
			$_SESSION['feedback'] = "<p class='error'>{$lang->USERNAME_NOT_AVAILABLE}</p>";
			break;
		case -1:
			$_SESSION['feedback'] = "<p class='error'>{$lang->TOO_LONG_STRING}</p>";
			break;
		case 1:
			$_SESSION['feedback'] = "<p class='success'>{$lang->NEW_USER_CREATED}</p>";
			header( "Location: ./index.php" );
			exit();
	}
}

$feedback = check_feedback_POST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

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
