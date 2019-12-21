<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';

$image_ruuid = $_GET['id'];
$image_thumbnail = $_GET['thumb'] ?? false;

/**
 * @var Image $image
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

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	header('HTTP/1.1 304 Not Modified');
	die();
}

header( 'Content-Type: ' . $image->mediatype );
header("Expires: Mon, 1 Jan 2099 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

readfile( $image->filepath );
