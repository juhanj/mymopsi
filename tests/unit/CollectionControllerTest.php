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
	protected $testUser;
	protected $testCollection;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		empty_database();
		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new CollectionController();
		$this->testUser = new User();
		$this->testUser->id = 1;
		$this->testCollection = new Collection();
		$this->testCollection->id = 1;
	}

	public function test_CreateEmptyCollectionRowInDatabase () {
		$result = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $this->testUser );

		self::assertInstanceOf( Collection::class, $result );
	}

	public function test_DeleteAllImagesInCollection () {
		$collection = Collection::fetchCollectionByID( $this->db, 2 );

		self::assertTrue( file_exists(INI['Misc']['path_to_collections'] . '/' . $collection->random_uid));

		$result = $this->ctrl->deleteAllImagesInCollection( $this->db, $collection );

		self::assertFalse( file_exists(INI['Misc']['path_to_collections'] . '/' . $collection->random_uid));

		self::assertTrue( $result );
	}

	public function test_DeleteCollectionFromDatabase () {
		$new_coll = $this->ctrl->createEmptyCollectionRowInDatabase( $this->db, $this->testUser );

		$result = $this->ctrl->deleteCollectionFromDatabase( $this->db, $new_coll );

		self::assertTrue( $result );
	}

	public function test_SetName () {
		$random_string = Common::createRandomUID( $this->db, 4, false );

		$result = $this->ctrl->setName( $this->db, $this->testCollection, $random_string );
		$coll = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		self::assertTrue( $result );
		self::assertEquals( $random_string, $coll->name );
	}

	public function test_SetDescription () {
		$random_string = Common::createRandomUID( $this->db, 4, false );

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
		$post_request = [
			'request' => 'new_collection',
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_requestCreateNewCollection_WithOptions () {
		$post_request = [
			'request' => 'new_collection',
			'name' => 'Foo',
			'description' => 'Bar',
			'public' => 'on',
			'editable' => 'on',
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestDeleteCollection () {
		$collection = Collection::fetchCollectionByID( $this->db, 2 );

		$post_request = [
			'request' => 'delete_collection',
			'collection' => $collection->random_uid,
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestEditName () {
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_name',
			'name' => 'New name',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestEditDescription () {
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_description',
			'description' => 'New description',
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}

	public function test_RequestEditPublic () {
		$collection = Collection::fetchCollectionByID( $this->db, 1 );
		$post_request = [
			'request' => 'edit_public',
			'public' => true,
			'collection' => $collection->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);
	}
}
