<?php declare(strict_types=1);

class Collection {

	/**
	 * @var string
	 */
	public $id = null;
	/**
	 * @var array Images in collection, and their info.
	 */
	public $imgs = array();
	/**
	 * @var bool Is there a collection with given ID?
	 */
	public $exists = false;

	function __construct ( DBConnection $db, string $id ) {
		if ( !$db or !$id ) {
			return;
		}

		$this->id = $id;

		$this->getCollection();
	}

	function getCollection () {
		$path = INI['Misc']['path_to_collections'] . '/' . $this->id . '/exifdata.csv';

		$file = fopen( $path, 'r' );
		if ( !$file ) {
			return;
		}

		$this->exists = true;

		fgetcsv($file, 1000, ",");

		$counter = 0;

		while ( ($data = fgetcsv($file, 1000, ",")) !== FALSE ) {
			$this->imgs[] = [
				'id' => $counter++,
				'filename' => $data[ 0 ],
				'lat' => $data[ 1 ], // Latitude
				'long' => $data[ 3 ], // Longitude
				'lat_ref' => $data[ 2 ], // North | South
				'long_ref' => $data[ 4 ], // East | West
				'resolution' => $data[ 6 ] // Image resolution, e.g. 1960x1080
			];
		}

		fclose($file);
	}
}
