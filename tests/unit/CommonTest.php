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

	public function test_fNumber () {
		$number = 1234.5678;
		$result = Common::fNumber( $number );

		self::assertEquals( '1 234,57', $result );
	}

	public function test_fDistance () {
		// meters
		$number = 123.456;
		$result = Common::fDistance( $number );
		self::assertEquals( '123 m', $result );
		// kilometers with decimal
		$number = 1234.567;
		$result = Common::fDistance( $number );
		self::assertEquals( '1,2 km', $result );
		// kilometers without decimal, with rounding
		$number = 12900;
		$result = Common::fDistance( $number );
		self::assertEquals( '13 km', $result );

		// unitGiven param testing:
		$number = 4;
		$result = Common::fDistance( $number, 'km' );
		self::assertEquals( '4,0 km', $result );
		// bounds param testing:
		$number = 800;
		$result = Common::fDistance( $number, 'm', [500, 3] );
		self::assertEquals( '0,8 km', $result );
	}

	public function test_fTime () {
		// seconds
		$number = 59;
		$result = Common::fTime( $number );
		self::assertEquals( '59 s', $result );
		// minutes
		$number = 61;
		$result = Common::fTime( $number );
		self::assertEquals( '1 m', $result );
		// hours < 10 (with decimal)
		$number = 60*60*9.4;
		$result = Common::fTime( $number );
		self::assertEquals( '9,4 h', $result );
		// hours > 10
		$number = 60*60*12;
		$result = Common::fTime( $number );
		self::assertEquals( '12 h', $result );

		// unit param testing:
		$number = 4;
		$result = Common::fTime( $number, 'h' );
		self::assertEquals( '4,0 h', $result );
		// bounds param testing:
		$number = 90;
		$result = Common::fTime( $number, 's', [ 120 ] );
		self::assertEquals( '90 s', $result );
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

	public function test_DeleteFiles_singleFile () {
		$file = INI['Misc']['path_to_collections'] . "/unitest-collec1-ruid/unitest-image1-ruid";

		Common::deleteFiles( $file );

		self::assertFalse( file_exists( $file ) );
	}

	public function test_DeleteFiles_directoryRecursively () {
		$file = INI['Misc']['path_to_collections'] . "/unitest-collec2-ruid";

		self::assertTrue( file_exists( $file ) );

		Common::deleteFiles( $file );

		self::assertFalse( file_exists( $file ) );
	}
}
