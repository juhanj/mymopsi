<?php declare(strict_types=1);
error_reporting(E_ERROR);
ini_set('display_errors', "1");

print("<pre>");

// Has necessary database information
$config = parse_ini_file( "../config/config.ini.php", true);

require '../class/dbconnection.class.php';
$db = new DBConnection( $config['Tietokanta'] );

$f = file('./database.sql', FILE_IGNORE_NEW_LINES); // Fetch tables from file

// Remove .sql comments
foreach ( $f as $k => $v ) {
    $f[$k] = strstr($v, '--', true) ?: $v;
}

// Every query into it's own index in the array
$db_file = explode( ";", implode("", $f) );
foreach ( $db_file as $sql ) {
    if ( !empty($sql) && strlen($sql) > 5 ) {
        $db->query( $sql );
    }
}

echo '<p>Tietokannan asennus on nyt suoritettu.</p>';
