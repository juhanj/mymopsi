<?php
declare(strict_types=1);

/*
 * Local directories (const for global use)
 */
const XAMPP_DIR = "D:/xampp/";
const DOC_ROOT = XAMPP_DIR . "htdocs/";
const WEB_PATH = 'mopsi_dev/mymopsi/';

const COLL_DIR = "C:/Users/Jq/Documents/mymopsi-collections/";
const UNIT_COLL_DIR = COLL_DIR . "unit/collections/";

const MOPSI_PHOTOS = DOC_ROOT . WEB_PATH . "tests/img-dataset/test-mopsi-photos/";
const REAL_IMAGE_FILE = DOC_ROOT . WEB_PATH . "tests/test-image.svg";

/*
 * PHP classes used for testing
 */
require DOC_ROOT . WEB_PATH . 'class/common.class.php';
require DOC_ROOT . WEB_PATH . 'class/dbconnection.class.php';

require DOC_ROOT . WEB_PATH . 'class/user.class.php';
require DOC_ROOT . WEB_PATH . 'class/collection.class.php';
require DOC_ROOT . WEB_PATH . 'class/image.class.php';

require DOC_ROOT . WEB_PATH . 'class/controller.class.php';

require DOC_ROOT . WEB_PATH . 'class/collectioncontroller.class.php';
require DOC_ROOT . WEB_PATH . 'class/usercontroller.class.php';
require DOC_ROOT . WEB_PATH . 'class/imagecontroller.class.php';

function set_up_database () {
	$db = new DBConnection();

	/*
	 * unit users
	 * 1:'Admin' and 2:'unit'
	 */
	$sql = 'insert into mymopsi_user (id, random_uid, username, password, type, email, admin) 
			values (?,?,?,?,?,?,?), (?,?,?,?,?,?,?)
			on duplicate key update username = values(username), password = values(password), email = values(email)';
	$values = [
		1, 'unitest-user1-ruid', 'admin', password_hash( 'admin', PASSWORD_DEFAULT ), 2, 'admin@admin', true,
		2, 'unitest-user2-ruid', 'user', password_hash( 'user', PASSWORD_DEFAULT ), 2, 'user@user', false,
	];
	$db->query( $sql, $values );

	/*
	 * unit collections
	 * 1:'test' (user:1) and 2:null (user:2)
	 */
	$sql = 'insert into mymopsi_collection (id, owner_id, random_uid, name, description, public)
			values (?,?,?,?,?,?),(?,?,?,?,?,?)
			on duplicate key update owner_id = values(owner_id), name = values(name), description = values(description), 
			                        public=values(public), editable=false, date_added = now()';
	$values = [
		1, 1, 'unitest-collec1-ruid', 'test1', null, false,
		2, 1, 'unitest-collec2-ruid', 'test2', null, true,
	];
	$db->query( $sql, $values );
	// Supress warning, because file already exists, don't want to write if
	@mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . "unitest-collec1-ruid/", 0777, true );
	@mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . "unitest-collec2-ruid/" );

	/*
	 * unit image
	 * 1 (all ones, collection:1)
	 */
	$sql = 'insert into mymopsi_img (id, collection_id, random_uid, hash, name, original_name, mediatype, size, latitude, longitude, filepath)
				values (?,?,?,?,?,?,?,?,?,?,?), (?,?,?,?,?,?,?,?,?,?,?), (?,?,?,?,?,?,?,?,?,?,?)
				on duplicate key update name=values(name)';
	$filename1 = INI[ 'Misc' ][ 'path_to_collections' ] . "unitest-collec1-ruid/unitest-image1-ruid";
	$filename2 = INI[ 'Misc' ][ 'path_to_collections' ] . "unitest-collec1-ruid/unitest-image2-ruid";
	$filename3 = REAL_IMAGE_FILE;
	$values = [
		1, 1, 'unitest-image1-ruid', '1', '1', '1', '1', '1', '1', '1', $filename1,
		2, 1, 'unitest-image2-ruid', '2', '2', '2', '2', '2', '2', '2', $filename2,
		3, 2, 'unitest-image3-ruid', '3', '3', '3', '3', '3', '3', '3', $filename3,
	];
	$db->query( $sql, $values );
	// Supress warning, because file already exists, don't want to write if
	@file_put_contents( $filename1, "empty" );
	@file_put_contents( $filename2, "empty" );

	// Suppress warning because file already exists
	@mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . 'temp/' );
}

function empty_database_and_test_collections () {
	$db = new DBConnection();

	$sql = "SET FOREIGN_KEY_CHECKS=0";
	$db->query( $sql );

	$sql = 'TRUNCATE TABLE mymopsi_img';
	$db->query( $sql );
	$sql = 'TRUNCATE TABLE mymopsi_collection';
	$db->query( $sql );
	$sql = 'TRUNCATE TABLE mymopsi_user_third_party_link';
	$db->query( $sql );
	$sql = 'TRUNCATE TABLE mymopsi_user';
	$db->query( $sql );

	$sql = "SET FOREIGN_KEY_CHECKS=1";
	$db->query( $sql );

	Common::deleteFiles( UNIT_COLL_DIR );
	mkdir( UNIT_COLL_DIR );
}

$database_configs = [
	"host" => "localhost",
	"name" => "mymopsi_unittest",
	"user" => 'root',
	"pass" => '',
];
$settings = [
	"username_min_len" => 1,
	"username_max_len" => 50,
	"password_min_len" => 8,
	"password_max_len" => 300,
	"coll_name_max_len" => 50,
	"coll_descr_max_len" => 300,
];
$misc = [
	'perl' => XAMPP_DIR . "perl/bin/perl.exe",
	'imagemagick' => "magick convert",
	'path_to_collections' => UNIT_COLL_DIR,
	'path_to_mopsi_photos' => MOPSI_PHOTOS,
];

define(
	'INI',
	[
		"Database" => $database_configs,
		"Settings" => $settings,
		'Misc' => $misc,
	]
);
