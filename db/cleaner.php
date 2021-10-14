<?php declare(strict_types=1);
require '../components/_start.php';
/** @var DBConnection $db */
/*/////////////////////////////////////////////////*/

/*
 * Cleaning collections directory
 */

$collectionsPath = INI[ 'Misc' ][ 'path_to_collections' ];

$folders = scandir( $collectionsPath );

debug( scandir( $collectionsPath ) );

foreach ( $folders as $folder ) {
	if ( $folder == '.' or $folder == '..' ) {
		continue;
	}

	$singleCollDirectory = INI[ 'Misc' ][ 'path_to_collections' ] . $folder;

	debug( $singleCollDirectory );
	debug( scandir($singleCollDirectory) );

	Common::deleteFiles( $singleCollDirectory );
}

mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . 'temp/' );

/*
 * Dropping tables, and recreating them with testdata
 */

$db->query( "SET FOREIGN_KEY_CHECKS=0" );

$sql = "drop table if exists mymopsi_img, mymopsi_collection, mymopsi_user, mymopsi_user_third_party_link";
$db->query( $sql );

$db->query( "SET FOREIGN_KEY_CHECKS=1" );

echo "<a href='install.php'>Link to install</a>";
