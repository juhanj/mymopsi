<?php
declare(strict_types=1);
ini_set( 'memory_limit', '-1' );
error_reporting( 0 );

//set encoding to unicode
mb_internal_encoding( "UTF-8" );

require './clusteringHandler.class.php';

// Get request information from either GET, or POST,
//  or lastly read directly request PHP received, in case it was JSON directly
$request = $_GET
	?: $_POST
		?: json_decode( file_get_contents( 'php://input' ), true );

// If request was empty, send back error
if ( empty( $request ) ) {
	header( '400 Bad Request', true, 400 );
	exit;
}

// Handling request
$clusteringHandler = ClusteringHandler::builder( $request );

switch ( $clusteringHandler->type ?? null ) {
	case 'spatial':
		$result = $clusteringHandler->spatialQuery();
		break;
	case 'nonSpatial':
		$result = $clusteringHandler->nonSpatialQuery();
		break;
	case 'dataBounds':
		$result = $clusteringHandler->dataBounds();
		break;
	case 'photoInfoBoundingBox':
		$result = $clusteringHandler->photoInfoByBoundingBox();
		break;
	default:
		$result = false;
}

$response = [
	'request' => $request,
	'response' => $result
];

// Some HTTP headers for the response
// Mostly security related, but last one sets content-type to JSON
header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
header( 'Access-Control-Allow-Methods: GET, POST' );
header( "Access-Control-Allow-Headers: X-Requested-With" );
header( "Access-Control-Allow-Credentials: true" );
header( 'Content-Type: application/json' );

// Return result in JSON format back to client.
echo json_encode(
	$response,
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
);
