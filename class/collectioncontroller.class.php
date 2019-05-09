<?php declare(strict_types=1);

class CollectionController {

	public $db = null;
	public $lang = null;
	public $result = null;

	function __construct ( DBConnection $db, Language $lang, array $parameters ) {
		if ( !$db or !$parameters ) {
			return;
		}

		$this->lang = $lang;
		$this->db = $db;

		$this->{$parameters['req']}( $parameters );
	}

	function getCollection () {}

	function editName () {}

	function editDescription () {}

	function deleteCollection () {}

	function addNewCollection () {}

	/**
	 * Run exiftool for a given collection and save output to .csv-file.
	 * @param array $uid <code>['id']</code>Four char UID of a collection
	 */
	function runExiftool ( array $uid ) {

		$perl = INI['Misc']['perl'];

		$exift = './exiftool/exiftool';

		// -a : allow duplicates (needed for gps coordinates)
		// -gps:all : all gps exif data
		// -ImageSize : self-explanatory
		// -c %.6f : format for gps coordinates output
		// -csv : print to csv
		$command = "-a -gps:all -ImageSize -c %.6f -csv"; // -csv -v5

		// Reads all images in the given directory
		$target = INI['Misc']['path_to_collections'] . "/{$uid['id']}/";

		// Where we want to save the .CSV-file. (Same dir as images)
		$csv = $target . '/exifdata.csv';

		exec(
			"{$perl} {$exift} {$command} {$target} > {$csv}"
		);

		$this->result = true;
	}

	/**
	 * Returns all public collections from the database.
	 */
	public function getPublicCollections () {
		//TODO: get all collections set public from database --jj190509
		// database call, get id, name, descr, size of collection (join)
		// return results
	}
}
