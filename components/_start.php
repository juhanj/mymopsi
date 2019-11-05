<?php declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', "1");

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
define(
	'CURRENT_PAGE',
	basename( $_SERVER[ 'SCRIPT_NAME' ] , '.php' )
);

/*
 * Automatic class loading
 * Set folders for all possible folders where includes/requires might happen.
 */
set_include_path(
	get_include_path() . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/class/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/components/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/cfg/' . PATH_SEPARATOR
	. DOC_ROOT . WEB_PATH . '/json/' . PATH_SEPARATOR );
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

require 'helper-functions.php';

session_start();

/*
 * Creating necessary objects
 */
$db = new DBConnection();
$lang = Language::getLanguageStrings($_COOKIE['lang'] ?? 'en', CURRENT_PAGE);

$user = !empty($_SESSION['user_id'])
	? User::fetchUserByID( $db, $_SESSION['user_id'] )
	: null;
