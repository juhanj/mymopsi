<?php declare(strict_types=1);

class Collection {

	/** @var int */
	public $id;
	/** @var string $random_uid */
	public $random_uid;
	/** @var int $owner_id */
	public $owner_id;

	public $name;
	public $description;

	public $public;
	public $editable;

	public $added;
	public $last_edited;

	public $number_of_images;

	/** @var Image[] $images Images in collection, and their info. */
	public $images;

	/** @var User $owner */
	public $owner;

	function __construct () {
	}

	/**
	 * @param DBConnection $db
	 * @param int $id
	 * @return Collection|null
	 */
	public static function fetchCollectionByID ( DBConnection $db, int $id ): ?Collection {
		$sql = 'select c.*, 
                    count(i.id) as number_of_images
				from mymopsi_collection c
					left join mymopsi_img i on c.id = i.collection_id 
				where c.id = ?
				group by c.id
				limit 1';
		$values = [ $id ];

		/** @var Collection $row */
		$row = $db->query( $sql, $values, false, 'Collection' );

		return $row ?: null;
	}

	/**
	 * @param DBConnection $db
	 * @param string $ruid
	 * @return Collection|null
	 */
	public static function fetchCollectionByRUID ( DBConnection $db, string $ruid ): ?Collection {
		$sql = 'select c.*, 
                    count(i.id) as number_of_images
				from mymopsi_collection c
					left join mymopsi_img i on c.id = i.collection_id 
				where c.random_uid = ?
				group by c.id
				limit 1';
		$values = [ $ruid ];

		/** @var Collection $row */
		$row = $db->query( $sql, $values, false, 'Collection' );

		return $row ?: null;
	}

	/**
	 * Get collection's images from the database
	 * @param DBConnection $db
	 */
	public function getImages ( DBConnection $db ) {
		$sql = "select *
				from mymopsi_img i
				where collection_id = ?";

		$this->images = $db->query( $sql, [ $this->id ], true, 'Image' );
	}

	/**
	 * Get collection's images from the database
	 * @param DBConnection $db
	 */
	public function getOwner ( DBConnection $db ) {
		$sql = "select *
				from mymopsi_user u
				where id = ?
				limit 1";

		$this->owner = $db->query( $sql, [ $this->owner_id ], false, 'User' );
	}
}
