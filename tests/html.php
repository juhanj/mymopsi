<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . '/tests' ],
	[ 'Tests', WEB_PATH . '/tests' ],
);

//debug( $_POST );
//debug( $_FILES );
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<style>
</style>

<body class="grid">

<?php require 'html-header.php'; ?>

<main class="main-body-container">
</main>

<?php require 'html-footer.php'; ?>

<script>
	async function getJSON ( url, returnJSON = true ) {
		let response = await fetch( url );
		return (returnJSON) ? await response.json() : await response;
	}

	(async () => {
		console.log(await getJSON( '../json/lang.json' ) )
	})()

	// let lang = getJSON( '../json/lang.json' ).then( (response) => response );
</script>

</body>
</html>
