<?php
declare(strict_types=1);

require './test-set-up.php';

use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends TestCase {

	protected $db;

	public function setUp (): void {
		parent::setUp();

		$this->db = (!$this->db)
			? new DBConnection()
			: $this->db;
	}

	public function test_Connection () {
		self::assertInstanceOf( PDO::class, $this->db->getConnection() );
	}

	public function test_GetOneRowFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = ? limit 1',
			[1],
			false
		);

		self::assertTrue( boolval($row->id) );
		self::assertEquals( 1, $row->id ?? null );
	}

	public function test_GetNoRowFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = 0 limit 1'
		);

		self::assertFalse( boolval($row) );
	}

	public function test_GetClassFromDatabase () {
		$row = $this->db->query(
			'select id from mymopsi_user where id = ? limit 1',
			[1], false, 'User'
		);

		self::assertInstanceOf( User::class, $row );
	}

}
