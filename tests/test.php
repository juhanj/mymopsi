<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/


$result = Common::getNominatimReverseGeocoding( 62.5913800, 29.7796980 );

debug( $result );