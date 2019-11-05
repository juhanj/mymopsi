<?php declare(strict_types=1);

/**
 * Interface Controller
 */
interface Controller {

	/**
	 * @param DBConnection $db
	 * @param User $user
	 * @param array $req
	 */
	public function handleRequest ( DBConnection $db, User $user, array $req );

	/**
	 * @param int $id
	 * @param string $msg
	 */
	public function setError ( int $id, string $msg );

}