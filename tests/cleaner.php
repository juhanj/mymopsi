<?php declare(strict_types=1);
require	$_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

$folders = scandir( INI['Misc']['path_to_collections'] );

debug( $folders );

foreach ( $folders as $folder ) {
	if ( $folder=='.' or $folder=='..' ) continue;

	$imgs = scandir( INI['Misc']['path_to_collections'] . '/' . $folder );

	foreach ( $imgs as $img ) {
		if ( $folder=='.' or $folder=='..' ) continue;

		unlink( INI['Misc']['path_to_collections'] . '/' . $folder . '/' . $img );
	}

	debug( rmdir( INI['Misc']['path_to_collections'] . '/' . $folder ) );
}
