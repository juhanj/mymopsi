<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

/*
 * Cleaning collections directory
 */

$folders = scandir( INI['Misc']['path_to_collections'] );

Utils::debug( $folders );

foreach ( $folders as $folder ) {
	if ( $folder=='.' or $folder=='..' ) {
		continue;
	}

	$imgs = scandir( INI['Misc']['path_to_collections'] . '/' . $folder );

	Utils::debug( $imgs );

	foreach ( $imgs as $img ) {
		if ( $img=='.' or $img=='..' ) continue;

		unlink( INI['Misc']['path_to_collections'] . '/' . $folder . '/' . $img );
	}

	Utils::debug( rmdir( INI['Misc']['path_to_collections'] . '/' . $folder ) );
}

/*
 * Dropping tables, and recreating them with testdata
 */

$sql = "drop table if exists mymopsi_img, mymopsi_collection, mymopsi_user, mymopsi_user_third_party_link";
$db->query(
	$sql
);

echo "<a href='install.php'>Link to install</a>";
