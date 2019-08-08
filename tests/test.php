<?php declare(strict_types=1);
require	$_SERVER['DOCUMENT_ROOT'] . '/mopsi_dev/mymopsi/components/_start.php';
/*/////////////////////////////////////////////////*/


$folders = scandir( DOC_ROOT . WEB_PATH . '/tests/img/1-normal-working-default-set' );

debug( $folders );
