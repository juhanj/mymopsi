<?php declare(strict_types=1);

/**
 * Interface Controller
 */
interface Controller {

	/**
	 * @param $db
	 * @param $req
	 * @return mixed
	 */
	public function handleRequest ( $db, $req );
}