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

	public $date_added;

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
	 * @param DBConnection $db
	 *
	 * @return Collection[] | null
	 */
	public static function fetchPublicCollections ( DBConnection $db ): ?array {
		$sql = 'select c.*,
                    count(i.id) as number_of_images
				from mymopsi_collection c
					left join mymopsi_img i on c.id = i.collection_id 
				where c.public = true
				group by c.owner_id';
		$values = [];

		/** @var Collection[] $rows */
		$rows = $db->query( $sql, $values, true, 'Collection' );

		return $rows ?: null;
	}

	/**
	 * This method is only meant for admin user
	 * @param DBConnection $db
	 *
	 * @return Collection[] | null
	 */
	public static function fetchAllCollections ( DBConnection $db ): ?array {
		$sql = 'select c.*,
                    count(i.id) as number_of_images
				from mymopsi_collection c
					left join mymopsi_img i on c.id = i.collection_id
				group by c.owner_id';
		$values = [];

		/** @var Collection[] $rows */
		$rows = $db->query( $sql, $values, true, 'Collection' );

		return $rows ?: null;
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
	 * Get collection's images from the database, with pagination and sorting
	 * @param DBConnection $db
	 * @param int[]    $pagination <p> [ipp, offset]. Items Per Page, and offset where to start.
	 * @param int[]    $ordering <p> [column, ASC|DESC]. 1. index in hardcoded array of columns. 2. 0=ASC, 1=DESC
	 */
	public function getImagesWithPagination ( DBConnection $db, array $pagination, array $ordering ) {

		$ipp = (int)$pagination[0]; // Items Per Page
		$offset = (int)$pagination[1];

		$orders = [
			['name','mediatype','size','latitude,longitude','date_created','date_added'],
			["ASC","DESC"]
		];
		$ordering = "{$orders[0][$ordering[0]]} {$orders[1][$ordering[1]]}";

		$sql = "select id
				    , collection_id
					, random_uid
					, hash
					, name
					, original_name
					, filepath
					, mediatype
					, size
					, latitude
					, longitude
					, date_created
					, date_added
				from mymopsi_img i
				where collection_id = ?
				order by {$ordering} 
				limit ? offset ?";

		$this->images = $db->query( $sql, [ $this->id, $ipp, $offset ], true, 'Image' );
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

	/**
	 * JSON formatted string of images
	 * (with enabled: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)
	 * @return string
	 */
	public function printImagesJSON (): string {
		return json_encode(
			$this->images,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
		);
	}
}
