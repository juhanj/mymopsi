<?php declare(strict_types=1);

/**
 * Class ClusteringHandler
 *
 * Slightly modified and cleaned up code
 * Originally written by Mohammad Rezaei for Mopsi server-side clustering API
 */
class ClusteringHandler {

	public $pathAPI = "/usr/local/www_root/mopsi/markerClustering/usage/clusteringAPI_clientServer/mccluster";
	public $pathPhotos = "/paikka/mobile_photo/";

	public $type;

	public $cellW;
	public $cellH;
	public $minDist;

	public $minX;
	public $maxX;
	public $minY;
	public $maxY;

	public $minLat;
	public $maxLat;
	public $minLon;
	public $maxLon;

	public $zoomLevel;
	public $reverseX;

	public $selected;
	public $clusterNum;
	public $objectNum;
	public $clusteringMethod;
	public $dataSize;

	public static function builder ( $options ): ClusteringHandler {
		$object = new ClusteringHandler();
		foreach ( $options AS $key => $value ) {
			$object->{$key} = $value;
		}
		return $object;
	}

	/**
	 * Return bounding box of objects resulted from a non spatial query
	 * @return array
	 */
	public function dataBounds () {
		$arg = "{$this->pathAPI} -t 1 -n {$this->dataSize}";
		exec( $arg );

		// bounding box of objects is written in the text file: clusteringAPI_clientServer/temp/dataBounds_info.txt
		$databounds = trim( file_get_contents( 'temp/dataBounds_info.txt' ) );
		$databounds = explode( " ", $databounds );

		$result = [
			[
				'lat' => $databounds[0], // north
				'lon' => $databounds[1], // east
			],
			[
				'lat' => $databounds[2], // south
				'lon' => $databounds[3], // west
			],
		];

		return $result;
	}

	/**
	 * Given a cell and the object index in the cell, find the information of the object
	 * @return array
	 */
	public function photoInfoByBoundingBox () {
		$arg = "{$this->pathAPI} -t 4 -n {$this->dataSize} -e {$this->minLat} -f {$this->maxLat}"
			. " -u {$this->minLon} -v {$this->maxLon} -s {$this->selected} -r {$this->reverseX}";

		exec( $arg );
		$photoinfo = trim( file_get_contents( 'temp/selectedPhoto_info.txt' ) );
		$photoinfo = explode( "&&", $photoinfo );

		$response = [
			[
				'lat' => $photoinfo[0],
				'lon' => $photoinfo[1],
				// Check if name is dummy, if yes replace with empty string instead
				'name' => ($photoinfo[2] === 'zxcv') ? $photoinfo[2] : '',
				'thumburl' => trim( $this->pathPhotos . "thumb-" . $photoinfo[3] ),
				'photourl' => trim( $this->pathPhotos . $photoinfo[3] ),
			]
		];

		return $response;
	}

	/**
	 * Read the clusters info written to the file: temp/clusters_info.txt by C code
	 */
	public function getClustersInfoFromFile () {
		// get clusters' info from file
		$clusterInfo = trim( file_get_contents( 'temp/clusters_info.txt' ) );
		$clusterInfo = explode( "\n", $clusterInfo );

		$n = count( $clusterInfo );
		$clusters = [];

		// last line is used for time information, hence $n-1
		for ( $i = 0; $i < $n - 1; $i++ ) {
			$cluster = explode( " ", $clusterInfo[$i] );

			$clusters[] = [
				'n' => $cluster[0],
				'x' => $cluster[1],
				'y' => $cluster[2],
				'latMin' => $cluster[3],
				'latMax' => $cluster[4],
				'lonMin' => $cluster[5],
				'lonMax' => $cluster[6],
				'thumburl' => trim( $this->pathPhotos . "thumb-" . $cluster[7] ),
				'photourl' => trim( $this->pathPhotos . $cluster[7] ),
				'id' => $i+1,
			];
		}

		// last line of file containing time information
		$last_line = explode(" ", $clusterInfo[$n-1]);
		$tc = $last_line[0];

		// reporting times and parameters
		// Stored in the last array index
		$clusters[] = [
			'queryTime' => 0,
			'clusteringTime' => $tc
		];

		return $clusters;
	}

	public function spatialQuery () {
		$arg = "{$this->pathAPI} -t 3 -w {$this->cellW} -h {$this->cellH} -d {$this->minDist} -a {$this->minX}"
			. " -b {$this->maxX} -p {$this->minY} -q {$this->maxY} -z {$this->zoomLevel} -r {$this->reverseX}"
			. " -n {$this->dataSize} -e {$this->minLat} -f {$this->maxLat} -u {$this->minLon} -v {$this->maxLon}";

		// run command line execution to run the C-code which does the actual clustering
		exec( $arg );
		// results are written to `/temp/clusters_info.txt`
		//TODO: Wait, why doesn't it just return the info immediately?
		//TODO: Wait, what happens if there's multiple requests?

		return getClustersInfoFromFile( $this->pathPhotos );
	}

	public function nonSpatialQuery () {
		$arg = "{$this->pathAPI} -t 2 -w {$this->cellW} -h {$this->cellH} -d {$this->minDist} -a {$this->minX}"
			. " -b {$this->maxX} -p {$this->minY} -q {$this->maxY} -z {$this->zoomLevel} -r {$this->reverseX}"
			. " -n {$this->dataSize}";
		exec( $arg );
		// results are written to `/temp/clusters_info.txt`
		//TODO: Wait, why doesn't it just return the info immediately?
		//TODO: Wait, what happens if there's multiple requests?
		return getClustersInfoFromFile( $this->pathPhotos );
	}
}
