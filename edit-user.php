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
		$_SESSION['feedback'] = "<p class='success'>{$lang->EDIT_SAVED}</p>";
	}
	elseif ( $controller->result['error'] ) {
		$_SESSION['feedback'] = "<p class='error'>Error, {$controller->result['errMsg']}</p>";
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

	<!-- Username edit container -->
	<section class="box">
		<form method="post">
			<!-- username -->
			<label for="name">
				<span class="label required"><?= $lang->USERNAME ?></span>
				<input type="text" name="username" value="<?= $user->username ?>" required
					minlength="<?= INI['Settings']['username_min_len'] ?>"
					maxlength="<?= INI['Settings']['username_max_len'] ?>">
			</label>
			<!-- Server-stuff -->
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="request" value="edit_username">
			<!-- Submit -->
			<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
		</form>
	</section>

	<!-- Password edit container -->
	<section class="box">
		<form method="post">
			<!-- Password -->
			<label for="password">
				<span class="label required"><?= $lang->PASSWORD ?></span>
				<input type="password" name="password" value="" required
					minlength="<?= INI['Settings']['password_min_len'] ?>"
					maxlength="<?= INI['Settings']['password_max_len'] ?>">
			</label>
			<!-- Password confirm (client-side check only) -->
			<!--<label for="password-confirm">
				<span class="label required"><?/*= $lang->CONFIRM_PASSWORD */?></span>
				<input type="password" name="password-confirm" value="" required
					minlength="<?/*= INI['Settings']['password_min_len'] */?>"
					maxlength="<?/*= INI['Settings']['password_max_len'] */?>">
			</label>-->
			<!-- Server-side stuff -->
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="request" value="edit_password">
			<!-- Submit button -->
			<input type="submit" class="button" value="<?= $lang->SUBMIT ?>">
		</form>
	</section>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
