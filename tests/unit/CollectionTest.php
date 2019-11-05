<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

use PHPUnit\Framework\TestCase;

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

	/** @test */
	public function testCreateEmptyCollectionObject () {
		$coll = new Collection();

		self::assertInstanceOf( Collection::class, $coll );
		self::assertNull( $coll->id );
	}

	public function testFetchCollectionWithID () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		self::assertEquals( 1, $coll->id );
		self::assertGreaterThanOrEqual( 1, $coll->number_of_images );
	}

	public function testFetchCollectionWithRUID () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );
		$coll = Collection::fetchCollectionByRUID( $this->db, $coll->random_uid );

		self::assertIsInt( $coll->id );
		self::assertIsInt( $coll->number_of_images );
	}

	public function testGettingImages () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$coll->getImages( $this->db );

		self::assertNotEmpty( $coll->images );
		self::assertInstanceOf( Image::class, $coll->images[0] );
	}

	public function testGettingOwner () {
		$coll = Collection::fetchCollectionByID( $this->db, 1 );

		$coll->getOwner( $this->db );

		self::assertInstanceOf( User::class, $coll->owner );
		self::assertIsInt( $coll->owner->id );
	}
}
