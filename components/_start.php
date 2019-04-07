<?php declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', "1");

/**
 * @param mixed $var
 * @param bool  $var_dump
 */
function debug($var,bool$var_dump=false){
	echo"<br><pre>Print_r ::<br>";print_r($var);echo"</pre>";
	if($var_dump){echo"<br><pre>Var_dump ::<br>";var_dump($var);echo"</pre><br>";};
}

/**
 * Prints formatted number: 1.000[,00]
 * @param mixed $number
 * @param int   $dec_count  [optional] default=2 <p> Number of decimals.
 * @return string
 */
function fNumber( $number, int $dec_count = 2 ) : string {
	return number_format( (float)$number, $dec_count, ',', '.' );
}

/**
 * Check feedback variable, and prevent resending form on page refresh or back button.
 * @return string $feedback
 */
function check_feedback_POST() {
	// Stop form resending
	if ( !empty($_POST) or !empty($_FILES) ){
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit();
	}

	// Check the feedback from Session data
	$feedback = isset($_SESSION["feedback"]) ? $_SESSION["feedback"] : "";
	unset($_SESSION["feedback"]);
	return $feedback;
}

/**
 * For easier access. This way any includes/requires and such can be written shorter,
 * and not be dependant on location.
 */
define(
	'DOC_ROOT',
	$_SERVER['DOCUMENT_ROOT']
);
define(
	'WEB_PATH',
	'/mopsi_dev/mymopsi/'
);

/*
 * Automatic class loading
 * Set folders for all possible folders where includes/requires might happen.
 */
set_include_path(
	get_include_path() . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/class/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/components/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/cfg/' . PATH_SEPARATOR );
spl_autoload_extensions( '.class.php' );
spl_autoload_register();

/**
 * Loading a ini-file. Probably not a bottleneck doing this on every pageload,
 * but it is easier than doing when needed. For example, what happens if ini-file location/name changes?
 * Double loading ini-files, because actual important info outside webroot.
 * //TODO: INI_SCANNER_TYPED untested. See how it works. --jj190328
 */
/**
 * Named constant for INI-settings.
 * <code>
 * Array(
 *  ['Database'],
 *  ['Admin'],
 *  ['Misc'],
 *  ['Testing']
 * )
 * </code>
 */
define(
	'INI' ,
	parse_ini_file(
		(parse_ini_file( 'config.ini.php' )[ 'config' ]),
		true ,
		INI_SCANNER_TYPED
	)
);

session_start();

/*
 * Creating necessary objects
 */
$db = new DBConnection();
