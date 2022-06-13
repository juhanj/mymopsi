<?php declare(strict_types=1);

/**
 * Class UserController
 */
class UserController implements Controller {

	const MIN_USERNAME_LENGTH = INI[ 'Settings' ][ 'username_min_len' ];
	const MAX_USERNAME_LENGTH = INI[ 'Settings' ][ 'username_max_len' ];

	const MIN_PASSWORD_LENGTH = INI[ 'Settings' ][ 'password_min_len' ];
	const MAX_PASSWORD_LENGTH = INI[ 'Settings' ][ 'password_max_len' ];

	/**
	 * @var array Used for returning results to Ajax-requests.
	 */
	public $result;

	/**
	 * @param DBConnection $db
	 * @param User|null    $user
	 * @param array        $req
	 */
	public function handleRequest ( DBConnection $db, $user, array $req ) {
		switch ( $req[ 'request' ] ) {
			case 'new_user':
				$this->requestCreateNewUser( $db, $req[ 'username' ], $req[ 'password' ] );
				break;
			case 'unified_login':
				$this->requestUnifiedLogin( $db, $req );
				break;
			case 'edit_username':
				$this->requestChangeUsername( $db, $user, $req[ 'username' ] );
				break;
			case 'edit_password':
				$this->requestChangePassword( $db, $user, $req[ 'password' ] );
				break;
			case 'edit_email':
				$this->requestChangePassword( $db, $user, $req[ 'email' ] );
				break;
			case 'delete_user':
				$this->requestDeleteUser( $db, $user, $req );
				break;
			default:
				$this->setError( -99, 'Invalid Request' );
		}
	}

	/**
	 * @param int    $id
	 * @param string $msg
	 */
	public function setError ( int $id, string $msg ) {
		$this->result[ 'success' ] = false;
		$this->result[ 'error' ] = true;

		$this->result[ 'errors' ][] = [
			'id' => $id,
			'msg' => $msg,
		];

	}

