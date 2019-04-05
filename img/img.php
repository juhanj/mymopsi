<?php declare( strict_types=1 );

require '../components/_start.php';

$path = INI['Misc']['path_to_collections'];
$collection = $_GET['cid'];
$image = sprintf('%04d', $_GET[ 'iid' ] );


$filename = $path . '/' . $collection . '/' . $image . '.png';

header( 'Content-Type: image/png' );
readfile( $filename );
