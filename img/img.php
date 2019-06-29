<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

$path = INI['Misc']['path_to_collections'];
$image_ruuid = $_GET['img'];

/**
 * @var \Image $image
 */
$image = $db->query(
	'select id, collection_id, mediatype from mymopsi_img where random_uid = ? limit 1',
	[ $image_ruuid ],
	false,
	'Image'
);

$filepath = $path . '/' . $image->collection_id . '/' . $image->id;

if ( !$image or !file_exists( $filepath ) ) {
	header('HTTP/1.0 404 Not Found');
	exit();
}

header( 'Content-Type: ' . $image->mediatype );
readfile( $filepath );
