<?php
declare(strict_types=1);

/**
 *
 */
class Common {

	/**
	 * Returns formatted number: 1.000[,00]
	 *
	 * @param mixed $number
	 * @param int   $dec_count [optional] default=2 <p> Number of decimals.
	 *
	 * @return string
	 */
	public static function fNumber ( $number, int $dec_count = 2 ): string {
		return number_format( (float)$number, $dec_count, ',', ' ' );
	}

	/**
	 * Format meters into human readable format.
	 * < 1k meters => 1000 meters (no decimals)
	 * > 1k meters => 1,0 km (with decimal)
	 * > 10k meters => 10 km (no decimal)
	 *
	 * @param int|float $distance in meters
	 *
	 * @return string
	 */
	public static function fDistance ( $distance ) {
		$distance = round( $distance );
		if ( $distance < 1000 ) {
			$formatted = self::fNumber( $distance, 0 ) . " m";
		}
		else {
			$distance /= 1000;
			if ( $distance < 10 ) {
				$formatted = self::fNumber( $distance, 1 ) . " km";
			}
			else {
				$formatted = self::fNumber( $distance, 0 ) . " km";
			}
		}

		return $formatted;
	}

	/**
	 * Format time into human readable format
	 * < 60 s => seconds, no decimal
	 * > 60 s => minutes, no decimal
	 * > 60 min => hours with minutes
	 * > 10 hours => hours, no minutes, no decimals
	 * TODO days months years
	 *
	 * @param int|float $time in seconds
	 *
	 * @return string
	 */
	public static function fTime ( $time ) {
		$time = round( $time );
		// seconds
		if ( $time < 60 ) {
			$formatted = "{$time} s";
		}

		// minutes
		else if ( $time < (60*60) ) {
			$time /= 60;

			if ( $time < 60 ) {
				$formatted = "{$time} m";
			}
		}

		// hours < 10h
		else if ( $time < 60*60*10 ) {
			$time /= 60*60;
			$formatted = self::fNumber( $time, 1 ) . " h";
		}

		else {
			$time /= 60*60;
			$formatted = self::fNumber( $time, 0 ) . " h";
		}

		return $formatted;
	}

	/**
	 * Check feedback variable, and prevent resending form on page refresh or back button.
	 *
	 * @return string $feedback
	 */
	public static function checkFeedbackAndPOST (): string {
		// Stop form resending
		if ( !empty( $_POST ) or !empty( $_FILES ) ) {
			header( "Location: " . $_SERVER[ 'REQUEST_URI' ] );
			exit();
		}

		// Check the feedback from Session data
		$feedback = isset( $_SESSION[ "feedback" ] ) ? $_SESSION[ "feedback" ] : "";
		unset( $_SESSION[ "feedback" ] );

		return $feedback;
	}

	/**
	 * Checks whether a given random unique ID is used anywhere in the MyMopsi DB.
	 * Duplicated so rare that might as well check all the tables, even though
	 * a identical RUID would be alloved in two different tables.
	 *
	 * @param DBConnection $db
	 * @param              $ruid
	 *
	 * @return bool
	 */
	public static function checkRandomUIDAvailable ( DBConnection $db, $ruid ) {
		$sql = "select
			        exists (select 1 from mymopsi_user where random_uid = ?) or
			        exists (select 1 from mymopsi_collection where random_uid = ?) or
			        exists (select 1 from mymopsi_img where random_uid = ?)
		        as found";
		$result = $db->query( $sql, [ $ruid, $ruid, $ruid ], false );

		return !$result->found;
	}


	/**
	 * Returns a unique, random N character string
	 * //TODO: Does not work for odd number of characters, always returns even number
	 *
	 * @param DBConnection $db
	 * @param int          $length      How long string returned
	 * @param bool         $checkIfUsed Check if given RUID already used in DB.
	 *
	 * @return string Random unique N character identifier
	 */
	public static function createRandomUID ( DBConnection $db, $length = 20, $checkIfUsed = true ) {
		$ruid = null;
		do {
			try {
				$ruid = bin2hex( random_bytes( (int)($length / 2) ) );
			} catch ( Exception $e ) {
				$ruid = substr( str_shuffle( '123456789QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm' ), 0, $length );
			}
		} while ( $checkIfUsed and !self::checkRandomUIDAvailable( $db, $ruid ) );

		return $ruid;
	}

	/**
	 * Runs exiftool for given target (file or dir)
	 *
	 * @param string $target  file or directory
	 * @param string $options options for exiftool, by default has "-ext '*' -j"
	 *
	 * @return stdClass[] decoded JSON-output from command line
	 */
	public static function runExiftool ( string $target, string $options = '' ) {
		$perl = INI[ 'Misc' ][ 'perl' ];
		$exiftool = DOC_ROOT . WEB_PATH . '/exiftool/exiftool';

		$commandOptions =
			' -ext "*"' // Process all files
			. " -j" // Print output in JSON format
		;
		$commandOptions .= $options;


		exec(
			"{$perl} {$exiftool} {$commandOptions} {$target}",
			$output
		);

		return json_decode( implode( "", $output ) );
	}


	/**
	 * Reverse geocode a lat-long coordinate. Fetches address using Mopsi API, which uses nominatim.
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 * @return mixed
	 */
	public static function getNominatimReverseGeocoding ( float $latitude, float $longitude ) {
		$param = [
			'request_type' => 'get_address',
			'lat' => $latitude,
			'lon' => $longitude,
		];

		$mopsiServer = 'https://cs.uef.fi/mopsi/mobile/server.php?param=' . urlencode( json_encode( $param ) );

		$json_response = file_get_contents( $mopsiServer );

		$result = json_decode( $json_response );

		return $result->address;
	}

}
