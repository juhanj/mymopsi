<?php declare(strict_types=1);
require '../components/_start.php';
/**
 * @var DBConnection $db
 * @var Language     $lang
 * @var User         $user
 */

/*
 * GET variables:
 */
$thumbnail = isset( $_GET[ 'thumb' ] );
// Single image
$image_ruid = $_GET[ 'id' ] ?? null; // RUID
// collection image (representative, thumbnail probably)
$collection_ruid = $_GET[ 'collection' ] ?? null; // RUID

if ( $collection_ruid ) {
	$image = Image::fetchCollectionRepresentativeImage( $db, $collection_ruid );
}
else {
	$image = Image::fetchImageByRUID( $db, $image_ruid );
}

if ( !$image or !file_exists( $image->filepath ) ) {
	// 404 file not found
	http_response_code( 404 );
	exit();
}

/*
 * If Image doesn't have a thumbnail created, create new one, and fetch from DB again
 */
if ( $image->thumbnailpath === null ) {
	$controller = new ImageController();
	$controller->requestCreateThumbnail( $db, [ 'image' => $image->random_uid ] );

	$image->thumbnailpath = $controller->result['thumbnailpath'];
}

// the browser will send a $_SERVER['HTTP_IF_MODIFIED_SINCE'] if it has a cached copy
//TODO: This is never sent from browser, with PPH-script images, so cache no work. --jj 22-06-16
if ( isset( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) ) {
	// if the browser has a cached version of this image, send 304
	http_response_code( 304 );
	//header( 'Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304 );
	exit();
}

$responseFilePath = ($thumbnail)
	? $image->thumbnailpath
	: $image->filepath;

// In some cases thumbnail creation fails
// Can't check for NULL because that means system hasn't tried to create one.
if ( $responseFilePath === 'no_thumbnail' ) {
	$responseFilePath = $image->filepath;
}

// All thumbnails are .JPEG format
$contentType = ($thumbnail) ? 'image/jpeg' : $image->mediatype;
// Have to read the filesize of the thumbnail, because the browser will actually
//  wait for those missing bytes otherwise, which shows as slow loading client-side
$contentLength = ($thumbnail) ? filesize($responseFilePath) : $image->size;

header( 'Content-Type: ' . $contentType );
header( 'Content-Length: ' . $contentLength );
// Cache valid 7 days
//TODO: Doesn't work currently --jj 22-06-16
header( "Cache-Control: public,max-age=604800,immutable", true );

readfile( $responseFilePath );
