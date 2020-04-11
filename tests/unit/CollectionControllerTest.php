<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class CollectionControllerTest
 */
class CollectionControllerTest extends TestCase {

	protected $db;
	protected $ctrl;
	protected $testCollection;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new CollectionController();
		$this->testCollection = new Collection();
		$this->testCollection->id = 1;
	}

	public function test_CreateEmptyCollectionRowInDatabase () {
		$user = new User();
		$user->id = 1;

		$result = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $user );

		self::assertInstanceOf( Collection::class, $result );
	}

	public function test_DeleteCollectionFromDatabase () {
		$user = new User();
		$user->id = 1;
		$new_coll = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $user );

		$result = $this->ctrl->deleteCollectionFromDatabase( $this->db, $new_coll );

		self::assertTrue( $result );
	}

	public function test_SetName () {
		$random_string = Utils::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setName( $this->db, $this->testCollection, $random_string );
		$coll = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		self::assertTrue( $result );
		self::assertEquals( $random_string, $coll->name );
	}

	public function test_SetDescription () {
		$random_string = Utils::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setDescription( $this->db, $this->testCollection, $random_string );
		$coll = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		self::assertTrue( $result );
		self::assertEquals( $random_string, $coll->description );
	}

	public function test_SetPublic () {
		$result = $this->ctrl->setPublic( $this->db, $this->testCollection, true );

		self::assertTrue( $result );
	}

	public function test_SetEditable () {
		$result = $this->ctrl->setEditable( $this->db, $this->testCollection, true );

		self::assertTrue( $result );
	}

	public function test_CreateServerClusteringJSON () {
		$collection = Collection::fetchCollectionByID($this->db,1);
		$result = $this->ctrl->createServerClusteringJSON( $this->db, $collection );

		self::assertTrue( $result );
	}

	public function test_CreateServerClusteringJSON_Fail_NoImages () {
		$collection = Collection::fetchCollectionByID($this->db,2);
		$result = $this->ctrl->createServerClusteringJSON( $this->db, $collection );

		echo $result;

		self::assertFalse( $result, print_r($result,true) );
	}

	public function test_requestCreateNewCollection () {
		$user = User::fetchUserByID( $this->db, 1 );
		$post_request = [
			'request' => 'new',
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_requestCreateNewCollection_WithOptions () {
		$user = User::fetchUserByID( $this->db, 1 );
		$post_request = [
			'request' => 'new',
			'name' => 'Foo',
			'description' => 'Bar',
			'public' => 'on',
			'editable' => 'on',
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestDeleteCollection () {
		$user = new User();
		$user->id = 1;
		$collection = Collection::fetchCollectionByID( $this->db, 2 );

		$post_request = [
			'collection' => $collection->random_uid,
			'request' => 'delete',
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestDeleteCollection_Fail_InvalidUser () {
		$user = new User();
		$post = [
			'request' => 'delete'
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertEquals(
			-1,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestDeleteCollection_Fail_InvalidCollection () {
		$user = new User();
		$user->id = 1;
		$post = [
			'request' => 'delete',
			'collection' => '1' // random uid expected
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertEquals(
			-2,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestDeleteCollection_Fail_InvalidOwner () {
		$user = User::fetchUserByID( $this->db, 2 );
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post = [
			'request' => 'delete',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertEquals(
			-3,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestDeleteCollection_Fail_WithImages () {
		$user = new User();
		$user->id = 1;
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post = [
			'request' => 'delete',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertEquals(
			-4,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestEditName () {
		$user = User::fetchUserByID( $this->db, 1 );
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_name',
			'name' => 'New name',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestEditDescription () {
		$user = User::fetchUserByID( $this->db, 1 );
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_description',
			'description' => 'New description',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestEditPublic () {
		$user = User::fetchUserByID( $this->db, 1 );
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_public',
			'public' => true,
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}
}
