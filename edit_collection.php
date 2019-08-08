<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang
 * @var $user
 */

?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<!-- Feedback from the server goes here. Any possible prints, successes, failures that the server does. -->
<div class="feedback compact" id="feedback"><?= $feedback ?></div>

<main class="main-body-container">

	<!-- One single <form> -->
	<form>
		<!-- Name -->
		<label>
			<span class="label required"><?= $lang->NAME ?></span>
			<input type="text" name="name" required>
		</label>

		<!-- Description -->
		<label>
			<span class="label required"><?= $lang->DESCRIPTION ?></span>
			<input type="text" name="description" required>
		</label>

		<!-- Public -->
		<label>
			<input type="checkbox" name="public">
			<span class="label"><?= $lang->PUBLIC ?></span>
		</label>

		<!-- Editable -->
		<label>
			<input type="checkbox" name="editable">
			<span class="label"><?= $lang->EDITABLE ?></span>
		</label>

		<!-- Cancel & Save -->
		<div>
			<!-- Cancel -->
			<button><?= $lang->CANCEL ?></button>
			<!-- Save -->
			<input type="submit" name="<?= $lang->CANCEL ?>">
		</div>
	</form>
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
