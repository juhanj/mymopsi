<?php declare(strict_types=1);

/**
 * Class CollectionController
 */
class CollectionControllerNEW {

	/**
	 * @var mixed
	 */
	public $result = null;

	public const KB = 1024;
	public const MB = 1048576;
	public const GB = 1073741824;

	public const MIN_NAME_LENGTH = 1;
	public const MAX_NAME_LENGTH = 190;

	/**
	 * @param DBConnection $db
	 * @param $ruid
	 * @return bool true if available
	 */
	private function checkRandomUIDAvailable ( DBConnection $db, $ruid ) {
		return !$db->query(
			'select 1 from mymopsi_collection where random_uid = ?',
			[ $ruid ]
		);
	}

	/**
	 * @param DBConnection $db
	 * @return string A 20-char long random string. Either random_byte() (cryptographically secure pseudo-random),
	 * or just shuffled alpha-numeric characters (not secure, only used if random_bytes() not available).
	 * Guaranteed unique (checked against database)
	 */
	private function createRandomUID ( DBConnection $db ) {
		$uid = null;
		do {
			try {
				$uid = bin2hex( random_bytes( 10 ) );
			} catch ( Exception $e ) {
				$uid = substr( str_shuffle( '123456789QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm' ), 0, 20 );
			}
		} while ( !$this->checkRandomUIDAvailable( $db, $uid ) );

		return $uid;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param string $name
	 * @return bool
	 */
	public function createNewCollection ( DBConnection $db, User $user, string $name ) {
		$name = trim( substr( $name ?? '', 0, self::MAX_NAME_LENGTH ) );

		if ( strlen( $name ) < self::MIN_NAME_LENGTH ) {
			$this->result = -1;
			return false;
		}

		$ruid = $this->createRandomUID( $db );

		$sql = 'insert into mymopsi_collection (owner_id, random_uid, name)
				values (?,?,?)';
		$values = [ $user->id, $ruid, $name ];

		$result = $db->query(
			$sql,
			$values
		);

		$id = $db->getConnection()->lastInsertId();

		if ( $id and !file_exists( INI['Misc']['path_to_collections'] . "/{$id}/" ) ) {
			mkdir( INI['Misc']['path_to_collections'] . "/{$id}/", 0755 );
		} else {
			$this->result = false;
			return false;
		}

		$this->result = [
			'success' => $result,
			'uid' => $ruid,
			'name' => $name
		];

		return true;
	}

	/**
	 * @param Image $img
	 * @return bool
	 */
	function deleteImage ( Image $img ) {
		//TODO
		return false;
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @return bool
	 */
	function deleteCollection ( DBConnection $db, Collection $collection ) {
		$sql = 'select id,filepath from mymopsi_img where collection_id = ?';
		$rows = $db->query( $sql, [ $collection->id ], true, 'Image' );
		$allImagesDeleted = true;
		$result = false;
		if ( $rows ) {
			foreach ( $rows as $img ) {
				$result = $this->deleteImage( $img );
				if ( !$result ) {
					$allImagesDeleted = false;
				}
			}
			if ( !$allImagesDeleted ) {
				$this->result = [
					'success' => false
				];
			}
		}

		if ( $allImagesDeleted ) {
			$sql = 'delete from mymopsi_collection where id = ? limit 1';
			$result = $db->query( $sql, [ $collection->id ] );

			$this->result = [
				'success' => $result
			];
		}

		return ($allImagesDeleted and $result);
	}

	/**
	 * For organizing the global _FILES variable a bit more sensibly, and more usable.
	 * See comment https://www.php.net/manual/en/reserved.variables.files.php#121500 in the PHP manual.
	 * @param array $_files _FILES global variable
	 * @return array re-formatted _FILES array
	 */
	function reorganizeUploadFilesArray ( array $_files ) {
		$result = [];
		foreach ( $_files as $fileArray ) {
			if ( is_array( $fileArray['name'] ) ) {
				foreach ( $fileArray as $attrib => $list ) {
					foreach ( $list as $index => $value ) {
						$result[$index][$attrib] = $value;
					}
				}
			} else {
				$result[] = $fileArray;
			}
		}

		return $result;
	}

	/**
	 * Run exiftool for a given folder and output to console in JSON format.
	 * Note! ExifTool will not read .tmp files. They need to be given correct file extension first.
	 * @param string $dir Directory under path_to_collections
	 * @return array Image metadata from all images in given directory
	 */
	function runExiftool ( string $dir ) {

		$perl = INI[ 'Misc' ][ 'perl' ];

		$exift = DOC_ROOT . WEB_PATH . '/exiftool/exiftool';

		// -j : print JSON console output
		// -g3 : I DON'T KNOW, but it's important!
		// -a : allow duplicates (needed for gps coordinates)
		// -gps:all : all gps exif data
		// -Datecreate : self-explanatory (should be time image was taken/created)
		// -ImageSize : self-explanatory
		// -c %.6f : format for gps coordinates output
		$command = "-j -g3 -a -gps:all -Datecreate -ImageSize -c %.6f";

		$target = INI[ 'Misc' ][ 'path_to_collections' ] . "/{$dir}/";

		exec(
			"{$perl} {$exift} {$command} {$target}" ,
			$output
		);

		return json_decode( implode( "" , $output ) );
	}
}
