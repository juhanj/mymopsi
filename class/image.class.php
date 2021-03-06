<?php declare(strict_types=1);

/**
 * For fetching data from database, and easier handling in code. Modifying
 * database is done only in controller-class, this is only for getting/viewing data.
 */
class Image {

	public $id;
	public $collection_id;
	public $random_uid;

	public $hash;

	public $name;
	public $original_name;
	public $description;

	public $filepath;
	public $thumbpath;
	public $mediatype;
	public $size;

	public $latitude;
	public $longitude;

	public $date_created;
	public $date_added;

	/**
	 * Fetch image by database ID (auto-incremenent primary key)
	 * Can be used on the server-side when database obfuscation is not needed.
	 *
	 * @param DBConnection $db
	 * @param int $id
	 * @return Image|null
	 */
	public static function fetchImageByID ( DBConnection $db, int $id ): ?Image {
		$sql = 'select *
				from mymopsi_img
				where id = ?
				limit 1';
		$values = [ $id ];

		/** @var Image $row */
		$row = $db->query( $sql, $values, false, 'Image' );

		return $row ?: null;
	}

	/**
	 * Fetch image object by RUID (for when anonymity/database obscufaction is important, e.g. UI)
	 *
	 * @param DBConnection $db
	 * @param string $ruid Random Unique ID
	 * @return Image|null
	 */
	public static function fetchImageByRUID ( DBConnection $db, string $ruid ): ?Image {
		$sql = 'select *
				from mymopsi_img
				where random_uid = ?
				limit 1';
		$values = [ $ruid ];

		/** @var Image $row */
		$row = $db->query( $sql, $values, false, 'Image' );

		return $row ?: null;
	}
}
