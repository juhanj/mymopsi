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
	public $thumbnailpath;
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
	 * @param int          $id
	 *
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
	 * @param string       $ruid Random Unique ID
	 *
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

	public static function fetchCollectionRepresentativeImage
	( DBConnection $db, string $collRuid, int $repType = 2 ): Image {
		$collection = Collection::fetchCollectionByRUID( $db, $collRuid );

		switch ( $repType ) {
			case 0:
				// first
				$orderBy = 'date_added asc';
				break;
			case 1 :
				// last
				$orderBy = 'date_added desc';
				break;
			case 2 :
			default :
				// random
				$orderBy = 'rand()';
		}

		$sql = "select id
                , random_uid
                , mediatype
				, filepath
				, thumbnailpath
				, size 
			from mymopsi_img
			where collection_id = ?
			order by $orderBy
			limit 1";
		$values = [ $collection->id ];

		/** @var Image $image */
		$image = $db->query( $sql, $values, false, 'Image' );
		return $image;
	}

	/**
	 * Used in edit-image.php, for printing out metadata in textbox
	 *
	 * @param $lang
	 *
	 * @return string
	 */
	public function getFileMetadata ( $lang ): string {
		$commandOptions =
			" -g -a --FileName --Directory --FilePermissions -lang " . $lang;

		$output = Common::runExiftool( $this->filepath, $commandOptions, true );

		return implode( "\n", $output );
	}

}
