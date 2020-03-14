<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

echo "{${rand(100,999)}}";

Utils::debug(
	"{${rand(100,999)}}"
);
