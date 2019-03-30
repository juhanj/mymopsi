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

<body>

<?php require DOC_ROOT . '/components/html-header.php'; ?>

<main class="main_body_container">

    <div class="feedback" id="feedback"><?= $feedback ?></div>

	<div class="form">
		<form>
			<input type="text" placeholder="ID of collection">
			<input type="submit" value="Show collection">		
		</form>
	</div>

	<!--// similar behavior as clicking on a link
		window.location.href = "http://stackoverflow.com";-->
	<hr>

	<a href="upload.php">Upload new collection</a>

	<hr>

	<form>
        <input type="text" placeholder="admin" value="admin" autocomplete="username" hidden aria-hidden="true">
		<input type="password" placeholder="Password" autocomplete="current-password">
		<input type="submit" value="For Admin">
	</form>
</main>

<?php require DOC_ROOT . '/components/html-footer.php'; ?>

<script>
</script>

</body>
</html>
