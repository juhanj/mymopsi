<?php declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', "1");
function debug($var,bool$var_dump=false){
	echo"<br><pre>Print_r ::<br>";print_r($var);echo"</pre>";
	if($var_dump){echo"<br><pre>Var_dump ::<br>";var_dump($var);echo"</pre><br>";};
}
/////////////////////////////////////////////////
