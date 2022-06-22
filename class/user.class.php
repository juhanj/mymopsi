<?php declare(strict_types=1);

/**
 * Class User
 */
class User {

	/** @var int */
	public $id;

	/** @var string */
	public $random_uid;

	/** @var string */
	public $username;

	/** @var string */
	public $password;

	/** @var int|null */
	public $type;

	/** @var string */
	public $email;

	/** @var boolean */
	public $admin;

	/** @var Collection[] */
	public $collections;

	/** @var int */
	public $number_of_collections;

	function __construct () {
	}

	/**
	 * @param DBConnection $db
	 * @param int $id
	 * @return User|null
	 */
	static function fetchUserByID ( DBConnection $db, int $id ): ?User {
		$sql = 'select u.*, count(c.id) as number_of_collections
				from mymopsi_user u
				left join mymopsi_collection c on c.owner_id = u.id
				where u.id = ?
				limit 1';
		$values = [ $id ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'User' );

		return $row->id ? $row : null;
	}

	/**
	 * @param DBConnection $db
	 * @param string $ruid Random Unique ID
	 * @return User|null
	 */
	static function fetchUserByRUID ( DBConnection $db, $ruid ): ?User {
		$sql = 'select u.*, count(c.id) as number_of_collections
				from mymopsi_user u
				left join mymopsi_collection c on c.owner_id = u.id
				where u.random_uid = ?
				limit 1';
		$values = [ $ruid ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'User' );

		return $row ?: null;
	}

	/**
	 * @param DBConnection $db
	 * @param string       $identifier Either, ID, UID, username, or email
	 *
	 * @return User|null
	 */
	static function fetchUserByUsernameOrEmail ( DBConnection $db, string $identifier ): ?User {
		$sql = 'select *
				from mymopsi_user
				where username = ?
				   or email = ?
				limit 1';
		$values = [ $identifier, $identifier ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'User' );

		return $row ?: null;
	}

	/**
	 * This method is only meant for admin user
	 * @param DBConnection $db
	 *
	 * @return User[] | null
	 */
	public static function fetchAllUsers ( DBConnection $db ): ?array {
		$sql = 'select *,
                    (select count(id) from mymopsi_collection c where c.owner_id = u.id ) 
                        as number_of_collections
				from mymopsi_user u';
		$values = [];

		/** @var Collection[] $rows */
		$rows = $db->query( $sql, $values, true, 'User' );

		return $rows ?: null;
	}

	/**
	 * @param DBConnection $db
	 */
	function getCollections ( DBConnection $db ) {
		$sql = 'select c.id, c.owner_id, c.random_uid, c.name, c.description, c.public, 
                    c.editable, c.date_added, count(i.id) as number_of_images
				from mymopsi_collection c
				left join mymopsi_img i on c.id = i.collection_id
				where c.owner_id = ?
				group by c.id';
		$values = [ $this->id ];

		$this->collections = $db->query( $sql, $values, true, 'Collection' );
	}
}
