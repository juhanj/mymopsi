<?php declare(strict_types=1);

class User {

	/** @var int */
	public $id;

	/** @var string */
	public $random_uid;

	/** @var string */
	public $email;

	/** @var \Collection[] */
	public $collections;

	function __construct () {}

	function getCollections ( DBConnection $db ) {
		$sql = 'select c.id, owner_id, c.random_uid, c.name, description, public, editable, c.date_added, last_edited, count(i.id) as number_of_images
				from mymopsi_collection c
				left join mymopsi_img i on c.id = i.collection_id
				where owner_id = ?';
		$values = [ $this->id ];

		$this->collections = $db->query( $sql, $values, true, 'Collection' );
	}

	/**
	 * @param \DBConnection $db
	 * @param string        $uid
	 * @return \User
	 */
	static function fetchUser ( DBConnection $db, string $uid ) : ?User {
		$sql = 'select id, random_uid, email
				from mymopsi_user 
				where random_uid = ? 
				limit 1';
		$values = [ $uid ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'User' );

		return $row ?: null;
	}
}
