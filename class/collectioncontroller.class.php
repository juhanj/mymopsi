<?php declare(strict_types=1);

class CollectionController {

	public $result = null;

	public const KB = 1024;
	public const MB = 1048576;
	public const GB = 1073741824;

	/**
	 * CollectionController constructor.
	 * @param DBConnection $db
	 * @param array         $parameters For example, from a form POST data.
	 */
	function __construct ( DBConnection $db , array $parameters ) {
		if ( !$db or !$parameters ) {
			return;
		}

		switch ( $parameters[ 'request' ] ) {
			case 'createNewCollection':
				$this->createNewCollection( $db , $parameters[ 'name' ] , $parameters[ 'email' ] );
				break;
			case 'addImagesToCollection':
				$this->addImagesToCollection( $db, $parameters[ 'collection-uid' ], $_FILES );
				break;
			case 'getPublicCollections':
				$this->getPublicCollections( $db, $parameters[ 'collection-uid' ] );
				break;
			default:
				$this->result = [ 'success' => false ];
		}
	}

	function checkRandomUIDAvailable ( DBConnection $db, $ruid ) {
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
	function createRandomUID ( DBConnection $db ) {
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
	 * @param string        $email
	 * @param string        $id
	 */
	function changeCollectionOwner ( DBConnection $db , string $email , string $id ) {
		// Check if user already exists in database
		$row = $db->query(
			'select id from mymopsi_user where email = ?' ,
			[ $email ]
		);

		// Create user if not
		if ( !$row ) {
			$db->query(
				'insert into mymopsi_user (email,random_uid) values (?,?)' ,
				[ $email , $ruuid = $this->createRandomUID($db) ]
			);
		}
		$user_id = $row->id ?? $db->getConnection()->lastInsertId();
		$db->query(
			'update mymopsi_collection set owner_id = ? where id = ?' ,
			[ $user_id , $id ]
		);
	}

	/**
	 * @param DBConnection $db
	 * @param string        $name
	 * @param string        $owner
	 */
	function createNewCollection ( DBConnection $db , string $name , string $owner ) {
		$sql = "insert into mymopsi_collection ( name, random_uid ) values (?,?)";

		$values = [ $name , $ruuid = $this->createRandomUID($db) ];
		$result = $db->query( $sql , $values );
		$id = $db->getConnection()->lastInsertId();

		if ( $id and !file_exists( INI[ 'Misc' ][ 'path_to_collections' ] . "/{$id}/" ) ) {
			mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . "/{$id}/" , 0755 );
		}
		else {
			$this->result = false;
			return;
		}

		$this->result = [
			'success' => $result,
			'uid' => $ruuid,
			'name' => $name
		];

		if ( $owner ) {
			$this->changeCollectionOwner( $db , $owner , $id );
		}
	}

	function deleteImage () {
		//TODO: Delete image
		//  delete img from database
		//  delete img from folder
	}

	function deleteCollection ( DBConnection $db , string $id ) {
		//TODO: Delete collection
		//  check images first
		//      use deleteImage() ?
		//      more efficient to do all at once? For file deletion probably not.
		//  delete collection from database
		//  delete folder
	}

	/**
	 * For organizing the global _FILES variable a bit more sensibly, and more usable.
	 * See comment https://www.php.net/manual/en/reserved.variables.files.php#121500 in the PHP manual.
	 * @param array $_files _FILES global variable
	 * @return array re-formatted _FILES array
	 */
	function reorganizeUploadFilesArray ( array $_files ) {
		$result = array();
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
	 * Run exiftool for a given collection and output to console in JSON format.
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

		// Reads all images in the given directory
		$target = INI[ 'Misc' ][ 'path_to_collections' ] . "/{$dir}/";

		exec(
			"{$perl} {$exift} {$command} {$target}" ,
			$output
		);

		return json_decode( implode( "" , $output ) );
	}

	/**
	 * Checks that uploaded file firstly uploade properly, and secondly is a valid image file.
	 * @param array $file
	 * @return bool True is valid file, false otherwise
	 */
	function checkUploadedFile ( array &$file ) {
		/*
		 * First part: Check for PHP upload errors.
		 * See https://www.php.net/manual/en/features.file-upload.errors.php for possible codes.
		 */
		if ( $file[ 'error' ] ) {
			return false;
		}

		/*
		 * Second part: checking for mimetype to see if image file.
		 * We don't use the $file['type'] because never trust user input!
		 *
		 * Checking for image mimetype using PHP finfo-extension, and regex.
		 * regex pattern: /(image\/)(\w+)/
		 *      (image\/)   string 'image/'
		 *      (\w+)       one or more word character
		 * So checks for mimetype "image/ *" basically. Could check for
		 * more specific mimetypes, but let's start with this.
		 */
		$file_mimetype = finfo_file(
			finfo_open( FILEINFO_MIME_TYPE ) ,
			$file[ 'tmp_name' ]
		);
		$file[ 'mimetype' ] = $file_mimetype;
		$regex_result = preg_match(
			'/(image\/)(\w+)/' ,
			$file_mimetype
		);

		if ( !$regex_result ) {
			// Overwrite the error variable, because we already checked it and it was 0.
			$file[ 'error' ] = -1;

			return false;
		}

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param int           $id
	 * @param array         $file
	 * @return int
	 */
	function addImageToDatabase ( DBConnection $db , int $id , array &$file ) {
		$hash = sha1_file( $file[ 'current_path' ] );
		$random_uid = $this->createRandomUID($db);
		$file['new_path'] = sprintf( "%s/%s/%s.%s" ,
             INI[ 'Misc' ][ 'path_to_collections' ] ,
             $id ,
             $random_uid ,
             pathinfo( $file[ 'name' ] , PATHINFO_EXTENSION )
		);

		$sql = "insert ignore into mymopsi_img 
	                ( collection_id, random_uid, name, original_name, filepath, 
	                 latitude, longitude, date_created, hash, size, mediatype ) 
				values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
		$values = [
			$id ,
			$this->createRandomUID($db) ,
			pathinfo( $file[ 'name' ] , PATHINFO_FILENAME ) ,
			pathinfo( $file[ 'name' ] , PATHINFO_FILENAME ) ,
			$file[ 'new_path' ] ,
			$file[ 'metadata' ]->GPSLatitude ?? null ,
			$file[ 'metadata' ]->GPSLongitude ?? null ,
			$file[ 'metadata' ]->Datecreate ?? null ,
			$hash ,
			$file[ 'size' ] ,
			$file[ 'mimetype' ]
		];

		$result = $db->query( $sql , $values );

		return $result
			? $db->getConnection()->lastInsertId()
			: 0;
	}

	/**
	 * Add new images to an existing collection.
	 * Also runs exiftool at the same time.
	 * @param DBConnection $db
	 * @param string        $uid
	 * @param array         $_files
	 */
	function addImagesToCollection ( DBConnection $db , string $uid, array $_files ) {
		// Organising _FILES global variable. The default structure is dumb and hard to use.
		$files = $this->reorganizeUploadFilesArray( $_files );

		// Let's make sure that no accidents happen because a variable is empty for some reason.
		if ( !$files or (strlen( INI[ 'Misc' ][ 'path_to_collections' ] ) < 20) ) {
			return;
		}

		$collection = Collection::fetchCollection( $db, $uid );
		$id = $collection->id;

		/*
		 * Create a temporary folder for running exiftool specific for this upload batch
		 */
		$new_temp_folder = "temp-{$id}-" . mt_rand( 0 , 10000 );
		mkdir( INI[ 'Misc' ][ 'path_to_collections' ] . "/{$new_temp_folder}/" );

		// For keeping track of failed uploads, and sending them back too.
		$errors = array();


		/*
		 * Moving files to temporary directory, while also dropping any uploaded files with errors.
		 * Not moving straight to final destination so that I won't run exiftool on already uploaded files.
		 */
		foreach ( $files as $index => &$file ) {
			if ( !$this->checkUploadedFile( $file ) ) {
				$errors[] = $file;
				unset( $files[ $index ] );
				continue;
			}

			$file[ 'current_path' ]	=
				sprintf( "%s/%s/%s.%s" ,
			         INI[ 'Misc' ][ 'path_to_collections' ] ,
			         $new_temp_folder ,
			         basename( $file[ 'tmp_name' ] ) ,
			         pathinfo( $file[ 'name' ] , PATHINFO_EXTENSION  ) );

			move_uploaded_file(
				$file[ 'tmp_name' ] ,
				$file[ 'current_path' ]
			);
		}

		unset( $file );

		/*
		 * Running exiftool for the directory made above.
		 * This is more efficient than running it individually, and avoids running for
		 * the whole collection, which are already in the database with GPS coordinates saved.
		 */
		/**
		 * @var $img_metadata \stdClass
		 *  SourceFile,
		 *  Main =>
		 *      GPSLatitudeRef,
		 *      GPSLatitude,
		 *      GPSLongitudeRef,
		 *      GPSLongitude,
		 *      ImageSize
		 */
		$img_metadata = $this->runExiftool( $new_temp_folder );

		/*
		 * Finally add images to database, and move to final storage location.
		 * Only valid, accepted images get moved.
		 */
		foreach ( $files as $key => &$file ) {
			$file[ 'metadata' ] = $img_metadata[ $key ]->Main ?? null;

			$image_id = $this->addImageToDatabase( $db , $id , $file );

			// If there is a duplicate, this is where it stops
			if ( !$image_id ) {
				$errors[] = $file;
				unlink( $file[ 'current_path' ] );
				unset( $files[ $key ] );
				continue;
			}

			rename(
				$file[ 'current_path' ] ,
				$file[ 'new_path' ]
			);
		}

		rmdir( INI[ 'Misc' ][ 'path_to_collections' ] . "/{$new_temp_folder}/" );

		// Pass on info on rejected/accepted images to the user.
		$this->result = [
			'success' => $files ,
			'errors' => $errors
		];
	}

	/**
	 * Returns all public collections from the database.
	 */
	function getPublicCollections () {
		//TODO: get all collections set public from database --jj190509
		// database call, get id, name, descr, size of collection (join)
		// return results
	}
}
