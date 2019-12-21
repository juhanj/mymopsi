<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

$temp_folder = 'C:\xampp\htdocs\mopsi_dev\mymopsi\tests\img\1-normal-working-default-set';

// run exiftool on that folder
$commandOptions =
	" -g3" // I don't know, but it's important! Related to getting GPS fields
	. " -a" // Allow duplicates (needed for gps coordinates)
	. " -filename"
	. " -gps:all" // All GPS metadata
	. " -Datecreate"
	. " -ImageSize"
	. " -c %.6f" // format for gps coordinates output
;

$metadata = Utils::runExiftool( $temp_folder, $commandOptions );

Utils::debug( $metadata );
