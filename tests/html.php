<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . '/tests' ],
	[ 'Tests', WEB_PATH . '/tests' ],
);

//debug( $_POST );
//debug( $_FILES );
//debug( $_COOKIE );
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
		<button class="button" id="button">Press here!</button>
	</div>
</main>

<?php require 'html-footer.php'; ?>

<script type="module">
</script>

</body>
</html>
