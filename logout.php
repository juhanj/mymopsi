<?php
declare(strict_types=1);

// Start old session
session_start();

// Unset variables saved in old session
$_SESSION = [];

// Destroy old session
session_destroy();

setcookie( 'username', '', 1 );
header( "location: index.php?loggedout" );
exit();