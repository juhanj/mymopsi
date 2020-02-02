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
			case 'new':
				$result = $this->requestCreateNewCollection( $db, $user, $req );
				break;
			case 'delete':
				$result = $this->requestDeleteCollection( $db, $user, $req );
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
	 * Create an empty stub collection in the database. Only has rows marked NOT NULL.
	 * Returns a copy of created collection.
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
			$ruid = Utils::createRandomUID( $db );
		}

		$db->query(
			'insert into mymopsi_collection (owner_id, random_uid) values (?,?)',
			[ $user->id, $ruid ]
		);

		$collection = Collection::fetchCollectionByID( $db, (int)$db->getConnection()->lastInsertId() );

		return $collection;
	}

	/**
	 * @param DBConnection $db
	 * @param Collection $collection
	 * @return bool
	 * @throws InvalidArgumentException if $collection has no ID
	 */
	public function deleteCollectionFromDatabase ( DBConnection $db, Collection $collection ): bool {
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
	public function setName ( DBConnection $db, Collection $collection, string $name ): bool {
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
	public function setDescription ( DBConnection $db, Collection $collection, string $description ): bool {
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
	public function setPublic ( DBConnection $db, Collection $collection, bool $value ): bool {
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
	public function setEditable ( DBConnection $db, Collection $collection, bool $value ): bool {
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
			$this->setError( -3, "User {$user->random_uid} does not own this collection" );
			return false;
		}

		if ( $collection->number_of_images !== 0 ) {
			$this->setError( -4,
				"Collection {$collection->random_uid} has {$collection->number_of_images} images in it."
				. " Cannot delete collections with images. Delete images first." );
			return false;
		}

		$result = $this->deleteCollectionFromDatabase( $db, $collection );

		if ( !$result ) {
			$this->setError( -5, "Collection could not be deleted." );
			return false;
		}

		$this->result = [
			'success' => true
		];

		return true;
	}
}
