<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/

$imagePath = "./img/test-actual-image.jpg";
$newThumbPath = INI[ 'Misc' ][ 'path_to_collections' ] . "test-thumb.jpg";

$command = INI[ 'Misc' ][ 'imagemagick' ]
	. " $imagePath " // Original image
	. " -thumbnail 128x128 " // Strip metadata, and size of thumbnail
	. " -sharpen 0x.5 " // Sharpen image a bit, comes out blurry otherwise
	. " -gravity center " // Center image for following option
	. " -extent 128x128 " // Make image square
	//TODO: transparent background (failed multiple tries) --jj 21-05-16
	. $newThumbPath; // New thumbnail path

exec( $command, $output, $returnCode );

debug( $command );
debug( $returnCode );

debug(
	file_exists( $newThumbPath ), true
);

Common::deleteFiles( $newThumbPath );

debug(
	file_exists( $newThumbPath ), true
);