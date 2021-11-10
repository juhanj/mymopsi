<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$feedback = Common::checkFeedbackAndPOST();

array_push(
   $breadcrumbs_navigation,
   [ 'User', WEB_PATH . 'collections.php' ],
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

	<!-- Username edit container -->
	<section class="box">
		<form method="post">
			<!-- username -->
			<label for="name">
				<span class="label"><?= $lang->USERNAME ?></span>
				<input type="text" name="username" value="<?= $user->username ?>" required
					minlength="<?= INI['Settings']['username_min_len'] ?>"
					maxlength="<?= INI['Settings']['username_max_len'] ?>"
					id="usernameInput">
			</label>
			<!-- Server-stuff -->
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="request" value="edit_username">
			<!-- Submit -->
			<input type="submit" class="button" value="<?= $lang->CHANGE_UN ?>"
				id="usernameSubmit" disabled>
		</form>
	</section>

	<!-- Password edit container -->
	<section class="box">
		<form method="post">
			<!-- Password -->
			<label for="password">
				<span class="label"><?= $lang->PASSWORD ?></span>
				<input type="password" name="password" value="" required
					minlength="<?= INI['Settings']['password_min_len'] ?>"
					maxlength="<?= INI['Settings']['password_max_len'] ?>"
					id="passwordInput">
			</label>

			<!-- Password confirm (client-side check only) -->
			<!-- There was a reason why this is commented out, can't remember why
			    Something about this being useless? -->
			<!--<label for="password-confirm">
				<span class="label required"></span>
				<input type="password" name="password-confirm" value="" required
					minlength=""
					maxlength="">
			</label>-->

			<!-- Server-side stuff -->
			<input type="hidden" name="class" value="user">
			<input type="hidden" name="request" value="edit_password">
			<!-- Submit button -->
			<input type="submit" class="button" value="<?= $lang->CHANGE_PW ?>"
				id="passwordSubmit" disabled>
		</form>
	</section>

	<hr>

	<section class="box warning">
		<p>
			<?= $lang->DANGER_DELETE_INFO ?>
		</p>
		<button class="button red" id="deleteButton"
		        data-user="<?= $user->random_uid ?>">
			<?= $lang->DELETE_USER_BUTTON ?>
		</button>
	</section>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
