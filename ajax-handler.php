<?php declare(strict_types=1);

$req = $_GET
	?: $_POST
	?: json_decode( file_get_contents('php://input'), true );

if ( empty($req) ) {
	header('400 Bad Request', true, 400);
	exit;
}

require './components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang Language
 */

$collection = new CollectionController( $db, $req );

$result = [
	'request' => $req,
	'result' => $collection->result
];

/*
 * Return result in JSON format back to client.
 */
echo json_encode( $result );

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Allow-Credentials: true" );
header('Content-Type: application/json');
exit();
