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

	<section class="box">
		<p>Nothing to see here yet.
	</section>

	<section class="box">
		<form method="post">
			<label for="name">
				<span class="label required"><?= $lang->USERNAME ?></span>
				<input type="text" name="name" value="<?= $user->username ?>" required
					minlength="<?= INI['Settings']['username_min_len'] ?>"
					maxlength="<?= INI['Settings']['username_max_len'] ?>">
			</label>
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="method" value="edit-username">
			<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
		</form>
	</section>

	<section class="box">
		<form method="post">
			<label for="password">
				<span class="label required"><?= $lang->PASSWORD ?></span>
				<input type="password" name="password" value="" required
					minlength="<?= INI['Settings']['password_min_len'] ?>"
					maxlength="<?= INI['Settings']['password_max_len'] ?>">
			</label>
			<label for="password-confirm">
				<span class="label required"><?= $lang->PASSWORD_CONFIRM ?></span>
				<input type="password" name="password-confirm" value="" required
					minlength="<?= INI['Settings']['password_min_len'] ?>"
					maxlength="<?= INI['Settings']['password_max_len'] ?>">
			</label>
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="method" value="edit-password">
			<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
		</form>
	</section>


	<section class="box">
		<p><?= $lang->COLLECTIONS_NRO ?>: </p>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
