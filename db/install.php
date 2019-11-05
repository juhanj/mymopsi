<?php declare( strict_types=1 );
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';

echo "<pre>";

$f = file( './database.sql', FILE_IGNORE_NEW_LINES ); // Fetch tables from file

// Remove .sql comments
foreach ( $f as $k => $v ) {
	$f[$k] = strstr( $v, '--', true ) ?: $v;
}

// Every query into it's own index in the array
$db_file = explode( ";", implode( "", $f ) );
foreach ( $db_file as $sql ) {
	if ( !empty( $sql ) && strlen( $sql ) > 5 ) {
		$db->query( $sql );
	}
}

echo '<p>Database installed successfully.</p>';

/*
 * Creating an admin user
 */
$controller = new UserController();

$controller->addNewUserToDatabase( $db, 'admin' );
$admin = User::fetchUserByID( $db, 1 );
$controller->setPassword( $db, $admin, 'password' );

$db->query( 'update mymopsi_user set admin = true where id = 1 limit 1' );

$admin = User::fetchUserByID( $db, 1 );

debug( $admin );

echo '<a href="../">Link to front page.</a>';
