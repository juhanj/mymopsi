<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

$req = $_GET
	?: $_POST
		?: json_decode( file_get_contents('php://input'), true );

if ( !empty($req) ) {
	header('Content-Type: application/json');
	$req['success'] = true;
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

</main>

<?php require 'html-footer.php'; ?>

<script>

    window.onload = () => {
        sendJSON( { "req" : "jsontest" }, './ajax-test.php' )
            .then( data => console.log(data) );
    }

</script>

</body>
</html>
