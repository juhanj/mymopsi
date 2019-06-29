<?php declare(strict_types=1);
require	$_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/
set_time_limit( 120 );

$algos = hash_algos();

$root = './img/';

$img_datasets = scandir( $root );

foreach ( $img_datasets as $dataset ) {
	if ( $dataset == '.' or $dataset == '..' ) { continue; }
	$test_datasets[$dataset] = scandir( $root . $dataset );
}

echo '<pre>';
echo "Number of files: " . count( $test_datasets, 1 ) . "\n";

foreach ( $algos as $algo ) {

	// MD2 is horribly slow. Like, more than 30 times slower. Is that an order of magnitude?
	if ( $algo == 'md2' ) continue;

	$time_start = microtime(true);
	foreach ( $test_datasets as $key => $dataset ) {
		foreach ( $dataset as $img ) {
			if ( is_dir($img) ) continue;
			//hash_file( $algo, "$root/$key/$img" );
		}
	}
	$time_end = microtime(true);
	$time = round($time_end - $time_start,2);
	$algo = str_pad( $algo, 12, '-' );
	echo "{$algo}: {$time} seconds\n";
}
