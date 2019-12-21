<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

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

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new UserController();
		$this->testUser = new User();
		$this->testUser->id = 2;
	}

	public function test_CreateEmptyUserRowInDatabase () {
		$new_user = $this->ctrl->createEmptyUserRowInDatabase( $this->db );

		self::assertInstanceOf( User::class, $new_user );
		self::assertIsInt( $new_user->id );
	}

	public function test_DeleteUserRowFromDatabase () {
		$result = $this->ctrl->deleteUserRowFromDatabase( $this->db, $this->testUser );

		self::assertTrue( $result );
	}

	public function test_SetUsername () {
		$random_string = Utils::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setUsername( $this->db, $this->testUser, $random_string );
		$user = User::fetchUserByID( $this->db, $this->testUser->id );

		self::assertTrue( $result );
		self::assertEquals( $random_string, $user->username );
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
		$random_string = Utils::createRandomUID( $this->db, 4, false );

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
		$username = Utils::createRandomUID( $this->db, 4, false );
		$password = 'password';

		$post = [
			'request' => 'new',
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

	public function test_RequestLogin () {
		$post = [
			'request' => 'login',
			'username' => 'admin',
			'password' => 'password'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue( $this->ctrl->result['success'], print_r( $this->ctrl->result, true ) );
		self::assertFalse( $this->ctrl->result['error'], print_r( $this->ctrl->result, true ) );
	}

	public function test_RequestLogin_Fail_PasswordUsernameLength () {
		$result = $this->ctrl->requestLogin( $this->db, 'admin', 'passw' );
		self::assertFalse( $result );
		self::assertEquals( -1, $this->ctrl->result['err'] );

		$result = $this->ctrl->requestLogin( $this->db, '', 'password123' );
		self::assertFalse( $result );
		self::assertEquals( -1, $this->ctrl->result['err'] );
	}

	public function test_RequestLogin_Fail_UserNotFound () {
		$result = $this->ctrl->requestLogin( $this->db, 'test_user_not_found', '12345678' );
		self::assertFalse( $result );
		self::assertEquals( -2, $this->ctrl->result['err'] );
	}

	public function test_RequestLogin_Fail_WrongPassword () {
		$result = $this->ctrl->requestLogin( $this->db, 'admin', 'password123' );
		self::assertFalse( $result, print_r( $this->ctrl->result, true ) );
		self::assertEquals( -3, $this->ctrl->result['err'] );
	}

	public function test_RequestMopsiLogin () {
		$post = [
			'request' => 'mopsi_login',
			'username' => 'test',
			'password' => 'test'
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue( $this->ctrl->result['success'], print_r( $this->ctrl->result, true ) );
		self::assertFalse( $this->ctrl->result['error'], print_r( $this->ctrl->result, true ) );

		self::assertIsInt( $_SESSION['user_id'] );
	}

	public function test_RequestMopsiLogin_fail_UserNotFound () {
		$result = $this->ctrl->requestMopsiLogin( $this->db, 'test', 'fail' );

		self::assertFalse( $result, print_r($this->ctrl->result, true) );
		self::assertTrue( $this->ctrl->result['error'] );
	}
}
