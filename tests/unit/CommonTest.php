<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class helperFunctionsTest
 */
class CommonTest extends TestCase {

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

	public function test_CheckRandomUIDAvailable () {
		$true = Common::checkRandomUIDAvailable( $this->db, 0 );

		self::assertTrue( $true );

		$user = User::fetchUserByID( $this->db, 1 );
		$false = Common::checkRandomUIDAvailable( $this->db, $user->random_uid );

		self::assertFalse( $false );
	}

	public function test_CreateRandomUID () {
		$ruid = Common::createRandomUID( $this->db );
		self::assertIsString( $ruid );
		self::assertEquals( 20, strlen( $ruid ) );

		$ruid = Common::createRandomUID( $this->db, 10 );
		self::assertIsString( $ruid );
		self::assertEquals( 10, strlen( $ruid ) );

		$ruid = Common::createRandomUID( $this->db, 4, false );
		self::assertIsString( $ruid );
		self::assertEquals( 4, strlen( $ruid ) );

		// Always returns even number, so testing odd numbers hard
	}

	public function test_RunExiftool () {
		$directory = 'D:\juhanj\Documents\mymopsi\mopsi_photos';
		$result = Common::runExiftool( $directory );
		self::assertIsObject( $result[0] );
	}

	public function test_GetNominatimReverseGeocoding () {
		$result = Common::getNominatimReverseGeocoding( 62.5913800, 29.7796980 );
		self::assertIsObject( $result );
	}

	public function test_DeleteFiles () {
		$file = "./test.txt";
		file_put_contents( $file, "foo bar" );

		self::assertTrue( file_exists( $file ) );

		Common::deleteFiles( $file  );

		self::assertFalse( file_exists( $file ) );
	}
}
