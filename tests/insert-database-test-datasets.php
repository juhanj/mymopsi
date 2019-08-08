<?php declare(strict_types=1);
require	$_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

function randomFloat($min = 0, $max = 1) {
	return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

$collections = [
	[
		bin2hex( random_bytes( 10 ) ),
		"test - different formats",
		"2-different-formats",
	],
	[
		bin2hex( random_bytes( 10 ) ),
		"test - different resolutions",
		"3-different-resolutions",
	],
	[
		bin2hex( random_bytes( 10 ) ),
		"test - many images",
		"5-many-images",
	],
];

$max_lat = 70;
$min_lat = 60;

$max_lng = 30;
$min_lng = 20;

$sql_coll = "insert ignore into mymopsi_collection ( random_uid, name, public )
			values ( ?, ?, true )";

foreach ( $collections as $coll ) {
	$db->query( $sql_coll , [ $coll[0], $coll[1] ] );

	$coll_id = $db->getConnection()->lastInsertId();

	echo "<h1>{$coll_id} - {$coll}</h2>";

	$dataset = scandir( DOC_ROOT . WEB_PATH . '/tests/img/' . $coll[2] );

	$sql_img = "insert ignore into mymopsi_img (collection_id, random_uid, hash, name, filepath, mediatype, size, latitude, longitude)
				values (?,?,?,?,?,?,?,?,?)";

	foreach ( $dataset as $img ) {
		if ( $img=='.' or $img=='..' ) { continue; }

		$filepath = DOC_ROOT . WEB_PATH . '/tests/img/' . $coll[2] . '/' . $img;
		echo ($filepath . '<br>');

		$db->query(
			$sql_img,
			[
				$coll_id,
				bin2hex( random_bytes( 10 ) ),
				str_shuffle(sha1_file($filepath )),
				basename($img),
				$filepath,
				finfo_file( finfo_open( FILEINFO_MIME_TYPE ), $filepath ),
				filesize( $filepath ),
				randomFloat( $min_lat, $max_lat ),
				randomFloat( $min_lng, $max_lng )
			]
		);

		echo $db->getConnection()->lastInsertId() . '<p>';
	}
}
