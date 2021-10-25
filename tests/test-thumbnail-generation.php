<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/

$testImagePath = "./test-image.svg";
// PHP needs R/W rights to thumbnail location.
// Test thumbnail will be deleted at the end.
$testThumbPath = INI[ 'Misc' ][ 'path_to_collections' ] . "test-thumb.jpg";

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

exec( $command, $output, $returnCode );

debug( $command );
debug( $output );
debug( $returnCode );

debug(
	file_exists( $testThumbPath ), true
);

Common::deleteFiles( $testThumbPath );

debug(
	file_exists( $testThumbPath ), true
);

