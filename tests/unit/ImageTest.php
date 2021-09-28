<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class UserTest
 */
class ImageTest extends TestCase {

	protected $db;

	public static function setUpBeforeClass (): void {
		parent::setUpBeforeClass();

		set_up_database();
	}

	public function setUp (): void {
		parent::setUp();
		$this->db = (!$this->db) ? new DBConnection() : $this->db;
	}

	public function test_FetchImageByID () {
		$img = Image::fetchImageByID( $this->db, 1 );

		self::assertEquals( 1, $img->id );
	}

	public function test_FetchImageByRUID () {
		$img = Image::fetchImageByID( $this->db, 1 );
		$img = Image::fetchImageByRUID( $this->db, $img->random_uid );

		self::assertEquals( 1, $img->id );
	}
}
