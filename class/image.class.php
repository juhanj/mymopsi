<?php declare(strict_types=1);

class Image {

	public $id;
	public $collection_id;
	public $random_uid;

	public $hash;

	public $name;
	public $original_name;
	public $filepath;

	public $mediatype;
	public $size;

	public $latitude;
	public $longitude;

	public $date_created;
	public $date_added;

	/**
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
	 * @param DBConnection $db
	 * @param string $ruid Random Unique ID
	 * @return Image|null
	 */
	public static function fetchImageByRUID ( DBConnection $db, $ruid ): ?Image {
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
