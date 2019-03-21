<?php declare(strict_types=1);

if ( empty($_GET['req']) AND empty($_POST['req']) ) {
	header('400 Bad Request');
	exit;
}

require './components/_start.php';
/**
 * @var $db DBConnection
 */

$req = $_GET ?: $_POST;
$collection = new Collection( $db, $req );

$result = $collection->result;

header('Content-Type: application/json');
echo json_encode( $result );
exit();
