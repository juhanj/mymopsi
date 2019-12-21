<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class CollectionControllerTest
 */
class ImageControllerTest extends TestCase {

	protected $db;
	protected $ctrl;
	protected $testUser;
	protected $testCollection;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
		$this->ctrl = new ImageController();
		$this->testCollection = new Collection();
		$this->testCollection->id = 1;
		$this->testUser = new User();
		$this->testUser->id = 1;
	}

	public function test_RequestUploadNewImages () {
		$_FILES = [
			'upload' => [
				'name' => [
					0 => 'Ahvenanmaa.png',
					1 => 'Ahvenanmaa.png'
				],
				'type' => [
					0 => 'image/png',
					1 => 'image/png',
				],
				'tmp_name' => [
					0 => 'C:\xampp\htdocs\mopsi_dev\mymopsi\tests\img\1-normal-working-default-set\Ahvenanmaa.png',
					1 => 'C:\xampp\htdocs\mopsi_dev\mymopsi\tests\img\1-normal-working-default-set\Ahvenanmaa.png',
				],
				'error' => [
					0 => 0,
					1 => 0,
				],
				'size' => [
					0 => '100000',
					1 => '100000',
				]
			]
		];

		$collection = Collection::fetchCollectionByID( $this->db, $this->testCollection->id );

		$post = [
			'request' => 'upload',
			'collection' => $collection->random_uid,
		];

		$this->ctrl->requestUploadNewImages( $this->db, $this->testUser, $post );

		self::assertTrue(
			$this->ctrl->result['success'],
			print_r( $this->ctrl->result, true )
		);
	}
}
