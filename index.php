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

<head>
	<style>
		a{ text-decoration:none}
	</style>
</head>

<body>

<?php require 'html-header.php'; ?>

<main class="main_body_container">

	<div class="title">Mymopsi</div>

	<div class="menu-head">
		<form action="./view.php" method="get">
			<input type="text" name="id" placeholder="ID of collection" class="text">
			<input type="submit" value="Show collection" class="submit">
		</form>
    </div>

	<div class="upload">
		<a href="upload.php">
			<div class="upload-button">Upload new collection</div>
		</a>
	</div>

	<form class="menu-body">
        <input type="text" placeholder="admin" value="admin" autocomplete="username" hidden aria-hidden="true">
		<input type="password" placeholder="Password" autocomplete="current-password" class="text">
		<input type="submit" value="For Admin" class="submit">
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
