<?php declare(strict_types=1);

class Collection {

	/** @var string */
	public $id;
	/** @var string $random_uid */
	public $random_uid;
	/** @var string $owner_id */
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
				where c.id = ?";

		$row = $db->query( $sql, [ $id ] );

		if ( !$row ) { return; }

		foreach ( $row as $property => $propertyValue ) {
			$this->{$property} = $propertyValue;
		}
	}

	function getCollectionImgs ( DBConnection $db ) {
		$sql = "select id, collection_id, random_uid, hash, name, original_name, extension, latitude, longitude, date_created, date_added, size
				from mymopsi_img i
				where collection_id = ?";

		$this->imgs = $db->query( $sql, [ $this->id ], true, 'Image' );
	}

	/**
	 * @param \DBConnection $db
	 * @param int           $id
	 * @return \Collection
	 */
	static function fetchCollection ( DBConnection $db, int $id ) : ?Collection {
		$sql = 'select * from mymopsi_collection where id = ? limit 1';
		$values = [ $id ];

		/** @var Collection $row */
		$row = $db->query( $sql, $values, false, 'Collection' );

		return $row ?: null;
	}
}
