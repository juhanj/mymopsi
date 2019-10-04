<?php declare(strict_types=1);

$request = $_POST;

if ( empty( $request ) ) {
	header( '400 Bad Request', true, 400 );
	exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 */

$controller = new UserController();
$controller->handleRequest( $db, $request );

if ( $controller->result['success'] ) {
	$_SESSION['feedback'] .= "<p class='success'>{$lang->LOGIN_SUCCESS}</p>";
	header( "Location:./index.php" );
	exit;
} else {
	$_SESSION['feedback'] .= "<p class='error'>{$lang->LOGIN_FAIL}</p>";
	header( "Location:./index.php" );
	exit;
}
