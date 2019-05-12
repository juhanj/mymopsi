<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 */
$feedback = check_feedback_POST();
?>

<!DOCTYPE html>
<html lang="<?= $lang->lang ?>">

<?php require 'html-head.php'; ?>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<h1 class="title">Mymopsi</h1>

	<div class="menu-head">
		<form action="./view.php" method="get">
			<input type="text" name="id" placeholder="<?= $lang->COLL_PLACEHOLDER ?>" class="text">
			<input type="submit" value="<?= $lang->COLL_SUBMIT ?>" class="submit">
		</form>
    </div>

	<div class="upload">
		<a href="upload.php">
			<div class="upload-button"><?= $lang->UPLOAD_NEW ?></div>
		</a>
	</div>

	<form class="menu-body">
		<!-- Hidden field for admin username because Chrome wants one. Something about accessiblity. -->
        <input type="text" placeholder="admin" value="admin" autocomplete="username" hidden aria-hidden="true">
		<input type="password" placeholder="<?= $lang->ADMIN_PW_PLACEHOLDER ?>" autocomplete="current-password" class="text">
		<input type="submit" value="<?= $lang->ADMIN_SUBMIT ?>" class="submit">
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
