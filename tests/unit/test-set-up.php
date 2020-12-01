<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';

require $home_directory . 'class/common.class.php';
require $home_directory . 'class/dbconnection.class.php';

require $home_directory . 'class/user.class.php';
require $home_directory . 'class/collection.class.php';
require $home_directory . 'class/image.class.php';

require $home_directory . 'class/controller.class.php';

require $home_directory . 'class/collectioncontroller.class.php';
require $home_directory . 'class/usercontroller.class.php';
require $home_directory . 'class/imagecontroller.class.php';

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
		1, 'unitest-user1-ruid', 'admin', password_hash( 'admin', PASSWORD_DEFAULT ), 2, 'admin@admin',true,
		2, 'unitest-user2-ruid', 'user', password_hash( 'user', PASSWORD_DEFAULT ), 2, 'user@user',false,
	];
	$db->query( $sql, $values );

	/*
	 * unit collections
	 * 1:'test' (user:1) and 2:null (user:2)
	 */
	$sql = 'insert into mymopsi_collection (id, owner_id, random_uid, name, description)
			values (?,?,?,?,?),(?,?,?,?,?)
			on duplicate key update owner_id = values(owner_id), name = values(name), description = values(description), 
			                        public=false, editable=false, date_added = now()';
	$values = [
		1, 1, 'unitest-collec1-ruid', 'test1', null,
		2, 1, 'unitest-collec2-ruid', 'test2', null,
	];
	$db->query( $sql, $values );
	@mkdir( INI['Misc']['path_to_collections'] . "/unitest-collec1-ruid/" );
	@mkdir( INI['Misc']['path_to_collections'] . "/unitest-collec2-ruid/" );

	/*
	 * unit image
	 * 1 (all ones, collection:1)
	 */
	$sql = 'insert into mymopsi_img (id, collection_id, random_uid, hash, name, original_name, mediatype, size, latitude, longitude, filepath)
				values (?,?,?,?,?,?,?,?,?,?,?), (?,?,?,?,?,?,?,?,?,?,?), (?,?,?,?,?,?,?,?,?,?,?)
				on duplicate key update name=values(name)';
	$filename1 = INI['Misc']['path_to_collections'] . "/unitest-collec1-ruid/unitest-image1-ruid";
	$filename2 = INI['Misc']['path_to_collections'] . "/unitest-collec1-ruid/unitest-image2-ruid";
	$filename3 = INI['Misc']['path_to_collections'] . "/unitest-collec2-ruid/unitest-image3-ruid";
	$values = [
		1, 1, 'unitest-image1-ruid', '1', '1', '1', '1', '1','1','1',$filename1,
		2, 1, 'unitest-image2-ruid', '2', '2', '2', '2', '2','2','2',$filename2,
		3, 2, 'unitest-image3-ruid', '3', '3', '3', '3', '3','3','3',$filename3,
	];
	$db->query( $sql, $values );
	@file_put_contents( $filename1, "empty" );
	@file_put_contents( $filename2, "empty" );
	@file_put_contents( $filename3, "empty" );
}

function empty_database () {
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

	shell_exec( 'rmdir /q /s D:\juhanj\Documents\mymopsi\unit\collections' );
	mkdir("D:\juhanj\Documents\mymopsi\unit\collections");
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
	'perl' => "C:/xampp/perl/bin/perl.exe",
	'path_to_collections' => "D:\juhanj\Documents\mymopsi\unit\collections",
	'path_to_mopsi_photos' => "D:\juhanj\Documents\mymopsi\unit\mopsi_photos",
];

define(
	'INI',
	[
		"Database" => $database_configs,
		"Settings" => $settings,
		'Misc' => $misc,
	]
);

define(
	'DOC_ROOT',
	'C:\xampp\htdocs'
);
define(
	'WEB_PATH',
	'/mopsi_dev/mymopsi/'
);