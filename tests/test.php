<?php declare(strict_types=1);
require '../components/_start.php';
/** @var DBConnection $db */
/*/////////////////////////////////////////////////*/


$testImagePath = "./Joensuu/";
$testThumbPath = "./thumbs/*.JPG";

echo "<p><pre>ImageMagick test | Thumbnail generation</pre>";
echo "<p><pre>{$testImagePath}</pre>";
echo "<p><pre>{$testThumbPath}</pre>";

$command = INI[ 'Misc' ][ 'imagemagick' ]
	. " $testImagePath " // Original image
	. " -thumbnail 128x128 " // Strip metadata, and size of thumbnail
	. " -sharpen 0x.5 " // Sharpen image a bit, comes out blurry otherwise
	. " -gravity center " // Center image for following option
	. " -extent 128x128 " // Make image square
	//TODO: transparent background (failed multiple tries) --jj 21-05-16
	. $testThumbPath; // New thumbnail path

debug( $command );
//exec( $command, $output, $returnCode );
