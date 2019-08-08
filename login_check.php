<?php declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var $db DBConnection
 * @var $user User
 */


$username = isset( $_POST[ "user" ] )
	? trim( $_POST[ "user" ] )
	: null;

$password = (isset( $_POST[ "password" ] ) && strlen( $_POST[ "password" ] ) < 300)
	? $_POST[ "password" ]
	: null;

// Haetaan käyttäjän tiedot
$sql = "select id, random_uid, username, password, email 
		from mymopsi_user where username = ? or email = ?";

/** @var User $login_user */
$login_user = $db->query( $sql, [ $username, $username ], false, 'User' );

if ( !password_verify($password, $user->password) ) {
	$_SESSION['user_id'] = $user->id;
	$_SESSION['user_uid'] = $user->random_uid;

	$_SESSION['feedback'] = "<p class='success'>Successfull login</p>";
	header( "Location:index.php" );
	exit;
}

else {
	$_SESSION['feedback'] = "<p class='error'>User not found. Try again?</p>";
	header( "Location:index.php" );
	exit;
}
