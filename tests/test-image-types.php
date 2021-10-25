<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/

$imagesDir = "./img/10-wiesmann-image-format-test-page/";

$files = scandir( $imagesDir );

$files = array_diff(
	scandir( $imagesDir ),
	[ '.', '..' ]
);

debug( $files );

echo "<hr>";

foreach ( $files as $file ) {
	$imgPath = $imagesDir . $file;
	echo "<p>$file</p>";
	echo "<img src='$imgPath' style='height: 5rem'>";

	echo "<hr>";
}