<?php declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/

$_SESSION['feedback'] .= "me fine";
debug(
	$_SESSION,
	true
);
