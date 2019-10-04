<?php declare(strict_types=1);

/**
 * Class UserController
 */
class UserController implements Controller {

	const MAX_USERNAME_LENGTH = 190;
	const MIN_PASSWORD_LENGTH = 8;
	const MAX_PASSWORD_LENGTH = 300;

	/**
	 * @var null Used for returning results to Ajax-requests.
	 */
	public $result = null;

	/**
	 * @param $db
	 * @param $req
	 */
	public function handleRequest ( $db, $req ) {
		switch ( $req['method'] ) {
			case 'login':
				$this->login( $db, $req['user'], $req['password'] );
				break;
			case 'mopsiLogin':
				$this->mopsiLogin( $db, $req['user'], $req['password'] );
				break;
			default:
				$this->result = [ 'success' => false ];
		}
	}

	/**
	 * @param DBConnection $db
	 * @param $username
	 * @return bool true if available
	 */
	private function checkUsernameAvailable ( DBConnection $db, $username ) {
		return !$db->query(
			'select 1 from mymopsi_user where username = ? limit 1',
			[ $username ]
		);
	}

	/**
	 * @param DBConnection $db
	 * @param $ruid
	 * @return bool true if available
	 */
	private function checkRandomUIDAvailable ( DBConnection $db, $ruid ) {
		return !$db->query(
			'select 1 from mymopsi_user where random_uid = ? limit 1',
			[ $ruid ]
		);
	}

	/**
	 * Returns a unique, random 20 character string
	 * @param DBConnection $db
	 * @return string Random unique 20 character identifier
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
	 * Create a new user in the database. Returns said user.
	 * @param DBConnection $db
	 * @param string $username
	 * @return User|null
	 */
	function createUser ( DBConnection $db, string $username ) {
		$random_uid = $this->createRandomUID( $db );
		$db->query(
			'insert into mymopsi_user (random_uid, username) values (?,?)',
			[ $random_uid, $username ]
		);

		$user = User::fetchUser( $db, $db->getConnection()->lastInsertId() );

		return $user;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param $newName
	 */
	function updateUsername ( DBConnection $db, User $user, $newName ) {
		$db->query(
			'update mymopsi_user set username = ? where id = ? limit 1',
			[ $newName, $user->id ]
		);
	}

	/**
	 * Updated password in the database. Also checks if password needs rehashing with password_needs_rehash,
	 * so it works if $password argument is either unhashed or hashed.
	 * @param DBConnection $db
	 * @param User $user - Must have ID
	 * @param string $password - Unhashed or hashed. Saved to database in hashed form.
	 */
	function updatePassword ( DBConnection $db, User $user, string $password ) {
		if ( password_needs_rehash( $password, PASSWORD_DEFAULT ) ) {
			$newHash = password_hash( $password, PASSWORD_DEFAULT );
		} else {
			$newHash = $password;
		}
		$db->query(
			'update mymopsi_user set password = ? where id = ? limit 1',
			[ $user->id, $newHash ]
		);
	}

	/**
	 * @param DBConnection $db
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	function createNewUser ( DBConnection $db, string $username, string $password ) {
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < self::MIN_PASSWORD_LENGTH
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->result = -1;
			return false;
		}

		if ( !$this->checkUsernameAvailable( $db, $username ) ) {
			$this->result = -2;
			return false;
		}

		$user = $this->createUser( $db, $username );

		if ( $user ) {
			$this->result = -3;
			return false;
		}

		$this->updatePassword( $db, $user, $password );

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param string $password
	 * @return bool
	 */
	function checkPassword ( DBConnection $db, User $user, string $password ) {
		if ( $user->type === 1 ) {
			// Here would happen updating the password hash in the database
			// I don't have the mopsi login hashing function, nor do I really want to add it here.
			// (The hashing function needed to check that the password is correct.)
			// This code here just for presentation.

//			if ( $this->verifyUnsecureOldPasswordHash( $password ) ) {
//				 $this->updatePassword( $db, $row, $password );
//			}

		} elseif ( $user->type === 2 ) {
			if ( password_verify( $password, $user->password ) ) {
				if ( password_needs_rehash( $user->password, PASSWORD_DEFAULT ) ) {
					$this->updatePassword( $db, $user, $password );
				}
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	function login ( DBConnection $db, string $username, string $password ) {
		$username = trim( $username );
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < self::MIN_PASSWORD_LENGTH
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->result = -1;
			return false;
		}

		$user = User::fetchUser( $db, $username );

		if ( !$user ) {
			$this->result = -4;
			return false;
		}

		if ( $this->checkPassword( $db, $user, $password ) ) {
			$_SESSION['user_id'] = $user->id;
			$this->result = [
				'success' => true,
				'user_id' => $user->id
			];
		} else {
			$this->result = -5;
			return false;
		}

		return true;
	}

	/**
	 * @param DBConnection $db
	 * @param string $username
	 * @param string $password
	 */
	function mopsiLogin ( DBConnection $db, string $username, string $password ) {
		$username = trim( $username );
		$usernameLength = strlen( $username );
		$passwordLength = strlen( $password );

		if ( $usernameLength > self::MAX_USERNAME_LENGTH
			or $passwordLength < 1 // I don't know the Mopsi password rules, not really my problem
			or $passwordLength > self::MAX_PASSWORD_LENGTH ) {
			$this->result = -1;
			return false;
		}

		$postData = [
			'username' => $username,
			'password' => $password,
			'request_type' => 'user_login',
		];
		$jsonData = json_encode( $postData );

		$curlHandle = curl_init();

		$curlOptions = [
			CURLOPT_URL => "https://cs.uef.fi/mopsi/mobile/server.php",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => ["param"=>$jsonData],
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

		if ( $response->message !== -1
			and $response->id !== -1
		 	and $response->error === null ) {

			$user = User::fetchUser( $db, $username );

			// Mopsi user found
			// Check for mymopsi account
			// If found: log in
			// If no a
		}

		$this->result = [
			'success' =>'true',
			'response' => $response
		];

		$user = User::fetchUser( $db, $username );

	}
}