	/**
	 * Create an empty stub user in the database. Only has rows marked NOT NULL.
	 *
	 * @param DBConnection $db
	 * @param string|null  $ruid
	 *
	 * @return User|null
	 */
	function createEmptyUserRowInDatabase ( DBConnection $db, string $ruid = null ): ?User {
		if ( is_null( $ruid ) ) {
			$ruid = Common::createRandomUID( $db ); // compoments/helper-functions.php
		}

		$db->query(
			'insert into mymopsi_user (random_uid) values (?)',
			[ $ruid ]
		);

		return User::fetchUserByID( $db, (int)$db->getConnection()->lastInsertId() );
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 *
	 * @return bool
	 */
	function deleteUserRowFromDatabase ( DBConnection $db, User $user ): bool {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		$rows_changed = $db->query(
			'delete from mymopsi_user where id = ? limit 1',
			[ $user->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 */
	function deleteAllCollectionsFromUser ( DBConnection $db, User $user ) {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}

		$collectionController = new CollectionController();

		$user->getCollections( $db );

		foreach ( $user->collections as $collection ) {
			$collectionController->deleteAllImagesInCollection( $db, $collection );
			$collectionController->deleteCollectionFromDatabase( $db, $collection );
		}
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 * @param string|null  $newName
	 *
	 * @return boolean
	 */
	function setUsername ( DBConnection $db, User $user, $newName ): bool {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_user set username = ? where id = ? limit 1',
			[ $newName, $user->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * Updated password in the database. Returns false if user not found or password not valid hash.
	 *
	 * @param DBConnection $db
	 * @param User         $user            - Must have ID
	 * @param string       $hashed_password Must be hashed by password_hash, and be up to date by password_needs_rehash.
	 *
	 * @return bool Returns false if no rows changed (Either user not found, or password needs updating)
	 */
	function setPassword ( DBConnection $db, User $user, string $hashed_password ) {
		if ( password_needs_rehash( $hashed_password, PASSWORD_DEFAULT ) ) {
			throw new InvalidArgumentException( "Password not valid. Either not hashed or need rehashing." );
		}
		$rows_changed = $db->query(
			'update mymopsi_user set password = ? where id = ? limit 1',
			[ $hashed_password, $user->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 * @param string       $newEmail
	 *
	 * @return bool
	 */
	function setEmail ( DBConnection $db, User $user, string $newEmail ): bool {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		$rows_changed = $db->query(
			'update mymopsi_user set email = ? where id = ? limit 1',
			[ $newEmail, $user->id ]
		);

		return boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param              $username
	 *
	 * @return bool true if available
	 */
	function checkUsernameAvailable ( DBConnection $db, string $username ): bool {
		$rows_changed = $db->query(
			'select 1 from mymopsi_user where username = ? limit 1',
			[ $username ]
		);

		return !boolval( $rows_changed );
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user     Must have type and hashed password
	 * @param string       $password Clear text password, from form POST
	 *
	 * @return bool
	 */
	function checkPasswordOnLogin ( DBConnection $db, User $user, string $password ): bool {
		if ( is_null( $user->password ) ) {
			throw new InvalidArgumentException( "User is not valid. No password found." );
		}
		if ( $user->type === 1 ) {
			// Here would happen updating the password hash in the database
			// I don't have the mopsi login hashing function, nor do I really want to add it here.
			// (The hashing function needed to check that the password is correct.)
			// This code here just for presentation.

			//if ( $this->verifyUnsecureOldPasswordHash( $password ) ) {
			//	$this->updatePassword( $db, $row, $password );
			//}
			return false;

		}
		else if ( $user->type === 2 ) {
			$password_correct = password_verify( $password, $user->password );

			if ( $password_correct and password_needs_rehash( $user->password, PASSWORD_DEFAULT ) ) {
				$hashed_password = password_hash( $password, PASSWORD_DEFAULT );
				$this->setPassword( $db, $user, $hashed_password );
			}
		}
		else {
			$password_correct = false;
		}

		return boolval( $password_correct );
	}

	/**
	 * Add a link between mopsi and mymopsi accounts in the database. Will update on duplicate key.
	 *
	 * @param DBConnection $db
	 * @param User         $user
	 * @param int          $mopsiID
	 *
	 * @return bool
	 */
	function addMopsiLinkToUser ( DBConnection $db, User $user, int $mopsiID ): bool {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		$result = $db->query(
			'insert into mymopsi_user_third_party_link (user_id, mopsi_id)
				values (?,?)
				on duplicate key update mopsi_id = values(mopsi_id)',
			[ $user->id, $mopsiID ]
		);

		return boolval( $result );
	}

	/**
	 * Check normal Mopsi account login
	 *
	 * @param DBConnection $db
	 * @param string       $username
	 * @param string       $password
	 *
	 * @return User|null
	 */
	function normalLogin ( DBConnection $db, string $username, string $password ): ?User {
		if ( strlen( $username ) < 1
			or strlen( $username ) > self::MAX_USERNAME_LENGTH
			or strlen( $password ) < 1
			or strlen( $password ) > self::MAX_PASSWORD_LENGTH ) {
			$this->setError( -1, 'Username or password length wrong' );

			return null;
		}

		$user = User::fetchUserByUsernameOrEmail( $db, $username );

		if ( $user ) {
			if ( !$this->checkPasswordOnLogin( $db, $user, $password ) ) {
				$this->setError( -3, 'Password wrong' );

				return null;
			}
		}
		else {
			$this->setError( -2, "No MyMopsi user found in database" );

			return null;
		}

		return $user;
	}

	/**
	 * @param DBConnection $db
	 * @param string       $username
	 * @param string       $password
	 *
	 * @return User|null
	 */
	function mopsiLogin ( DBConnection $db, string $username, string $password ): ?User {
		if ( strlen( $username ) > self::MAX_USERNAME_LENGTH
			or strlen( $password ) < 1
			or strlen( $password ) > self::MAX_PASSWORD_LENGTH ) {
			$this->setError( -1, 'Username or password length wrong' );

			return null;
		}

		$jsonData = json_encode( [
			'username' => $username,
			'password' => $password,
			'request_type' => 'user_login',
		] );
		// We send it as a POST-request, but Mopsi-server still wants the data in JSON

		$curlHandle = curl_init();

		$curlOptions = [
			CURLOPT_URL => "https://cs.uef.fi/mopsi/mobile/server.php",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => [ "param" => $jsonData ],
			// Mopsi server wants the "param", and the JSON, in this specific format
			// Not my fault.
		];

		curl_setopt_array(
			$curlHandle,
			$curlOptions
		);

		$responseJSON = curl_exec( $curlHandle );
		$response = json_decode( $responseJSON );

		curl_close( $curlHandle );

		if ( $response->message === -1
			and $response->id === -1
			and $response->error !== null ) {
			$this->setError( -2, "Mopsi server error, no user found probably" );

			return null;
		}

		// We have a Mopsi-user. Now let's find the linked mymopsi-user ...

		$row = $db->query(
			'select user_id from mymopsi_user_third_party_link where mopsi_id = ?',
			[ $response->id ]
		);

		// ... or create a new one if none found above.

		if ( !$row ) {
			// Create new empty MyMopsi user, so the site has something to refer to.
			$user = $this->createEmptyUserRowInDatabase( $db );

			// Do not create a username or password for new user.
			// Breaks login process.

			$this->addMopsiLinkToUser( $db, $user, (int)$response->id );
		}
		else {
			$user = User::fetchUserByID( $db, $row->user_id );
		}

		return $user;
	}

	/**
	 * @param DBConnection $db
	 * @param string       $username From form POST
	 * @param string       $password Clear text password, from form POST
	 *
	 * @return bool
	 */
	public function requestCreateNewUser ( DBConnection $db, string $username, string $password ) {
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength < self::MIN_USERNAME_LENGTH
			or $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < self::MIN_PASSWORD_LENGTH
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->setError( -1, 'Username or password length wrong' );

			return false;
		}

		if ( !$this->checkUsernameAvailable( $db, $username ) ) {
			$this->setError( -2, "Username '{$username}' not available" );

			return false;
		}

		$user = $this->createEmptyUserRowInDatabase( $db );
		$this->setUsername( $db, $user, $username );

		if ( !$user ) {
			$this->setError( -3, 'Could not add user to database' );

			return false;
		}

		$hashed_password = password_hash( $password, PASSWORD_DEFAULT );
		$this->setPassword( $db, $user, $hashed_password );

		$this->result = [
			'success' => true,
			'error' => false,
			'user_uid' => $user->random_uid,
		];

		return true;
	}

	/**
	 * Log in functionality for Mopsi/MyMopsi account both.
	 * (And possibly others if ever implemented.)
	 *
	 * @param DBConnection $db
	 * @param array        $options
	 *
	 * @return bool
	 */
	public function requestUnifiedLogin ( DBConnection $db, array $options ) {
		// Sanity check $options for empty fields
		if ( empty( $options[ 'username' ] ) or empty( $options[ 'password' ] ) ) {
			$this->setError( -1, "Invalid param" );

			return false;
		}

		// I don't care about empty spaces at either end of string.
		// Assume it's a user mistake, and remove.
		$options[ 'username' ] = trim( $options[ 'username' ] );
		// I don't do the same for the password, because there I consider it
		//  a valid character just like any other. If a user wants to end
		//  their password with five spaces, that's their problem.

		// Check if either login returns a valid user.
		$user = $this->normalLogin( $db, $options[ 'username' ], $options[ 'password' ] )
			?? $this->mopsiLogin( $db, $options[ 'username' ], $options[ 'password' ] );

		// if both above fail return error
		if ( !$user ) {
			$this->setError( -2, "No MyMopsi or Mopsi user found" );

			return false;
		}

		$this->result = [
			'success' => true,
			'error' => false,
			'user_id' => $user->id,
		];

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User         $user
	 * @param string       $new_name
	 *
	 * @return bool
	 */
	public function requestChangeUsername ( DBConnection $db, User $user, string $new_name ) {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		$usernameLength = strlen( $new_name );

		if ( $usernameLength < self::MIN_USERNAME_LENGTH
			or $usernameLength > self::MAX_USERNAME_LENGTH ) {
			$this->setError( -2, 'Username length wrong' );

			return false;
		}

		if ( !$this->checkUsernameAvailable( $db, $new_name ) ) {
			$this->setError( -3, "Username '{$new_name}' not available" );

			return false;
		}

		$result = $this->setUsername( $db, $user, $new_name );

		if ( !$result ) {
			$this->setError( -3, 'Something went wrong, could not edit DB' );

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
	 * @param User         $user
	 * @param string       $new_password
	 *
	 * @return bool
	 */
	public function requestChangePassword ( DBConnection $db, User $user, string $new_password ) {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		if ( strlen( $new_password ) < self::MIN_USERNAME_LENGTH
			or strlen( $new_password ) > self::MAX_USERNAME_LENGTH ) {
			$this->setError( -2, 'Password length wrong' );

			return false;
		}

		$result = $this->setPassword( $db, $user, password_hash( $new_password, PASSWORD_DEFAULT ) );

		if ( !$result ) {
			$this->setError( -3, 'Something went wrong, could not edit DB' );

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
	 * @param User         $user
	 * @param string       $new_email
	 *
	 * @return bool
	 */
	public function requestChangeEmail ( DBConnection $db, User $user, string $new_email ) {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		//TODO: Email length should probably have some kind of check for sanity

		$result = $this->setEmail( $db, $user, $new_email );

		if ( !$result ) {
			$this->setError( -2, 'Something went wrong, could not edit DB' );

			return false;
		}

		$this->result = [
			'success' => true,
			'error' => false,
		];

		return true;
	}

	/**
	 * @param \DBConnection $db
	 * @param \User         $user
	 * @param               $options
	 *
	 * @return bool
	 */
	public function requestDeleteUser ( DBConnection $db, User $user, $options ) {
		if ( !$user->id ) {
			$this->setError( -1, 'User not valid' );

			return false;
		}

		$this->deleteAllCollectionsFromUser( $db, $user );

		$this->deleteUserRowFromDatabase( $db, $user );

		$this->result = [
			'success' => true,
			'error' => false,
		];

		return true;
	}

}
