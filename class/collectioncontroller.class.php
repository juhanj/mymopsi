<?php declare(strict_types=1);

/**
 * Class CollectionController
 */
class CollectionController implements Controller {

	public const KB = 1024;
	public const MB = 1048576;
	public const GB = 1073741824;

//	public const MIN_NAME_LENGTH = INI['Settings']['coll_name_min_len'];
	public const MAX_NAME_LENGTH = INI['Settings']['coll_name_max_len'];

//	public const MIN_DESCR_LENGTH = INI['Settings']['coll_descr_min_len'];
	public const MAX_DESCR_LENGTH = INI['Settings']['coll_descr_max_len'];

	/**
	 * @var mixed
	 */
	public $result = null;

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $req
	 */
	public function handleRequest ( DBConnection $db, User $user, array $req ) {
		switch ( $req['request'] ?? null ) {
			case 'new_collection':
				$result = $this->requestCreateNewCollection( $db, $user, $req );
				break;
			case 'delete_collection':
				$result = $this->requestDeleteCollection( $db, $user, $req );
				break;
			case 'edit_name':
				$result = $this->requestEditName( $db, $user, $req );
				break;
			case 'edit_description':
				$result = $this->requestEditDescription( $db, $user, $req );
				break;
			case 'edit_public':
				$result = $this->requestEditPublic( $db, $user, $req );
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
			'error' => true,
			'err' => $id,
			'errMsg' => $msg,
		];
	}

