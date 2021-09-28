<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class UserControllerTest
 */
class UserControllerTest extends TestCase {

	protected $db;
	protected $ctrl;
	protected $testUser;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		empty_database_and_test_collections();
		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new UserController();
		$this->testUser = new User();
		$this->testUser->id = 1;
	}

	public function test_CreateEmptyUserRowInDatabase () {
		$new_user = $this->ctrl->createEmptyUserRowInDatabase( $this->db );

		self::assertInstanceOf( User::class, $new_user );
		self::assertIsInt( $new_user->id );
	}

	public function test_DeleteUserRowFromDatabase () {
		$temp_user = $this->ctrl->createEmptyUserRowInDatabase( $this->db );
		$result = $this->ctrl->deleteUserRowFromDatabase( $this->db, $temp_user );

		self::assertTrue( $result );
	}

	public function test_deleteAllCollectionsFromUser () {
		$this->ctrl->deleteAllCollectionsFromUser( $this->db, $this->testUser );
		$this->testUser->getCollections( $this->db );

		self::assertEmpty( $this->testUser->collections );
	}

	public function test_SetUsername () {
		$random_string = Common::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setUsername( $this->db, $this->testUser, $random_string );
		$user = User::fetchUserByID( $this->db, $this->testUser->id );

		self::assertTrue( $result );
		self::assertEquals( $random_string, $user->username );

		$result = $this->ctrl->setUsername( $this->db, $this->testUser, 'admin' );
	}

	public function test_SetPassword () {
		$hashed_password = password_hash( 'password', PASSWORD_DEFAULT );

		$result = $this->ctrl->setPassword( $this->db, $this->testUser, $hashed_password );

		self::assertTrue( $result );
	}

	public function test_SetPassword_Fail () {
		self::expectException( InvalidArgumentException::class );
		$this->ctrl->setPassword( $this->db, $this->testUser, 'password' );
	}

	public function test_SetEmail () {
		$random_string = Common::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setEmail( $this->db, $this->testUser, $random_string.'@email' );

		self::assertTrue( $result );
	}

	public function test_CheckUsernameAvailable () {
		$result = $this->ctrl->checkUsernameAvailable( $this->db, 'null_user' );
		self::assertTrue( $result );

		$result = $this->ctrl->checkUsernameAvailable( $this->db, 'admin' );
		self::assertFalse( $result );
	}

	public function test_addMopsiLinkToUser () {
		$result = $this->ctrl->addMopsiLinkToUser( $this->db, $this->testUser, 1 );

		self::assertTrue( $result );
	}

	public function test_RequestCreateNewUser () {
		$username = Common::createRandomUID( $this->db, 4, false );
		$password = 'password';

		$post = [
			'request' => 'new_user',
			'username' => $username,
			'password' => $password
		];

		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue( $this->ctrl->result['success'], print_r( $this->ctrl->result, true ) );
		self::assertFalse( $this->ctrl->result['error'], print_r( $this->ctrl->result, true ) );
		self::assertIsString( $this->ctrl->result['user_uid'] );
	}

	public function test_RequestCreateNewUser_Fail () {
		$result = $this->ctrl->requestCreateNewUser( $this->db,
			'admin', 'password' );
		self::assertFalse( $result, print_r( $this->ctrl->result, true ) );

		$result = $this->ctrl->requestCreateNewUser( $this->db,
			'admin', 'pass' );
		self::assertFalse( $result, print_r( $this->ctrl->result, true ) );
	}

	public function test_RequestUnifiedLogin_normal () {
		$post = [
			'request' => 'unified_login',
			'username' => 'admin',
			'password' => 'admin'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue( $this->ctrl->result['success'], print_r( $this->ctrl->result, true ) );
	}

	public function test_RequestUnifiedLogin_mopsi () {
		$post = [
			'request' => 'unified_login',
			'username' => 'test',
			'password' => 'test'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue( $this->ctrl->result['success'], print_r( $this->ctrl->result, true ) );
	}

	public function test_RequestChangeName () {
		$post_request = [
			'request' => 'edit_username',
			'username' => 'New name'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestChangePassword () {
		$post_request = [
			'request' => 'edit_password',
			'password' => 'New password'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestChangeEmail () {
		$post_request = [
			'request' => 'edit_email',
			'email' => 'new@email'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestDeleteUser () {
		$user = User::fetchUserByID( $this->db, $this->testUser->id );
		$post_request = [
			'request' => 'delete_user',
			'user' => $user->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		$user = User::fetchUserByID( $this->db, $this->testUser->id );
		self::assertNull( $user );
	}
}
