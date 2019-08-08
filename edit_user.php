<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang
 * @var $user
 */

if ( !empty($_POST) ) {
	$sql = "insert into mymopsi_user (random_uid, username, password, email) 
			values (?,?,?) 
			on duplicate key update username=values(username), password, email"

	/*
	 * If user logged in, save changes
	 */
	if ( $user ) {
		$sql = "insert into "

	}
	/*
	 * Else creating a new user.
	 */
	else {

	}
}

?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<!-- Form - with username & password & email & cancel & save -->
	<form class="box">

		<?php if ( $user ) : ?>
			<h2><?= $lang->EDIT_USER_HEADER ?></h2>
		<?php else : ?>
			<h2><?= $lang->NEW_USER_HEADER ?></h2>
		<?php endif; ?>

		<!-- Username -->
		<label class="compact">
			<span class="label required"><?= $lang->USERNAME ?></span>
			<input type="text" name="name" required>
		</label>

		<!-- Password -->
		<label class="compact">
			<span class="label required"><?= $lang->PASSWORD ?></span>
			<input type="password" name="password" required>
		</label>

		<!-- Email -->
		<label class="compact">
			<span class="label"><?= $lang->EMAIL ?></span>
			<input type="email" name="email">
		</label>

		<!-- Required input explanation -->
		<p class="required-input side-note">
			<span class="required"></span> = <?= $lang->REQUIRED_INPUT ?>
		</p>

		<input type="hidden">


		<!-- Cancel & Save -->
		<div class="buttons margins-off">
			<!-- Cancel -->
			<button class="button light"><?= $lang->CANCEL ?></button>
			<!-- Save -->
			<input type="submit" value="<?= $lang->CANCEL ?>">
		</div>
	</form>

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
