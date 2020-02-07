<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

$request = $_POST;

if ( empty( $request ) ) {
	header( '400 Bad Request', true, 400 );
	exit;
}

/**
 * @var DBConnection $db
 * @var Language $lang
 */

$controller = new UserController();
$controller->handleRequest( $db, $user, $request );

session_regenerate_id(true);

if ( $controller->result['success'] ) {
	$_SESSION['user_id'] = $controller->result['user_id'];
	$_SESSION['username'] = $controller->result['username'] ?: null;
	$_SESSION['feedback'] .= "<p class='success'>{$lang->LOGIN_SUCCESS}</p>";
	header( "Location:././collections.php?user={$controller->result['user_uid']}" );
	exit;
} else {
	$_SESSION['feedback'] .= "<p class='error'>{$lang->LOGIN_FAIL}</p>";
	$_SESSION['feedback'] .= "<p class='error'>{$controller->result['errMsg']}</p>";
	header( "Location:./index.php" );
	exit;
}
