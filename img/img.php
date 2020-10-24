<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

$image_ruuid = $_GET['id']; // Random unique identifier from the database
$image_thumbnail = $_GET['thumb'] ?? false; // Any value, even empty, is OK

/**
 * @var Image $image
 */
$image = $db->query(
	'select id, collection_id, mediatype, filepath, size from mymopsi_img where random_uid = ? limit 1',
	[ $image_ruuid ],
	false,
	'Image'
);

if ( !$image or !file_exists( $image->filepath ) ) {
	header( 'HTTP/1.0 404 Not Found' );
	header( "location: {$_SERVER['HTTP_REFERER']}" );
	exit();
}

// the browser will send a $_SERVER['HTTP_IF_MODIFIED_SINCE'] if it has a cached copy
if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
	// if the browser has a cached version of this image, send 304
	header( 'Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304 );
	exit();
}

header( 'Content-Type: ' . $image->mediatype );
header( 'Content-Length: ' . $image->size );
header( "Cache-Control: private, max-age=31536000", true );

readfile( $image->filepath );
