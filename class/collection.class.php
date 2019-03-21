<?php declare(strict_types=1);

class collection {

	public $result = null;

	function __construct ( DBConnection $db, $parameters ) {
		if ( !$db or !$parameters ) {
			return;
		}

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
		$ini = parse_ini_file( "./cfg/config.ini.php", true );

		// I don't have perl on my PATH in windows, so it's easier to have it set in config.ini file.
		$perl = $ini['perl'];

		$exift = './exiftool/exiftool';

		// -a : allow duplicates (needed for gps coordinates)
		// -gps:all : all gps exif data
		// -ImageSize : self-explanatory
		// -c %.6f : format for gps coordinates output
		// -csv : print to csv
		$command = "-a -gps:all -ImageSize -c %.6f -csv"; // -v5

		// Reads all images in the given directory
		$target = "./collection/{$uid['id']}/";

		exec(
			"{$perl} {$exift} {$command} {$target} > collection.csv"
		);

		$this->result = true;
	}
}
