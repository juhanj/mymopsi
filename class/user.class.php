<?php declare(strict_types=1);

class User {

	/** @var int */
	public $id;

	/** @var string */
	public $random_uid;

	/** @var string */
	public $email;

	/** @var Collection[] */
	public $collections;

	/** @var string */
	public $password;

	/** @var int|null */
	public $type;

	function __construct () {
	}
	
	/**
	 * @param DBConnection $db
	 * @param string $identifier Either, ID, UID, username, or email
	 * @return User|null
	 */
	static function fetchUser ( DBConnection $db, string $identifier ): ?User {
		$sql = 'select id, random_uid, username, password, email, type
				from mymopsi_user
				where id = ?
				   or random_uid = ?
				   or username = ?
				   or email = ?
				limit 1';
		$values = [ $identifier, $identifier, $identifier, $identifier ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'User' );

		return $row ?: null;
	}

	/**
	 * @param DBConnection $db
	 */
	function getCollections ( DBConnection $db ) {
		$sql = 'select c.id, owner_id, c.random_uid, c.name, description, public, editable, c.date_added, last_edited, count(i.id) as number_of_images
				from mymopsi_collection c
				left join mymopsi_img i on c.id = i.collection_id
				where owner_id = ?';
		$values = [ $this->id ];

		$this->collections = $db->query( $sql, $values, true, 'Collection' );
	}
}
