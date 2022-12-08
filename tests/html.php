<?php declare(strict_types=1);
require '../components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . 'tests' ],
	[ 'Tests', WEB_PATH . 'tests' ],
);

//debug( $_GET );
//debug( $_POST );
//debug( $_FILES );
//debug( $_COOKIE );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
	#img {
		width: 100%;
		min-width: 256px;

		max-height: 256px;
	}
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
	<img src="../img/mopsi256.png" id="img">
</main>

<?php require 'html-footer.php'; ?>

<script>
</script>

</body>
</html>
