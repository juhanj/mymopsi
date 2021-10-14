<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/

function randomFloat ( $min = 0, $max = 1 ) {
	return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

$collections = [
	[
		bin2hex( random_bytes( 10 ) ), // random_uid for database
		"test - different formats", // name for database
		"2-different-formats", // directory name for images
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

// Approx. the bounding box area of Finland
$latitude_range = [ 60, 70 ];
$longitude_range = [ 20, 30 ];

$sql_coll = "insert ignore into mymopsi_collection ( random_uid, name, public )
			values ( ?, ?, true )";
$sql_img = "insert ignore into mymopsi_img (collection_id, random_uid, hash, name, filepath, mediatype, size, latitude, longitude)
				values (?,?,?,?,?,?,?,?,?)";

/*
 * Inserting collections and images
 */
foreach ( $collections as $coll ) {
	// Collection to database
	$db->query( $sql_coll, [ $coll[0], $coll[1] ] );

	$coll_id = $db->getConnection()->lastInsertId();

	if ( !$coll_id ) {
		echo "<p>Insert failed for " . $coll[1];
		debug( $coll );
		continue;
	}

	echo "<h1>{$coll_id} - {$coll[1]}</h1>";

	$dataset = scandir( DOC_ROOT . WEB_PATH . 'tests/img/' . $coll[2] );

	foreach ( $dataset as $img ) {
		if ( $img == '.' or $img == '..' ) {
			continue;
		}

		$filepath = DOC_ROOT . WEB_PATH . 'tests/img/' . $coll[2] . '/' . $img;
		echo($filepath . '<br>');

		$db->query(
			$sql_img,
			[
				$coll_id,
				bin2hex( random_bytes( 10 ) ),
				str_shuffle( sha1_file( $filepath ) ),
				basename( $img ),
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
