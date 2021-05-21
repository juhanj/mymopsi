<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/**
 * @var DBConnection $db
 * @var Language $lang
 * @var User $user
 */

/*
 * GET variables:
 */
$thumbnail = isset($_GET['thumb']);
// Single image GET request
$image_ruid = $_GET['id'] ?? null; // RUID
// collection image GET request
$collection_ruid = $_GET['collection'] ?? null; // RUID

// Collection representative image
// Random, first added, last added
//TODO: currently only random, add other options --jj 21-05-16
if ( $collection_ruid ) {
	$collection = Collection::fetchCollectionByRUID( $db, $collection_ruid );

	/** @var Image $image */
	$image = $db->query(
		'select id, random_uid, mediatype, filepath, thumbpath, size from mymopsi_img where collection_id = ? order by rand() limit 1',
		[ $collection->id ],
		false,
		'Image'
	);
}

// Singe individual image
else {
	/** @var Image $image */
	$image = $db->query(
		'select id, random_uid, mediatype, filepath, thumbpath, size from mymopsi_img where random_uid = ? limit 1',
		[ $image_ruid ],
		false,
		'Image'
	);
}

if ( !$image or !file_exists( $image->filepath ) ) {
	//	header( 'HTTP/1.1 404 Not Found' );
	http_response_code(404);
	exit();
}

if ( $image->thumbpath === null ) {
	$controller = new ImageController();
	$controller->requestCreateThumbnail( $db, [ 'image' => $image->random_uid ] );

	$image = $db->query(
		'select id, random_uid, mediatype, filepath, thumbpath, size from mymopsi_img where id = ? limit 1',
		[ $image->id ],
		false,
		'Image'
	);
}

// This is never sent from browser, with PPH-script images, so cache no work.
// the browser will send a $_SERVER['HTTP_IF_MODIFIED_SINCE'] if it has a cached copy
if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
	// if the browser has a cached version of this image, send 304
	http_response_code(304);
	//header( 'Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304 );
	exit();
}

$responseFilePath = ($thumbnail)
	? $image->thumbpath
	: $image->filepath;

if ( $responseFilePath === 'no_thumbnail' ) {
	$responseFilePath = $image->filepath;
}

header( 'Content-Type: ' . $image->mediatype );
header( 'Content-Length: ' . $image->size );
// Cache valid 7 days
header( "Cache-Control: public,max-age=604800,immutable", true );

readfile( $responseFilePath );
