<?php declare(strict_types=1);

/**
 * Start stuff, copied from _start.php
 * here was a line that fetched something from database that doesn't exist, which breaks the script
 */
define( 'DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] );
define( 'WEB_PATH', '/mopsi_dev/mymopsi/' );
set_include_path(
	get_include_path() . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/class/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/cfg/' . PATH_SEPARATOR );
spl_autoload_extensions( '.class.php' );
spl_autoload_register();
define(
	'INI',
	parse_ini_file( (parse_ini_file( 'config.ini.php' )[ 'config' ]), true, INI_SCANNER_TYPED )
);
$db = new DBConnection();

/**
 * Actual install starts here
 */
echo "<pre>";

$f = file( './database.sql', FILE_IGNORE_NEW_LINES ); // Fetch tables from file

// Remove .sql comments
foreach ( $f as $k => $v ) {
	$f[ $k ] = strstr( $v, '--', true ) ?: $v;
}

// Every query into it's own index in the array
$db_file = explode( ";", implode( "", $f ) );
foreach ( $db_file as $sql ) {
	if ( !empty( $sql ) && strlen( $sql ) > 5 ) {
		$db->query( $sql );
	}
}

echo '<p>Database installed successfully.</p>';
echo '<a href="../">Link to front page.</a>';

/*
 * Creating an admin user
 */
$controller = new UserController();

$db->query(
	"insert into mymopsi_user (random_uid,username,password) values (?,?,?),(?,?,?)",
	[
		Common::createRandomUID( $db, 20, false ), 'admin', password_hash( 'admin', PASSWORD_DEFAULT ),
		Common::createRandomUID( $db, 20, false ), 'user', password_hash( 'user', PASSWORD_DEFAULT ),
	]
);

$db->query( 'update mymopsi_user set admin = true where id = 1 limit 1' );

$admin = User::fetchUserByID( $db, 1 );
$user = User::fetchUserByID( $db, 2 );

print_r( $admin );
print_r( $user );
