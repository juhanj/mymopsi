<?php declare(strict_types=1);
error_reporting( E_ALL );
ini_set( 'display_errors', "1" );

mb_internal_encoding( "UTF-8" );

/**
 * Used for printing out variables in human readable format
 * @param      $var
 * @param bool $var_dump
 */
function debug ( $var, bool $var_dump = false ) {
	echo "<br><pre>Print_r ::<br>";
	print_r( $var );
	echo "</pre>";
	if ( $var_dump ) {
		echo "<br><pre>Var_dump ::<br>";
		var_dump( $var );
		echo "</pre><br>";
	}
}

/**
 * For easier access. This way any includes/requires and such can be written shorter,
 * and not be dependent on location.
 * As a general rule, no leading slash added ever. Only ending slash for directories.
 */
define(
	'DOC_ROOT',
	$_SERVER['DOCUMENT_ROOT'] . '/' // DOCUMENT_ROOT doesn't have trailing slash
);
define(
	'CURRENT_PAGE',
	basename( $_SERVER['SCRIPT_NAME'], '.php' )
);
const FILE_PATH = 'mopsi_dev/mymopsi/';
// web-path needs a leading slash to indicate root for links, src, and such.
// Any paths used in HTML, basically. Could add it above, but brakes stuff in
//  Windows testing, and also sometimes adds double slashes.
const WEB_PATH = '/mopsi_dev/mymopsi/';

/*
 * Automatic class loading
 * Set folders for all possible folders where includes/requires might happen.
 */
set_include_path(
	get_include_path() . PATH_SEPARATOR
	. DOC_ROOT . FILE_PATH . 'class/' . PATH_SEPARATOR
	. DOC_ROOT . FILE_PATH . 'components/' . PATH_SEPARATOR
	. DOC_ROOT . FILE_PATH . 'cfg/' . PATH_SEPARATOR
	. DOC_ROOT . FILE_PATH . 'json/' . PATH_SEPARATOR
	. DOC_ROOT . FILE_PATH . PATH_SEPARATOR );
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
	'INI',
	parse_ini_file(
		(parse_ini_file( 'config.ini.php' )['config']),
		true,
		INI_SCANNER_TYPED
	)
);

session_start();

/*
 * Creating necessary objects
 */
$db = new DBConnection();
$lang = Language::getLanguageStrings( $_COOKIE['mymopsi_lang'] ?? 'en' );

$user = !empty( $_SESSION['user_id'] )
	? User::fetchUserByID( $db, $_SESSION['user_id'] )
	: null;

$breadcrumbs_navigation = [
];