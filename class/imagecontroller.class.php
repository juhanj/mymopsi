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
	 * @param User         $user
	 * @param array        $req
	 */
	public function handleRequest ( DBConnection $db, User $user, array $req ) {
		switch ( $req[ 'request' ] ?? null ) {
			case 'upload':
				$result = $this->requestUploadNewImages( $db, $user, $req );
				break;
			case 'upload_mopsi_csv':
				$result = $this->requestAddMopsiPhotosFromCSV( $db, $user, $req );
				break;
			case 'edit_gps':
				$result = $this->requestEditGPSCoordinate( $db, $user, $req );
				break;
			case 'delete_image':
				$result = $this->requestDeleteImage( $db, $user, $req );
				break;
			case 'create_thumbnail':
				$result = $this->requestCreateThumbnail( $db, $req );
				break;
			default:
				$result = false;
				$this->setError( 0, 'Invalid request' );
		}

		$this->result[ 'success' ] = $result;
	}

	/**
	 * @param int    $id
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
	 * @param Image        $image
	 * @param              $lat
	 * @param              $long
	 *
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
	 *
	 * @param array $_files _FILES global variable
	 *
	 * @return array re-formatted _FILES array
	 */
	function reorganizeUploadFilesArray ( array $_files ) {
		$result = [];
		foreach ( $_files as $fileArray ) {
			if ( is_array( $fileArray[ 'name' ] ) ) {
				foreach ( $fileArray as $attrib => $list ) {
					foreach ( $list as $index => $value ) {
						$result[ $index ][ $attrib ] = $value;
					}
				}
			}
			else {
				$result[] = $fileArray;
			}
		}

		return $result;
	}

	/**
	 * @param      $folder
	 * @param null $files
	 *
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
		$metadata = Common::runExiftool( $folder, $commandOptions );

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
	 * Use exec() and ImageMagick to create a 256x256 .webp thumbnail.
	 * Does not check if the new path directory exists, and WILL FAIL if it doesn't.
	 *
	 * @param string $imagePath    Path to full image
	 * @param string $newThumbPath Path to new thumb (where it will be saved).
	 *                             Make sure the directory exists!
	 *
	 * @return bool True, if result_code from exec == 0 (1 means bad)
	 */
	function createImageThumbnailFile ( string $imagePath, string $newThumbPath ): bool {
		$command = "magick convert "
			. $imagePath // Original image
			. " -thumbnail 256x256 " // Strip metadata, and size of thumbnail
			. " -sharpen 0x.5 " // Sharpen image a bit, comes out blurry otherwise
			. " -gravity center " // Center image for following option
			. " -extent 256x256 " // Make image square
			//TODO: transparent background (failed multiple tries) --jj 21-05-16
			. $newThumbPath; // New thumbnail path

		exec( $command, $output, $returnCode );

		return ($returnCode === 0);
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 * @param              $options
	 *
	 * @return bool
	 */
	public function requestUploadNewImages ( DBConnection $db, User $user, $options ): bool {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		$collection = (!empty( $options[ 'collection' ] ))
			? Collection::fetchCollectionByRUID( $db, $options[ 'collection' ] )
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
		if ( strlen( INI[ 'Misc' ][ 'path_to_collections' ] ) < 20 ) {
			$this->setError( -5, 'Config error' );

			return false;
		}

		// reorganise FILES array, because I don't like how PHP does it.
		$files = $this->reorganizeUploadFilesArray( $_FILES );

		$collections = INI[ 'Misc' ][ 'path_to_collections' ];
		$temp_folder = $collections . "temp-{$collection->id}-" . mt_rand( 0, 10000 ) . '/';
		$final_destination = $collections . $collection->random_uid . '/';
		$final_thumb_destination = $final_destination . '/thumb/';

		if ( !file_exists( $final_destination ) ) {
			mkdir( $final_destination );
		}
		if ( !file_exists( $final_thumb_destination ) ) {
			mkdir( $final_thumb_destination );
		}

		// create temp folder for the upload
		// So that I can run exiftool on the uploaded files, but nothing else.
		mkdir( $temp_folder );

		$good_uploads = [];
		$bad_uploads = [];

		// Check each file for errors, duplicates, and such, and then move to temp folder
		foreach ( $files as $index => &$upload ) {
			// Check for PHP upload errors
			if ( $upload[ 'error' ] ) {
				$upload[ 'error_msg' ] = "PHP upload error";
				$bad_uploads[] = $upload;
				continue;
			}

			// Check filetype in the metadata of the file itself
			// Should be sufficiently fast, on localhost 2000 files 1,5 sec
			$file_mimetype = finfo_file(
				finfo_open( FILEINFO_MIME_TYPE ),
				$upload[ 'tmp_name' ]
			);
			if ( stripos( $file_mimetype, 'image/' ) === false ) {
				$upload[ 'error_msg' ] = "{$file_mimetype} not valid file type";
				$bad_uploads[] = $upload;
				continue;
			}
			else {
				$upload[ 'mime' ] = $file_mimetype;
			}

			// Check for duplicate already in the collection
			// Hash calculation
			$upload[ 'hash' ] = hash_file( 'md5', $upload[ 'tmp_name' ] );
			$upload[ 'size' ] = filesize( $upload[ 'tmp_name' ] );
			$is_duplicate = $db->query(
				'select id from mymopsi_img where collection_id = ? and hash = ? and size = ?',
				[ $collection->id, $upload[ 'hash' ], $upload[ 'size' ] ]
			);
			if ( $is_duplicate ) {
				$upload[ 'error_msg' ] = "Duplicate";
				$bad_uploads[] = $upload;
				continue;
			}

			$upload[ 'new_ruid' ] = Common::createRandomUID( $db );
			// New file is just the RUID + extension. Used to have original name attached but
			// exiftool and encoding differences made that difficult.
			// Extension is needed for ImageMagick thumbnail generation (used to detect file type).
			$upload[ 'new_file_name' ] = $upload[ 'new_ruid' ] . "." . pathinfo( $upload[ 'name' ] )[ 'extension' ];

			// Move to temporary folder
			$upload[ 'temp_path' ] = $temp_folder . $upload[ 'new_file_name' ];

			// This is not a simple rename(), it also checks that the file is actually from a POST request.
			// (Reason for comment: failed unit test... Will have to write better solution.)
			move_uploaded_file(
				$upload[ 'tmp_name' ],
				$upload[ 'temp_path' ]
			);

			$upload[ 'final_path' ] = $final_destination . $upload[ 'new_file_name' ];
			$upload[ 'thumb_path' ] = $final_thumb_destination . "thumb-{$upload[ 'new_file_name' ]}.webp";

			$good_uploads[] = $upload;
		}

		$metadata = $this->getUploadMetadataWithExiftool( $temp_folder );

		// Add each file to database and move to final destination
		foreach ( $good_uploads as $index => &$file ) {

			// What have I created?
			foreach ( $metadata as $f ) {
				// Go through each metadata file, and find the same one, and take results (if any)
				if ( basename( $f->SourceFile ) === $file[ 'new_file_name' ] ) {
					if ( isset( $f->Main->GPSLatitude ) ) {
						$file[ 'latitude' ] = $f->Main->GPSLatitude;
						$file[ 'longitude' ] = $f->Main->GPSLongitude;
					}
				}
			}

			// Add to database
			// Since adding multiple, will do differently from other classes
			//  and not first add empty row. Plus a lot of unique columns to check
			$result = $db->query(
				'insert into mymopsi_img (
                         collection_id, random_uid, hash, name, original_name,
                         filepath, thumbpath, mediatype, size, latitude, longitude,
                         date_created )
                      values (?,?,?,?,?,?,?,?,?,?,?,?)',
				[
					$collection->id, $file[ 'new_ruid' ], $file[ 'hash' ], $file[ 'name' ], $file[ 'name' ],
					$file[ 'final_path' ], $file[ 'thumb_path' ], $file[ 'mime' ], $file[ 'size' ],
					$file[ 'latitude' ] ?? null, $file[ 'longitude' ] ?? null,
					null /*filectime( $file['final_path'] )*/
				]
			);

			if ( $result ) {
				// Move file to final destination
				rename(
					$file[ 'temp_path' ],
					$file[ 'final_path' ]
				);

				$this->createImageThumbnailFile(
					$file[ 'final_path' ], $file[ 'thumb_path' ]
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
	 * @param User         $user
	 * @param array        $options POST request
	 *
	 * @return bool
	 */
	public function requestEditGPSCoordinate ( DBConnection $db, User $user, array $options ): bool {
		// LAT & LONG need to be valid
		if ( empty( $options[ 'lat' ] ) or empty( $options[ 'long' ] ) ) {
			$this->setError( -1, 'Coordinate not given' );

			return false;
		}

		// image ID needs to be valid
		if ( empty( $options[ 'image' ] ) ) {
			$this->setError( -2, 'No image ID' );

			return false;
		}
		else {
			$image = Image::fetchImageByRUID( $db, $options[ 'image' ] );
			if ( !$image ) {
				$this->setError( -3, 'Image not valid' );

				return false;
			}
		}

		$result = $this->setGPSCoordinates( $db, $image, $options[ 'lat' ], $options[ 'long' ] );

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
		$collection = Collection::fetchCollectionByID( $db, $image->collection_id );
		$collContr->createServerClusteringJSON( $db, $collection );

		$this->result = [
			'success' => true,
			'error' => false,
			'old_gps' => [ $image->latitude, $image->longitude ],
			'new_gps' => [ $options[ 'lat' ], $options[ 'long' ] ],
		];

		return true;
	}

	/**
	 * This was made to help a student, and is not considered normal behaviour of the site.
	 *
	 * @param \DBConnection $db
	 * @param \User         $user
	 * @param array         $options
	 *
	 * @return bool
	 */
	public function requestAddMopsiPhotosFromCSV ( DBConnection $db, User $user, array $options ): bool {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		$collection = (!empty( $options[ 'collection' ] ))
			? Collection::fetchCollectionByRUID( $db, $options[ 'collection' ] )
			: null;

		if ( !$collection ) {
			$this->setError( -2, 'Collection not valid' );

			return false;
		}

		if ( $collection->owner_id !== $user->id
			or ($collection->public and $collection->editable) ) {
			$this->setError( -3, 'Access to collection denied' );

			return false;
		}

		$photos = $options[ 'photos' ];
		$good_uploads = [];
		$bad_uploads = [];

		// Values for prepared statement
		$ids = array_map( function ( $photo ) {
			return $photo[ 'photo_id' ];
		}, $photos );
		// Question marks for prepared statement
		$inQuestionMarksString = str_repeat( '?,', count( $ids ) - 1 ) . '?';

		$sql = "SELECT po.id as photo_id
					, po.id as point_id
					, po.name
					, po.description
					, po.latitude
					, po.longitude
					, po.timestamp
					, ph.photo_id as filename
					, ph.format
					, ph.userid
				FROM Point po
					JOIN Photo ph on po.id = ph.point_id
				WHERE ph.photo_id IN ({$inQuestionMarksString})";

		$rows = $db->query( $sql, $ids, true );

		//
		$final_destination = INI[ 'Misc' ][ 'path_to_collections' ] . $collection->random_uid;
		$mopsi_images_dir = INI[ 'Misc' ][ 'path_to_mopsi_photos' ];

		if ( !file_exists( $final_destination ) ) {
			mkdir( $final_destination );
		}

		//
		foreach ( $rows as $photo ) {
			$photo->filepath = $mopsi_images_dir . '/' . $photo->filename;

			/*
			 * Duplicate check for already uploaded images
			 */
			$sql = 'select 1
					from mymopsi_img
					where collection_id = ?
						and hash = ?
						and size = ?';
			$values = [
				$collection->id,
				$photo->hash = hash_file( 'md5', $photo->filepath ),
				$photo->size = filesize( $photo->filepath ),
			];

			$is_duplicate = $db->query( $sql, $values );

			if ( $is_duplicate ) {
				$photo->error_msg = "Duplicate";
				$bad_uploads[] = $photo;
				unset( $photo );
				continue;
			}

			/*
			 * Duplicate check for images in current upload batch
			 */
			foreach ( $rows as $other_photo ) {
				if ( $other_photo->photo_id === $photo->photo_id ) {
					continue;
				}

				if ( isset( $other_photo->hash ) and ($photo->hash === $other_photo->hash) ) {
					$photo->error_msg = "Duplicate";
					$bad_uploads[] = $photo;
					break;
				}
			}

			if ( isset( $other_photo->error_msg ) and ($photo->error_msg === 'Duplicate') ) {
				unset( $photo );
				continue;
			}

			/*
			 * Creating random unique identifier (RUID)
			 */
			$photo->new_ruid = Common::createRandomUID( $db );

			$good_uploads[] = $photo;
		}

		foreach ( $good_uploads as $photo ) {
			$sql = 'insert ignore into mymopsi_img (
						collection_id, random_uid, hash, name, original_name,
						filepath, mediatype, size, latitude, longitude,
						date_created )
				  values (?,?,?,?,?,?,?,?,?,?,?)';
			$values = [
				$collection->id,
				$photo->new_ruid,
				$photo->hash,
				$photo->name,
				$photo->name,
				$photo->filepath,
				'image/jpeg',
				$photo->size,
				$photo->latitude,
				$photo->longitude,
				$photo->timestamp,
			];

			$result = $db->query( $sql, $values );

			if ( $result ) {
				$good_uploads[] = $photo;
			}
			else {
				$bad_uploads[] = $photo;
			}
		}

		// Create (or update if already created) JSON for server-side clustering
		$collContr = new CollectionController();
		$collContr->createServerClusteringJSON( $db, $collection );

		$this->result = [
			'success' => true,
			'error' => false,
			'good_uploads' => $good_uploads,
			'failed_uploads' => $bad_uploads,
		];

		return true;
	}

	public function requestDeleteImage ( DBConnection $db, User $user, array $options ): bool {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		// Checking valid image
		if ( empty( $options[ 'image' ] ) ) {
			$this->setError( -1, 'No image ID' );

			return false;
		}
		else {
			$image = Image::fetchImageByRUID( $db, $options[ 'image' ] );
			if ( !$image ) {
				$this->setError( -2, 'Image not valid' );

				return false;
			}
		}

		$collection = Collection::fetchCollectionByID( $db, $image->collection_id );

		if ( $collection->owner_id !== $user->id and $user->admin == false ) {
			$this->setError( -3, "No access" );

			return false;
		}

		// Delete files
		// Function also checks if file exists, so if it doesn't, just move on to deleting db row
		Common::deleteFiles( $image->filepath );

		// Delete database row
		$db->query(
			"delete from mymopsi_img where id = ?",
			[ $image->id ]
		);

		$this->result = [
			'success' => true,
			'error' => false,
		];

		return true;
	}

	public function requestCreateThumbnail ( DBConnection $db, array $options ) {

		$image = Image::fetchImageByRUID( $db, $options[ 'image' ] );

		if ( !$image ) {
			$this->setError( -1, 'Image not valid' );

			return false;
		}

		// Collection RUID is needed for file path
		$collection = Collection::fetchCollectionByID( $db, $image->collection_id );

		$thumbDirectory = INI[ 'Misc' ][ 'path_to_collections' ] . $collection->random_uid . '/thumb/';
		$newThumbFileName = "thumb-{$image->random_uid}.webp";

		$newFullPath = $thumbDirectory . $newThumbFileName;

		if ( !file_exists( $thumbDirectory ) ) {
			mkdir( $thumbDirectory );
		}

		$result = $this->createImageThumbnailFile( $image->filepath, $newFullPath );

		if ( !$result ) {
			$this->setError( -2, 'Could not create thumbnail' );

			$newFullPath = 'no_thumbnail';
		}

		// Save thumbnail path to database
		$db->query(
			'update mymopsi_img set thumbpath = ? where id = ? limit 1',
			[ $newFullPath, $image->id ]
		);

		$this->result = [
			// Request stuff:
			'success' => true,
			'error' => false,
			// Data:
			'thumbpath' => $newFullPath,
		];

		return true;
	}

}
