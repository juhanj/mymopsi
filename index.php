<?php declare(strict_types=1);
require './components/_start.php';
/**
 * @var $db DBConnection
 */
$feedback = check_feedback_POST();
?>

<!DOCTYPE html>
<html lang="fi">

<?php require DOC_ROOT . '/components/html-head.php'; ?>

<head>
	<style>
		a{ text-decoration:none}
	</style>
</head>

<body>

<?php require DOC_ROOT . '/components/html-header.php'; ?>

<main class="main_body_container">

	<div class="title">Mymopsi</div>

	<div class="menu-head">
		<form action="collection.php" method="get">
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

<?php require DOC_ROOT . '/components/html-footer.php'; ?>

<script>
</script>

</body>
</html>
