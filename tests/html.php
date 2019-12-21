<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

Utils::debug( $_POST );
Utils::debug( $_FILES );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">

	<div class="box">

		<form method="post" enctype="multipart/form-data">
			<input type="file" name="test[]" multiple>
			<input type="hidden" name="foo" value="hidden">
			<input type="submit">
		</form>

	</div>

</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
