<?php declare(strict_types=1);
require '../components/_start.php';
/** @var DBConnection $db */
/*/////////////////////////////////////////////////*/


$are_there_any_public_colls = $db->query(
	"select count(id) as count from mymopsi_collection where public = true",
	[],
	false
);
debug( $are_there_any_public_colls );