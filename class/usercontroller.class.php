<?php declare(strict_types=1);

/**
 * Class UserController
 */
class UserController implements Controller {

	const MIN_USERNAME_LENGTH = INI['Settings']['username_min_len'];
	const MAX_USERNAME_LENGTH = INI['Settings']['username_max_len'];

	const MIN_PASSWORD_LENGTH = INI['Settings']['password_min_len'];
	const MAX_PASSWORD_LENGTH = INI['Settings']['password_max_len'];

	/**
	 * @var null Used for returning results to Ajax-requests.
	 */
	public $result = null;

	/**
	 * @param DBConnection $db
	 * @param User|null $user
	 * @param array $req
	 */
	public function handleRequest ( DBConnection $db, $user, array $req ) {
		switch ( $req['method'] ) {
			case 'login':
				$this->requestLogin( $db, $req['user'], $req['password'] );
				break;
			case 'mopsiLogin':
				$this->mopsiLogin( $db, $req['user'], $req['password'] );
				break;
			default:
				$this->setError( -99, 'Invalid Request' );
		}
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
	 * Create an empty stub user in the database. Only has rows marked NOT NULL.
	 * @param DBConnection $db
	 * @param string|null $ruid
	 * @return User|null
	 */
	function createEmptyUserRowInDatabase ( DBConnection $db, string $ruid = null ): ?User {
		if ( is_null( $ruid ) ) {
			$ruid = Utils::createRandomUID( $db ); // compoments/helper-functions.php
		}

		$db->query(
			'insert into mymopsi_user (random_uid) values (?)',
			[ $ruid ]
		);

		$user = User::fetchUserByID( $db, (int)$db->getConnection()->lastInsertId() );

		return $user;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
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
	 * @param User $user
	 * @param string|null $newName
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
	 * @param DBConnection $db
	 * @param User $user - Must have ID
	 * @param string $hashed_password Must be hashed by password_hash, and be up to date by password_needs_rehash.
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
	 * @param User $user
	 * @param string $newEmail
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
	 * @param $username
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
	 * @param User $user Must have type and hashed password
	 * @param string $password Clear text password, from form POST
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

		} elseif ( $user->type === 2 ) {
			$password_correct = password_verify( $password, $user->password );

			if ( $password_correct and password_needs_rehash( $user->password, PASSWORD_DEFAULT ) ) {
				$hashed_password = password_hash( $password, PASSWORD_DEFAULT );
				$this->setPassword( $db, $user, $hashed_password );
			}
		} else {
			$password_correct = false;
		}

		return boolval( $password_correct );
	}

	/**
	 * Add a link between mopsi and mymopsi accounts in the database. Will update on duplicate key.
	 * @param DBConnection $db
	 * @param User $user
	 * @param int $mopsiID
	 * @return bool
	 */
	function addMopsiLinkToUser ( DBConnection $db, User $user, int $mopsiID ): bool {
		if ( is_null( $user->id ) ) {
			throw new InvalidArgumentException( "User is not valid." );
		}
		$result = $db->query(
			'insert into mymopsi_user_third_party_link (user_id, mopsi_id) 
				values (?,?) 
				on duplicate key update mopsi_id = value(mopsi_id)',
			[ $user->id, $mopsiID ]
		);
		return boolval( $result );
	}

	/**
	 * @param DBConnection $db
	 * @param string $username From form POST
	 * @param string $password Clear text password, from form POST
	 * @return bool
	 */
	function requestCreateNewUser ( DBConnection $db, string $username, string $password ) {
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

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param string $username From fornm POST
	 * @param string $password Clear text password, from form POST
	 * @return bool
	 */
	function requestLogin ( DBConnection $db, string $username, string $password ) {
		$username = trim( $username );
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength < self::MIN_USERNAME_LENGTH
			or $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < self::MIN_PASSWORD_LENGTH
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->setError( -1, 'Username or password length wrong' );
			return false;
		}

		$user = User::fetchUserByUsernameOrEmail( $db, $username );

		if ( !$user ) {
			$this->setError( -2, "Username '{$username}' not found" );
			return false;
		}

		$password_correct = $this->checkPasswordOnLogin( $db, $user, $password );

		if ( !$password_correct ) {
			$this->setError( -3, 'Password wrong' );
			return false;
		}

		$this->result = [
			'success' => true,
			'user_id' => $user->id,
		];

		return true;
	}

	/**
	 * //TODO
	 * @param DBConnection $db [description]
	 * @param string $username [description]
	 * @param string $password [description]
	 * @return [type]                 [description]
	 */
	function requestMopsiLogin ( DBConnection $db, string $username, string $password ) {
		$username = trim( $username );
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < 1 // I don't know the Mopsi password rules, not really my problem
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->setError( -1, 'Username or password length wrong' );
			return false;
		}

		$postData = [
			'username' => $username,
			'password' => $password,
			'request_type' => 'user_login',
		];
		$jsonData = json_encode( $postData );
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

			$this->setError( -2, 'Mopsi says: ' . $response->error );
			return false;
		}

		/*
		 * We have a Mopsi-user. Now let's find the linked mymopsi-user,
		 * or create a new one.
		 */

		$row = $db->query(
			'select user_id from mymopsi_user_third_party_link where mopsi_id = ?',
			[ $response->id ]
		);

		if ( !$row ) {
			// Create new empty MyMopsi user, so the site has something to refer to.
			$user = $this->createEmptyUserRowInDatabase( $db );
			$this->addMopsiLinkToUser( $db, $user, (int)$response->id );
		}
		else {
			$user = new User();
			$user->id = $row->user_id;
		}

		// If found: log in
		$_SESSION['user_id'] = $user->id;
		$_SESSION['username'] = $response->username;

		// If not, create a new stub user and log that in
		$this->result = [
			'success' => true,
			'response' => $response
		];

		return true;
	}

	/**
	 * //TODO
	 * @param DBConnection $db [description]
	 * @param User $user [description]
	 * @return [type]             [description]
	 */
	function requestDeleteUser ( DBConnection $db, User $user ) {
	}

	/**
	 * //TODO
	 * @param DBConnection $db [description]
	 * @param User $user [description]
	 * @param string $new_password [description]
	 * @return [type]                     [description]
	 */
	function requestPasswordChange ( DBConnection $db, User $user, string $new_password ) {
	}

	/**
	 * //TODO
	 * @param DBConnection $db [description]
	 * @param User $user [description]
	 * @param string $new_username [description]
	 * @return [type]                     [description]
	 */
	function requestUsernameChange ( DBConnection $db, User $user, string $new_username ) {
	}

	/**
	 * //TODO
	 * @param DBConnection $db [description]
	 * @param User $user [description]
	 * @param string $new_email [description]
	 * @return [type]                     [description]
	 */
	function requestEmailChange ( DBConnection $db, User $user, string $new_email ) {
	}
}
