<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

// Valid user logged in
if ( !$user ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->NOT_LOGGED_IN}</p>";
	header( 'location: index.php' );
	exit();
}
// Valid collection RUID provided via GET
if ( empty( $_GET[ 'id' ] ) ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->ID_MISSING}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}

$feedback = Common::checkFeedbackAndPOST();

$collection = Collection::fetchCollectionByRUID( $db, $_GET[ 'id' ] );

// Valid collection found with given RUID
if ( !$collection ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->COLLECTION_INVALID}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}
// Check either collection owner, public&editable collection (not implemented), or admin user
if ( (!$collection->public and !$collection->editable) and ($collection->owner_id !== $user->id) ) {
	$_SESSION[ 'feedback' ] = "<p class='error'>{$lang->NOT_COLL_OWNER}</p>";
	header( 'location: ' . $_SERVER[ 'HTTP_REFERER' ] );
	exit();
}

array_push(
	$breadcrumbs_navigation,
	[ 'User', WEB_PATH . '/collections.php' ],
	[ 'Collection', WEB_PATH . '/collection.php?id=' . $collection->random_uid ],
);
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
		<form id="csv-form">
			<input type="file" accept="text/plain" id="csv-input">

			<button type="submit" class="button" id="submit-button">
				<?= $lang->SUBMIT ?>
				<?= file_get_contents( './img/upload.svg' ) ?>
			</button>
		</form>
	</div>

	<div id="output"></div>

</main>

<?php require 'html-footer.php'; ?>

<script src="https://d3js.org/d3-dsv.v1.min.js"></script>
<script>
	'use strict';

	function handleRequestResponse ( response ) {
		console.log( response );
	}

	function handleCSVFile ( event ) {
		let csv = event.target.result;
		let data = d3.csvParse( csv );


		let photo_ids = data.map( obj => {
			return { 'photo_id': obj.photo_id };
		} );

		console.log( { data, photo_ids } );

		let request = {
			'class': 'image',
			'request': 'upload_mopsi_csv',
			'collection': '<?= $collection->random_uid ?>',
			'photos': photo_ids,
		};

		sendJSON( request )
			.then( handleRequestResponse );
	}

	let input = document.getElementById( 'csv-input' );
	let output = document.getElementById( 'output' );

	input.onchange = function () {
		console.log( input.files );
		// console.log( d3.parse( input.files[0] ) );

		let reader = new FileReader();

		// this is called after reader.readAsText()
		reader.onload = handleCSVFile;

		// start reading the file. When it is done, calls the onload event defined above.
		reader.readAsText( input.files[0] );
	}
</script>

</body>
</html>
