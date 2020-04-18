<?php
declare(strict_types=1);

/**
 * Class ImageController
 */
class ImageController implements Controller {

	public $result;

	public const KB = 1024;
	public const MB = 1048576;
	public const GB = 1073741824;

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $req
	 */
	public function handleRequest ( DBConnection $db, User $user, array $req ) {
		switch ( $req['request'] ?? null ) {
			case 'upload':
				$result = $this->requestUploadNewImages( $db, $user, $req );
				break;
			case 'edit_gps':
				$result = $this->requestEditGPSCoordinate( $db, $user, $req );
				break;
			default:
				$result = false;
				$this->setError( 0, 'Invalid request' );
		}

		$this->result['success'] = $result;
	}

	/**
	 * @param int $id
	 * @param string $msg
	 */
	public function setError ( int $id, string $msg ) {
		$this->result = [
			'success' => false,
			'error' => true,
			'err' => $id,
			'errMsg' => $msg,
		];
	}

	/**
	 * @param DBConnection $db
	 * @param Image $image
	 * @param $lat
	 * @param $long
	 * @return bool
	 */
	function setGPSCoordinates ( DBConnection $db, Image $image, $lat, $long ) {
		if ( is_null( $image->id ) ) {
			throw new InvalidArgumentException( "Image is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_img set latitude = ?, longitude = ? where id = ? limit 1',
			[ $lat, $long, $image->id ]
		);
		return boolval( $rows_changed );
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
	 * @param $folder
	 * @param null $files
	 * @return stdClass[]
	 */
	function getUploadMetadataWithExiftool ( $folder, &$files = null ) {
		// run exiftool on that folder
		$commandOptions =
			" -g3" // I don't know, but it's important! Related to getting GPS fields
			. " -a" // Allow duplicates (needed for gps coordinates)
			. " -gps:all" // All GPS metadata
			. " -Datecreate"
			. " -ImageSize"
			. " -c %.6f" // format for gps coordinates output
		;
		$metadata = Utils::runExiftool( $folder, $commandOptions );

		return $metadata;
	}

	function writeGPSIntoImageFile ( Image $image ) {
		switch ( $image->mediatype ) {
			case 'image/jpeg':
				// code...
				break;

			default:
				// code...
				break;
		}
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param $options
	 * @return bool
	 */
	public function requestUploadNewImages ( DBConnection $db, User $user, $options ) {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );
			return false;
		}

		$collection = (!empty( $options['collection'] ))
			? Collection::fetchCollectionByRUID( $db, $options['collection'] )
			: null;

		if ( !$collection ) {
			$this->setError( -2, 'Collection not valid' );
			return false;
		}

		if ( $collection->owner_id !== $user->id
			or ($collection->public and $collection->editable) ) {
			$this->setError( -3, 'Access denied' );
			return false;
		}

		// User has access to collection. Proceed to checking for errors and filetype.

		if ( !$_FILES ) {
			$this->setError( -4, 'No files received' );
			return false;
		}

		// Would be very bad to have this empty, since it would write to root
		// So check just in case
		if ( strlen( INI['Misc']['path_to_collections'] ) < 20 ) {
			$this->setError( -5, 'Config error' );
			return false;
		}

		// reorganise FILES array
		$files = $this->reorganizeUploadFilesArray( $_FILES );

		$collections = INI['Misc']['path_to_collections'];
		$temp_folder = $collections . "/temp-{$collection->id}-" . mt_rand( 0, 10000 );
		$final_destination = $collections . '/' . $collection->random_uid;


		if ( !file_exists( $final_destination ) ) {
			mkdir( $final_destination );
		}

		// create temp folder for the upload
		// So that I can run exiftool on the uploaded files, but nothing else.
		mkdir( $temp_folder );

		$good_uploads = [];
		$bad_uploads = [];

		// Check each file for errors, duplicates, and such, and then move to temp folder
		foreach ( $files as $index => &$upload ) {
			// Check for PHP upload errors
			if ( $upload['error'] ) {
				$upload['error_msg'] = "PHP upload error";
				$bad_uploads[] = $upload;
				continue;
			}

			// Check filetype in the metadata of the file itself
			// Should be sufficiently fast, on localhost 2000 files 1,5 sec
			$file_mimetype = finfo_file(
				finfo_open( FILEINFO_MIME_TYPE ),
				$upload['tmp_name']
			);
			if ( stripos( $file_mimetype, 'image/' ) === false ) {
				$upload['error_msg'] = "{$file_mimetype} not valid file type";
				$bad_uploads[] = $upload;
				continue;
			} else {
				$upload['mime'] = $file_mimetype;
			}

			// Check for duplicate already in the collection
			// Hash calculation
			$upload['hash'] = hash_file( 'md5', $upload['tmp_name'] );
			$upload['size'] = filesize( $upload['tmp_name'] );
			$is_duplicate = $db->query(
				'select id from mymopsi_img where collection_id = ? and hash = ? and size = ?',
				[ $collection->id, $upload['hash'], $upload['size'] ]
			);
			if ( $is_duplicate ) {
				$upload['error_msg'] = "Duplicate";
				$bad_uploads[] = $upload;
				continue;
			}

			$upload['new_ruid'] = Utils::createRandomUID( $db );
			// New file is just the RUID. Used have have original name attached but
			// exiftool and encoding differences made that difficult.
			$upload['new_file_name'] = $upload['new_ruid'];

			// Move to temporary folder
			$upload['temp_path'] = $temp_folder . '/' . $upload['new_file_name'];
			move_uploaded_file(
				$upload['tmp_name'],
				$upload['temp_path']
			);

			$upload['final_path'] = $final_destination . '/' . $upload['new_file_name'];

			$good_uploads[] = $upload;
		}

		$metadata = $this->getUploadMetadataWithExiftool( $temp_folder );

		// Add each file to database and move to final destination
		foreach ( $good_uploads as $index => &$file ) {

			// What have I created?
			foreach ( $metadata as $f ) {
				// Go through each metadata file, and find the same one, and take results (if any)
				if ( basename( $f->SourceFile ) === $file['new_file_name'] ) {
					if ( isset( $f->Main->GPSLatitude ) ) {
						$file['latitude'] = $f->Main->GPSLatitude;
						$file['longitude'] = $f->Main->GPSLongitude;
					}
				}
			}

			// Add to database
			// Since adding multiple, will do differently from other classes
			//  and not first add empty row. Plus a lot of unique columns to check
			$result = $db->query(
				'insert into mymopsi_img (
                         collection_id, random_uid, hash, name, original_name,
                         filepath, mediatype, size, latitude, longitude,
                         date_created )
                      values (?,?,?,?,?,?,?,?,?,?,?)',
				[
					$collection->id, $file['new_ruid'], $file['hash'], $file['name'], $file['name'],
					$file['final_path'], $file['mime'], $file['size'], $file['latitude'] ?? null, $file['longitude'] ?? null,
					null /*filectime( $file['final_path'] )*/
				]
			);

			if ( $result ) {
				// Move file to final destination
				rename(
					$file['temp_path'],
					$file['final_path']
				);
			}
		}

		// Create (or update if already created) JSON for server-side clustering
		$collContr = new CollectionController();
		$collContr->createServerClusteringJSON( $db, $collection );

		rmdir( $temp_folder );

		$this->result = [
			'success' => true,
			'error' => false,
			'good_uploads' => $good_uploads,
			'failed_uploads' => $bad_uploads,
			'metadata' => $metadata,
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options POST request
	 * @return bool
	 */
	public function requestEditGPSCoordinate ( DBConnection $db, User $user, $options ) {
		// LAT & LONG need to be valid
		if ( empty( $options['lat'] ) or empty( $options['long'] ) ) {
			$this->setError( -1, 'Coordinate not given' );
			return false;
		}

		// image ID needs to be valid
		if ( empty( $options['image'] ) ) {
			$this->setError( -2, 'No image ID' );
			return false;
		} else {
			$image = Image::fetchImageByRUID( $db, $options['image'] );
			if ( !$image ) {
				$this->setError( -3, 'Image not valid' );
				return false;
			}
		}

		$result = $this->setGPSCoordinates( $db, $image, $options['lat'], $options['long'] );

		if ( !$result ) {
			$this->setError( -3, 'Could not edit database, something went wrong' );
			return false;
		}

		// set GPS in file
		// function?
		// check file type first (if supports GPS metadata)
		// write into file
		//TODO: how to write to file with exiftool
		// return true if success, return false if failure or cant write into file
		// Carry on regardless whether success, return success in request

		// Update JSON for server-side clustering
		$collContr = new CollectionController();
		$collection = Collection::fetchCollectionByID($db,$image->collection_id);
		$collContr->createServerClusteringJSON( $db, $collection );

		$this->result = [
			'success' => true,
			'error' => false,
			'old_gps' => [ $image->latitude, $image->longitude ],
			'new_gps' => [ $options['lat'], $options['long'] ]
		];

		return true;
	}
}
