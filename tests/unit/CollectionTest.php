<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 */
class CollectionTest extends TestCase {

	protected $db;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db)
			? new DBConnection()
			: $this->db;
	}

	public function test_CreateEmptyCollectionObject () {
		$coll = new Collection();

		self::assertInstanceOf( Collection::class, $coll );
		self::assertNull( $coll->id );
	}

	public function test_FetchCollectionByID () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		self::assertEquals( 1, $coll->id );
		self::assertIsInt( $coll->number_of_images );
	}

	public function test_FetchCollectionByRUID () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );
		$coll = Collection::fetchCollectionByRUID( $this->db, $coll->random_uid );

		self::assertIsInt( $coll->id );
		self::assertIsInt( $coll->number_of_images );
	}

	public function test_FetchPublicCollections () {
		$colls = Collection::fetchPublicCollections( $this->db );

		self::assertIsArray( $colls );
		self::assertNotEmpty( $colls );
	}

	public function test_FetchAllCollections () {
		$colls = Collection::fetchAllCollections( $this->db );

		self::assertIsArray( $colls );
		self::assertNotEmpty( $colls );
	}

	public function test_GetImages () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$coll->getImages( $this->db );

		self::assertIsArray( $coll->images );
		self::assertGreaterThanOrEqual(
			2,
			count($coll->images)
		);
	}

	public function test_GetOwner () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$coll->getOwner( $this->db );

		self::assertInstanceOf( User::class, $coll->owner );
		self::assertIsInt( $coll->owner->id );
	}
}
