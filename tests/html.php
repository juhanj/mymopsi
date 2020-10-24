<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

array_push(
	$breadcrumbs_navigation,
	[ 'Settings', WEB_PATH . '/tests' ],
	[ 'Tests', WEB_PATH . '/tests' ],
);

//Utils::debug( $_POST );
//Utils::debug( $_FILES );
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
	let latitude = 62.5913800;
	let longitude = 29.7796980;

	let parameters = {
		'request_type' : 'get_address',
		'lat' : latitude,
		'lon' : longitude
	}

	let url = 'https://cs.uef.fi/mopsi/mobile/server.php?param='
		+ encodeURIComponent(JSON.stringify( parameters ));

	fetch( url )
		.then(data=>{return data.json()})
		.then(res=>{console.log(res)});
</script>

</body>
</html>
