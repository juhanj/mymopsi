<?php
declare(strict_types=1);

$home_directory = 'C:\xampp\htdocs\mopsi_dev\mymopsi/';
require_once $home_directory . '\tests\unit\test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends TestCase {

	protected $db;

	public function setUp (): void {
		$this->db = (!$this->db)
			? new DBConnection()
			: $this->db;
	}

	public function testConnection () {
		self::assertInstanceOf( PDO::class, $this->db->getConnection() );
	}

	public function testGetOneRowFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = ? limit 1',
			[1]
		);

		self::assertTrue( boolval($row->id) );
		self::assertEquals( 1, $row->id ?? null );
	}

	public function testGetNoRowFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = 0 limit 1'
		);

		self::assertFalse( boolval($row) );
	}

	public function testGetClassFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = ? limit 1',
			[1], false, 'User'
		);

		self::assertInstanceOf( User::class, $row );
	}

}
