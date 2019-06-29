<?php //declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', "1");
function debug($var,$var_dump=false){
	echo"<br><pre>Print_r ::<br>";print_r($var);echo"</pre>";
	if($var_dump){echo"<br><pre>Var_dump ::<br>";var_dump($var);echo"</pre><br>";};
}


$ini = parse_ini_file( "../cfg/config.ini", true )['Testing'];

$perl = $ini['perl'];
$exift = $ini['exiftool'];
$command = "-a -gps:all -c %.6f"; // -v5
$target = $ini['testimg'];

$output = null;

exec(
	"{$perl} {$exift} {$command} {$target}",
	$output
);
debug($output);


// Timing / Benchmarking
// 600 images = >10 seconds
/*$time_pre = microtime(true);
exec(
	""{$perl} {$exift} -csv ./collections/Sony",
	$output
);
$time_post = microtime(true);

$exec_time = $time_post - $time_pre;
debug( $exec_time );

debug($output);*/
