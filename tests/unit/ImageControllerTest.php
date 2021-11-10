<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class CollectionControllerTest
 */
class ImageControllerTest extends TestCase {

	protected $db;
	protected $ctrl;
	protected $testUser;
	protected $testCollection;
	protected $testImage;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		empty_database_and_test_collections();
		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new ImageController();
		$this->testUser = new User();
		$this->testUser->id = 1;
		$this->testCollection = new Collection();
		$this->testCollection->id = 1;
		$this->testImage = new Image();
		$this->testImage->id = 1;
	}

	public function test_RequestUploadNewImages () {

		// For future unit tests; this doesn't work, as it's not a simple rename()
		// Should use PHPT and ---POST_RAW---, but that is outside todays work.

		$imgDatasetPath = "../img-dataset/finland-regions-coat-of-arms/";
		$collectionsPath = "C:/Users/Jq/Documents/mymopsi-collections/collections/temp/";

		copy(
			$imgDatasetPath . 'Kainuu.png',
			$collectionsPath . 'Kainuu.png'
		);
		copy(
			$imgDatasetPath . 'Pirkanmaa.png',
			$collectionsPath . 'Pirkanmaa.png'
		);
		$_FILES = [
			'upload' => [
				'name' => [
					0 => 'Kainuu.png',
					1 => 'Pirkanmaa.png',
				],
				'type' => [
					0 => 'image/png',
					1 => 'image/png',
				],
				'tmp_name' => [
					0 => $collectionsPath . 'Kainuu.png',
					1 => $collectionsPath . 'Pirkanmaa.png',
				],
				'error' => [
					0 => 0,
					1 => 0,
				],
				'size' => [
					0 => '30123',
					1 => '30123',
				],
			],
		];

		$collection = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		$post = [
			'request' => 'upload',
			'collection' => $collection->random_uid,
		];

		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue(
			$this->ctrl->result[ 'success' ],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestAddMopsiPhotosFromCSV () {

		set_up_database();
		empty_database_and_test_collections();
		set_up_database();

		$collection = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		$post = [
			'request' => 'upload_mopsi_csv',
			'collection' => $collection->random_uid,
			'photos' => [
				0 => [
					'photo_id' => "040514_15-44-09_1381041452.jpg",
				],
				1 => [
					'photo_id' => "050614_16-31-26_347325878.jpg",
				],
			],
		];

		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue(
			$this->ctrl->result[ 'success' ],
			print_r( $this->ctrl->result, true )
		);
	}

	public function test_RequestDeleteImage () {
		$image = Image::fetchImageByID( $this->db, 2 );

		$post = [
			'request' => 'delete_image',
			'image' => $image->random_uid,
		];

		self::assertTrue( file_exists( $image->filepath ) );

		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue(
			$this->ctrl->result[ 'success' ],
			print_r( $this->ctrl->result, true )
		);

		self::assertFalse( file_exists( $image->filepath ) );
	}

	public function test_CreateThumbnail () {
		$image = Image::fetchImageByID( $this->db, 3 );
		$newThumbPath = INI[ 'Misc' ][ 'path_to_collections' ] . 'temp/test-thumb-actual-image.jpg';

		Common::deleteFiles( $newThumbPath );

		$this->ctrl->createImageThumbnailFile(
			$image->filepath,
			$newThumbPath
		);

		self::assertTrue( file_exists( $newThumbPath ), print_r([$image->filepath,$newThumbPath],true) );
	}

	public function test_RequestCreateThumbnail () {
		$image = Image::fetchImageByID( $this->db, 3 );

		$post = [
			'request' => 'create_thumbnail',
			'image' => $image->random_uid,
		];

		$this->ctrl->handleRequest( $this->db, $this->testUser, $post );

		self::assertTrue(
			$this->ctrl->result[ 'success' ],
			print_r( $this->ctrl->result, true )
		);

		$image = Image::fetchImageByID( $this->db, 3 );

		self::assertTrue( file_exists( $image->thumbnailpath ) );
	}

	public function test_RequestEditName () {
		$image = Image::fetchImageByID( $this->db, $this->testImage->id );
		$post_request = [
			'request' => 'edit_name',
			'name' => 'New name',
			'image' => $image->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$image->name !== 'New name'
		);

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);

		$image = Image::fetchImageByID( $this->db, $this->testImage->id );
		self::assertTrue(
			$image->name === 'New name'
		);
	}

	public function test_RequestEditDescription () {
		$image = Image::fetchImageByID( $this->db, $this->testImage->id );
		$post_request = [
			'request' => 'edit_description',
			'description' => 'New description',
			'image' => $image->random_uid
		];
		$this->ctrl->handleRequest( $this->db, $this->testUser, $post_request );

		self::assertTrue(
			$image->description !== 'New description'
		);

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true ) . print_r( $post_request, true )
		);

		$image = Image::fetchImageByID( $this->db, $this->testImage->id );
		self::assertTrue(
			$image->description === 'New description'
		);
	}

}
