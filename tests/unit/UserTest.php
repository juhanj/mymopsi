<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class UserTest
 */
class UserTest extends TestCase {

	protected $db;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		empty_database_and_test_collections();
		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
	}

	public function test_FetchUserByID () {
		$user = User::fetchUserByID( $this->db, 1 );

		self::assertEquals( 1, $user->id );
	}

	public function test_FetchUserByUsername () {
		$user = User::fetchUserByUsernameOrEmail( $this->db, 'admin' );

		self::assertEquals( 1, $user->id );
	}

	public function test_FetchUserByEmail () {
		$user = User::fetchUserByUsernameOrEmail( $this->db, 'admin@admin' );

		self::assertEquals( 1, $user->id );
	}

	public function test_FetchWithRUID () {
		$user = User::fetchUserByID( $this->db, 1 );
		$user = User::fetchUserByRUID( $this->db, $user->random_uid );

		self::assertEquals( 1, $user->id, print_r($user,true) );
	}

	public function test_FetchUserFail () {
		$user = User::fetchUserByID( $this->db, (int)null );

		self::assertNull( $user );
	}

	public function test_fetchAllUsers () {
		$users = User::fetchAllUsers( $this->db );

		self::assertIsArray( $users );
		self::assertInstanceOf( 'User', $users[0] );
	}

	public function test_GetCollections () {
		$user = User::fetchUserByID( $this->db, 1 );

		$user->getCollections( $this->db );

		self::assertIsArray( $user->collections );
		self::assertGreaterThanOrEqual(
			2,
			count($user->collections)
		);
	}
}