	/**
	 * Create an empty stub collection in the database. Only has rows 
	 * marked NOT NULL. Returns a copy of created collection.
	 * @param DBConnection $db
	 * @param User $user Must have ID
	 * @param string|null $ruid
	 * @return Collection|null
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	function createEmptyCollectionRowInDatabase ( DBConnection $db, User $user, string $ruid = null ): ?Collection {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		if ( is_null( $ruid ) ) {
			$ruid = Common::createRandomUID( $db );
		}

		$db->query(
			'insert into mymopsi_collection (owner_id, random_uid) values (?,?)',
			[ $user->id, $ruid ]
		);

		$collection = Collection::fetchCollectionByID( 
			$db,
			(int)$db->getConnection()->lastInsertId() 
		);

		return $collection;
	}

	/**
	 * Delete all images in the collection. Deletes the collection directory, and
	 * all image rows in database that belong to given collection.
	 * @param \DBConnection $db
	 * @param \Collection   $collection
	 *
	 * @return bool
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	function deleteAllImagesInCollection ( DBConnection $db, Collection $collection ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}

		// Sanity check, otherwise it will see no rows changed, and return false at end.
		if ( $collection->number_of_images === 0 ) {
			return true;
		}

		// Delete files
		Common::deleteFiles( INI['Misc']['path_to_collections'] . $collection->random_uid );

		// Delete from database
		$rows_changed = $db->query(
			'delete from mymopsi_img where collection_id = ? limit ?',
			[ $collection->id, $collection->number_of_images ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @return bool
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	function deleteCollectionFromDatabase ( DBConnection $db, Collection $collection ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}
		$rows_changed = $db->query(
			'delete from mymopsi_collection where id = ? limit 1',
			[ $collection->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection Must have ID
	 * @param string $name
	 * @return bool
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	function setName ( DBConnection $db, Collection $collection, string $name ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_collection set name = ? where id = ? limit 1',
			[ $name, $collection->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection Must have ID
	 * @param string $description
	 * @return bool
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	function setDescription ( DBConnection $db, Collection $collection, string $description ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_collection set description = ? where id = ? limit 1',
			[ $description, $collection->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @param bool $value
	 * @return bool
	 */
	function setPublic ( DBConnection $db, Collection $collection, bool $value ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_collection set public = ? where id = ? limit 1',
			[ $value, $collection->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @param bool $value
	 * @return bool
	 */
	function setEditable ( DBConnection $db, Collection $collection, bool $value ): bool {
		if ( is_null( $collection->id ) ) {
			throw new InvalidArgumentException( "Collection is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_collection set editable = ? where id = ? limit 1',
			[ $value, $collection->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * Create a JSON file with specific format for the server-side clustering API.
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @return bool|string
	 */
	function createServerClusteringJSON ( DBConnection $db, Collection $collection ): bool {
		$sql = "select random_uid as filename, name, latitude as lat, longitude as lon
				from mymopsi_img
				where collection_id = ?
					and latitude is not null
					and longitude is not null";
		$rows = $db->query( $sql, [ $collection->id ] );

		if ( !$rows ) {
			return false;
		}

		// Database returns an object, not array, if there is only one result
		if ( is_array( $rows ) ) {
			$rows = [ $rows ];
		}

		$file_path = INI['Misc']['path_to_collections'] 
			. "/{$collection->random_uid}/cluster-data.json";

		file_put_contents( $file_path, json_encode( $rows ) );

		return true;

		//	[
		//		{
		//			"lat": "-33.1545",
		//			"lon": "-118.384",
		//			"name": "",
		//			"filename": "131009_20-03-50_899419002.jpg"
		//		},
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public function requestCreateNewCollection ( DBConnection $db, User $user, array $options ): bool {
		if ( !$user ) {
			$this->setError( -1, 'User not valid' );
			return false;
		}

		$name = (!empty( $options['name'] ))
			? trim( mb_substr( $options['name'], 0, self::MAX_NAME_LENGTH ) )
			: null;
		$description = (!empty( $options['description'] ))
			? trim( mb_substr( $options['description'], 0, self::MAX_DESCR_LENGTH ) )
			: null;
		$public = isset( $options['public'] );
		$editable = isset( $options['editable'] );

		$new_coll = $this->createEmptyCollectionRowInDatabase( $db, $user );

		if ( !$new_coll ) {
			$this->setError( -2, 'Failed adding to database' );
			return false;
		}

		mkdir( INI['Misc']['path_to_collections'] . "/{$new_coll->random_uid}/" );

		if ( $name ) {
			$this->setName( $db, $new_coll, $name );
		}
		if ( $description ) {
			$this->setDescription( $db, $new_coll, $description );
		}
		if ( $public ) {
			$this->setPublic( $db, $new_coll, $public );
		}
		if ( $editable ) {
			$this->setEditable( $db, $new_coll, $editable );
		}

		$this->result = [
			'success' => true,
			'collection_id' => $new_coll->id,
			'collection_uid' => $new_coll->random_uid,
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public function requestDeleteCollection ( DBConnection $db, User $user, array $options ): bool {
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

		if ( $collection->owner_id !== $user->id and $user->admin == false ) {
			$this->setError( -3, "Authorization error" );
			return false;
		}

		$result = $this->deleteAllImagesInCollection( $db, $collection );

		if ( !$result ) {
			$this->setError( -4, "Could not delete images" );
			return false;
		}

		$result = $this->deleteCollectionFromDatabase( $db, $collection );

		if ( !$result ) {
			$this->setError( -5, "Collection could not be deleted." );
			return false;
		}

		$this->result = [
			'success' => true,
			'error' => false,
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public function requestEditName ( DBConnection $db, User $user, array $options ): bool {
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

		if ( $collection->owner_id !== $user->id and $user->admin == false ) {
			$this->setError( -3, "User {$user->random_uid} does not have access to this collection" );
			return false;
		}

		$new_name = $options['name'];

		if ( mb_strlen( $new_name ) < 1 or mb_strlen( $new_name ) > INI['Settings']['coll_name_max_len'] ) {
			$this->setError( -4, "New name {$new_name} length invalid" );
			return false;
		}

		$result = $this->setName( $db, $collection, $new_name );

		if ( !$result ) {
			$this->setError( -5, "Name could not be changed. Unknown error." );
			return false;
		}

		$this->result = [
			'success' => true
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public function requestEditDescription ( DBConnection $db, User $user, array $options ): bool {
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

		if ( $collection->owner_id !== $user->id and $user->admin == false ) {
			$this->setError( -3, "User {$user->random_uid} does not have access to this collection" );
			return false;
		}

		$new_descr = $options['description'] ?? '';

		if ( mb_strlen( $new_descr ) < 1 or mb_strlen( $new_descr ) > INI['Settings']['coll_descr_max_len'] ) {
			$this->setError( -4, "New description {$new_descr} length invalid" );
			return false;
		}

		$result = $this->setDescription( $db, $collection, $new_descr );

		if ( !$result ) {
			$this->setError( -5, "Description could not be changed. Unknown database error." );
			return false;
		}

		$this->result = [
			'success' => true
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $options
	 * @return bool
	 */
	public function requestEditPublic ( DBConnection $db, User $user, array $options ): bool {
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

		if ( $collection->owner_id !== $user->id and $user->admin == false ) {
			$this->setError( -3, "User {$user->random_uid} does not have access to this collection" );
			return false;
		}

		$public = $options['public'] ? boolval($options['public']) : false;

		$result = $this->setPublic( $db, $collection, $public );

		if ( !$result ) {
			$this->setError( -5, "Description could not be changed. Unknown database error." );
			return false;
		}

		$this->result = [
			'success' => true
		];

		return true;
	}
}
