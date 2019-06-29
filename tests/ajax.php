<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

$get = $_GET ?? null;
$post = $_POST ?? null;
$file = $_FILES ?? null;
$input = json_decode( file_get_contents( 'php://input' ) , true ) ?? null;

if ( !empty( $get ) or !empty( $post ) or !empty( $file ) or !empty( $input ) ) {
	header( 'Content-Type: application/json' );
	$req = [
		"request" => [ $get , $post , $file , $input ]
	];
	echo json_encode( $req );
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<?php require 'html-head.php'; ?>

<body>

<?php require 'html-header.php'; ?>

<main class="main_body_container">

	<button>TEST ME!</button>
	<p id="test">
		<!-- For info on files to be uploaded. -->
		<!--		--><?php //debug($_SERVER) ?>
	</p>

	<form id="form-id" action="./test.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="hidden" value="test">
		<input type="file" name="files[]" accept="*" multiple="multiple" id="file-id">
		<input type="submit" value="Submit" id="submit-id">
	</form>

</main>

<?php require 'html-footer.php'; ?>

<script>

	let form = document.getElementById('form-id');
	let fileInput = document.getElementById('file-id');
	let filesArray;
	let maxBatchSize = 250*1024; //1 MB //250KB

	fileInput.onchange = () => {
		filesArray = Array.from(fileInput.files)
	};

	form.onsubmit = (event) => {
		event.preventDefault();

		let filesAsFormData = new FormData();
		filesAsFormData.append('test',"foo");

		let indx = 0;
		while ( indx in filesArray ) {
			filesAsFormData.delete('files[]');

			// let currentBatch = [];
			let currentBatchSize = 0;

			while ( indx in filesArray && (currentBatchSize+filesArray[indx].size) < maxBatchSize ) {

				// currentBatch.push( filesArray[indx] );
				filesAsFormData.append( 'files[]', filesArray[indx] );
				currentBatchSize += filesArray[indx].size;
				indx++;
			}

			console.log('sending...');
			sendForm( filesAsFormData, './ajax.php' )
				.then((jsonResponse) => {
					console.log(jsonResponse);
				});
		}

	}

</script>

</body>
</html>
