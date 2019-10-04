<?php declare(strict_types=1);

class Collection {

	/** @var int */
	public $id;
	/** @var string $random_uid */
	public $random_uid;
	/** @var int $owner_id */
	public $owner_id;

	/** @var Image[] Images in collection, and their info. */
	public $imgs;

	public $name;
	public $description;

	public $public;
	public $editable;

	public $added;
	public $last_edited;

	public $number_of_images;

	function __construct () {}

	function populateVariables ( DBConnection $db, int $id ) {
		$sql = "select id, owner_id, random_uid, name, description, public, editable, date_added, last_edited
				from mymopsi_collection c
				where c.id = ?
				limit 1";

		$row = $db->query( $sql, [ $id ] );

		if ( !$row ) { return; }

		foreach ( $row as $property => $propertyValue ) {
			$this->{$property} = $propertyValue;
		}
	}

	function getCollectionImgs ( DBConnection $db ) {
		$sql = "select id, collection_id, random_uid, hash, name, original_name, filepath, latitude, longitude, date_created, date_added, size
				from mymopsi_img i
				where collection_id = ?
				order by name";

		$this->imgs = $db->query( $sql, [ $this->id ], true, 'Image' );
	}

	/**
	 * @param \DBConnection $db
	 * @param string        $uid
	 * @return \Collection
	 */
	static function fetchCollection ( DBConnection $db, string $uid ) : ?Collection {
		$sql = 'select id, owner_id, random_uid, name, description, public, editable, date_added, last_edited
				from mymopsi_collection 
				where random_uid = ? 
				limit 1';
		$values = [ $uid ];

		/** @var Collection $row */
		$row = $db->query( $sql, $values, false, 'Collection' );

		return $row ?: null;
	}
}
