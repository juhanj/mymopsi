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

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new CollectionController();
	}

	public function testControllerAddingCollectionToDatabase () {
		$user = new User();
		$user->id = 1;

		$result = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $user );

		self::assertInstanceOf( Collection::class, $result );
	}

	public function testUpdatingCollectionName () {
		$coll = new Collection();
		$coll->id = 1;
		$result = $this->ctrl->updateName( $this->db, $coll, 'test' );

		self::assertTrue( $result );
	}

	public function testFailUpdatingCollectionName () {
		$fake_collection = new Collection();
		$fake_collection->id = -1;
		$result = $this->ctrl->updateName( $this->db, $fake_collection, 'test' );

		self::assertFalse( $result );
	}

	public function testUpdatingCollectionDescription () {
		$coll = new Collection();
		$coll->id = 1;
		$result = $this->ctrl->updateDescription( $this->db, $coll, 'new descript' );

		self::assertTrue( $result );
	}

	public function testFailUpdatingCollectionDescription () {
		$fake_collection = new Collection();
		$fake_collection->id = -1;
		$result = $this->ctrl->updateName( $this->db, $fake_collection, 'new descript' );

		self::assertFalse( $result );
	}

	public function testRemovingCollectionFromDatabaseDirectly () {
		$user = new User();
		$user->id = 1;
		$collection = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $user );

		$result = $this->ctrl->removeCollectionFromDatabase( $this->db, $collection );

		self::assertTrue( $result );

		$coll = Collection::fetchCollectionByID( $this->db, $collection->id );

		self::assertNull( $coll );
	}

	public function testTogglePublic () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$result = $this->ctrl->togglePublic( $this->db, $coll );

		self::assertTrue( $result );

		$old_public_value = $coll->public;

		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		self::assertNotEquals( $old_public_value, $coll->public );
	}

	public function testToggleEditable () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$result = $this->ctrl->toggleEditable( $this->db, $coll );

		self::assertTrue( $result );

		$old_editable_value = $coll->editable;

		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		self::assertNotEquals( $old_editable_value, $coll->editable );
	}

	public function testRequestNewCollection () {
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

	public function testDeletingCollectionInvalidUser () {
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

	public function testDeletingCollectionInvalidCollection () {
		$user = new User();
		$user->id = 1;
		$post = [
			'request' => 'delete',
			'collection' => '1'
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertEquals(
			-2,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function testDeletingCollectionInvalidOwner () {
		$user = User::fetchUserByID( $this->db, 2 );
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post = [
			'request' => 'delete',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $user, $post );

		self::assertTrue( $collection->owner_id !== $user->id );
		self::assertTrue( $user->admin == false );

		self::assertEquals(
			-3,
			$this->ctrl->result['err'],
			print_r( $this->ctrl->result, true )
		);
	}

	public function testDeletingCollectionWithImages () {
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

	public function testRequestDeletingCollection () {
		$user = new User();
		$user->id = 2;
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
}
