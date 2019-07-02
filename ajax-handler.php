<?php declare(strict_types=1);

$req = $_GET
	?: $_POST
	?: json_decode( file_get_contents('php://input'), true );

if ( empty($req) ) {
	header('400 Bad Request', true, 400);
	exit;
}

require	$_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $lang Language
 */

$collection = new CollectionController( $db, $lang, $req );

$result = [
	'request' => $req,
	'result' => $collection->result
];

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Allow-Credentials: true" );
header('Content-Type: application/json');
/*
 * Return result in JSON format back to client.
 */
echo json_encode( $result );
