<?php declare(strict_types=1);
require $_SERVER[ 'DOCUMENT_ROOT' ] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

$json = json_decode( file_get_contents( './temp/words-outokumpu.txt' ) );
$random_string = substr( str_shuffle( '123456789QWERTYUIOPASDFGHJKLZXCVBNM' ), 0, 3 );

//debug( $random_string );

$temp = [];
foreach ( $json as $key => $number ) {
	$random_string = substr( str_shuffle( '123456789QWERTYUIOPASDFGHJKLZXCVBNM' ), 0, 3 );
	$temp[ $random_string ] = $number;
}

echo json_encode($temp);

//debug( $json );