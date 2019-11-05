<?php declare(strict_types=1);

class Image {

	public $id;
	public $collection_id;
	public $random_uid;

	public $name;
	public $original_name;
	public $mediatype;

	public $filepath;

	public $latitude;
	public $longitude;

	public $date_created;
	public $date_added;

	public $hash;
	public $size;

	/**
	 * @param DBConnection $db
	 * @param int $id
	 * @return Image|null
	 */
	public static function fetchImageByID ( DBConnection $db, int $id ): ?Image {
		$sql = 'select *
				from mymopsi_image
				where id = ?
				limit 1';
		$values = [ $id ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'Image' );

		return $row ?: null;
	}

	/**
	 * @param DBConnection $db
	 * @param string $ruid Random Unique ID
	 * @return Image|null
	 */
	public static function fetchImageByRUID ( DBConnection $db, $ruid ): ?Image {
		$sql = 'select *
				from mymopsi_image
				where random_uid = ?
				limit 1';
		$values = [ $ruid ];

		/** @var User $row */
		$row = $db->query( $sql, $values, false, 'Image' );

		return $row ?: null;
	}
}
