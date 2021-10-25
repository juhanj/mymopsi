<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/


$command = INI[ 'Misc' ][ 'imagemagick' ]
	. " -version";

exec( $command, $output, $returnCode );

debug( $command );
debug( $output );
debug( $returnCode );