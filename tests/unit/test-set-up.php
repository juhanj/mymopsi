<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';

require $home_directory . 'class/utils.class.php';
require $home_directory . 'class/dbconnection.class.php';

require $home_directory . 'class/user.class.php';
require $home_directory . 'class/collection.class.php';
require $home_directory . 'class/image.class.php';

require $home_directory . 'class/controller.class.php';

require $home_directory . 'class/collectioncontroller.class.php';
require $home_directory . 'class/usercontroller.class.php';

function set_up_database () {
	$db = new DBConnection();

	/*
	 * Test users
	 * 1:'Admin' and 2:'Test'
	 */
	$sql = 'insert into mymopsi_user (id, random_uid, username, password, type, email, admin) 
			values (?,?,?,?,?,?,?), (?,?,?,?,?,?,?)
			on duplicate key update username = values(username), password = values(password), email = values(email)';
	$values = [
		1, Utils::createRandomUID( $db, 20, false ), 'admin', password_hash( 'password', PASSWORD_DEFAULT ), 2, 'admin@admin',true,
		2, Utils::createRandomUID( $db, 20, false ), 'test', password_hash( '12345678', PASSWORD_DEFAULT ), 2, 'test@test',false,
	];
	$db->query( $sql, $values );

	/*
	 * Test collections
	 * 1:'test' (user:1) and 2:null (user:2)
	 */
	$sql = 'insert into mymopsi_collection (id, owner_id, random_uid, name, description)
			values (?,?,?,?,?),(?,?,?,?,?)
			on duplicate key update owner_id = values(owner_id), name = values(name), description = values(description), 
			                        public=false, editable=false, date_added = now(), last_edited = now()';
	$values = [
		1, 1, Utils::createRandomUID( $db, 20, false ), 'test', '',
		2, 1, Utils::createRandomUID( $db, 20, false ), 'test', '',
	];
	$db->query( $sql, $values );

	/*
	 * Test image
	 * 1 (all ones, collection:1)
	 */
	$sql = 'insert into mymopsi_img (id, collection_id, random_uid, hash, name, original_name, mediatype, size)
				values (?,?,?,?,?,?,?,?),(?,?,?,?,?,?,?,?)
				on duplicate key update id=id';
	$values = [
		1, 1, Utils::createRandomUID( $db, 20, false ), '1', '1', '1', '1', '1',
		2, 1, Utils::createRandomUID( $db, 20, false ), '2', '2', '2', '2', '2'
	];
	$db->query( $sql, $values );
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

define(
	'INI',
	[
		"Database" => $database_configs,
		"Settings" => $settings,
	]
);
