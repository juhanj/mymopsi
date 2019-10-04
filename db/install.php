<?php declare(strict_types=1);
error_reporting( E_ERROR );
ini_set( 'display_errors', "1" );

print("<pre>");

// Has necessary database information
$config =
	parse_ini_file(
		"../cfg/config.ini",
		true,
		INI_SCANNER_TYPED
	);

require '../class/dbconnection.class.php';
$db = new DBConnection( $config['Database'] );

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

echo '<a href="../">Link to front page.</a>';
