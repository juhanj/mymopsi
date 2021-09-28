<?php declare(strict_types=1);
require '../components/_start.php';
/*/////////////////////////////////////////////////*/

$file = "C:\Users\Jq\Pictures\Joensuu/IMG_20210606_235347.jpg";

$result = Common::runExiftool( $file );

debug( $result );