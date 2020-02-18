<?php declare(strict_types=1);

$request = $_GET
	?: $_POST
		?: json_decode( file_get_contents( 'php://input' ), true );

if ( empty( $request ) ) {
	header( '400 Bad Request', true, 400 );
	exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */


$class_controller = $request['class'] . 'Controller';

/**
 * @var Controller $controller
 */
$controller = new $class_controller();

$controller->handleRequest( $db, $user, $request );

$result = [
	'request' => $request,
	'result' => $controller->result
];

header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
header( 'Access-Control-Allow-Methods: GET, POST' );
header( "Access-Control-Allow-Headers: X-Requested-With" );
header( "Access-Control-Allow-Credentials: true" );
header( 'Content-Type: application/json' );
/*
 * Return result in JSON format back to client.
 */
echo json_encode(
	$result,
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
);
