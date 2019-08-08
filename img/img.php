<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

$path = INI['Misc']['path_to_collections'];
$image_ruuid = $_GET['id'];

/**
 * @var \Image $image
 */
$image = $db->query(
	'select id, collection_id, mediatype, filepath from mymopsi_img where random_uid = ? limit 1',
	[ $image_ruuid ],
	false,
	'Image'
);

if ( !$image or !file_exists( $image->filepath ) ) {
	header('HTTP/1.0 404 Not Found');
	header( "location: {$_SERVER['HTTP_REFERER']}");
	exit();
}

header( 'Content-Type: ' . $image->mediatype );
readfile( $image->filepath );
