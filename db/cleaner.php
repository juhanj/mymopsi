<?php declare(strict_types=1);
require '../components/_start.php';
/** @var DBConnection $db */
/*/////////////////////////////////////////////////*/

/*
 * Cleaning collections directory
 */

$folders = scandir( INI[ 'Misc' ][ 'path_to_collections' ] );

Common::debug( $folders );

foreach ( $folders as $folder ) {
	if ( $folder == '.' or $folder == '..' ) {
		continue;
	}

	$imgs = scandir( INI[ 'Misc' ][ 'path_to_collections' ] . '/' . $folder );

	Common::debug( $imgs );

	foreach ( $imgs as $img ) {
		if ( $img == '.' or $img == '..' ) {
			continue;
		}

		unlink( INI[ 'Misc' ][ 'path_to_collections' ] . '/' . $folder . '/' . $img );
	}

	Common::debug( rmdir( INI[ 'Misc' ][ 'path_to_collections' ] . '/' . $folder ) );
}

/*
 * Dropping tables, and recreating them with testdata
 */

$db->query( "SET FOREIGN_KEY_CHECKS=0" );

$sql = "drop table if exists mymopsi_img, mymopsi_collection, mymopsi_user, mymopsi_user_third_party_link";
$db->query( $sql );

$db->query( "SET FOREIGN_KEY_CHECKS=1" );

echo "<a href='install.php'>Link to install</a>";
