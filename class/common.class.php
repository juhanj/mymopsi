<?php
declare(strict_types=1);

/**
 *
 */
class Common {

	public static function isIntegerPowerOfTwo ( int $number ): bool {
		return ($number != 0) && (($number & ($number - 1)) == 0);
	}

	/**
	 * Round given number (float or int) to nearest given number (float or int)
	 *
	 * @param int|float $number
	 * @param int|float $roundTo The nearest number to round to. (uses round())
	 *
	 * @return int|float The original number rounded up to the nearest rounding number.
	 */
	public static function fancyRound ( $number, $roundTo ) {
		// If rounding to a decimal place, first multiply both parameters
		// by number of decimal places (the rounding below doesn't work with decimals)
		if ( is_float( $roundTo ) ) {
			// How many decimal places
			$decimals = strlen( (string)$roundTo ) - 2;
			// Multiplier to get input without decimals
			// e.g. roundTo: 0.05 => decimals: 2 => multiplier: 100 (1 + two zeroes)
			$multiplier = (int)('1' . (str_repeat( '0', $decimals )));

			// e.g.
			// number: 12.3456 & roundTo: 0.05
			// => 1234.56      & => 5
			$number = $multiplier * $number;
			$roundTo = $multiplier * $roundTo;
		}

		// If the original number is an integer and is a multiple of
		// the "nearest rounding number", return it without change.
		if ( (intval( $number ) == $number) && (!is_float( intval( $number ) / $roundTo )) ) {
			$return = intval( $number );
		}

		// If the original number is a float or if this integer is
		// not a multiple of the "nearest rounding number", do the
		// rounding up.
		else {
			$return = round( ($number + $roundTo / 2) / $roundTo ) * $roundTo;
		}

		// If original roundTo was a decimal, need to get number back to same precision
		if ( isset( $multiplier ) ) {
			// e.g. 12.3456 => 1234.56 => 1235 => 12.35
			$return = $return / $multiplier;
		}

		return $return;
	}

	/**
	 * Returns formatted number: 1 000[,00]
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
	 * @param string    $unit     'm'||'km' ; If given unit is KM, skip meter formatting
	 * @param int[]     $bounds   At what point switch from m to km, and km without decimals
	 *
	 * @return string
	 */
	public static function fDistance ( $distance, $unit = 'm', $bounds = [ 1000, 10 ] ) {
		$distance = round( $distance );
		if ( $distance < ($bounds[ 0 ] ?? 1000) and $unit === 'm' ) {
			$formatted = self::fNumber( $distance, 0 ) . " m";
		}
		else {
			if ( $unit === 'm' ) {
				$distance /= 1000;
			}
			if ( $distance < ($bounds[ 1 ] ?? 10) ) {
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
	 * //TODO days months years
	 *
	 * @param int|float $time      in seconds
	 * @param string    $unit      s || m || h ;
	 *                             Skip to correct formatting level
	 * @param int[]     $bounds    Bounds between different levels of formatting
	 *                             Default: [ 60 (s), 60*60 (m), 60*60*10 (h) ]
	 *
	 * @return string
	 */
	public static function fTime ( $time, $unit = 's', $bounds = [ 60, 60 * 60, 60 * 60 * 10 ] ) {
		// If number not second, convert to second
		if ( $unit === 'm' ) {
			$time *= 60;
		}
		else if ( $unit === 'h') {
			$time *= (60 * 60);
		}

		$time = round( $time );

		// seconds
		if ( $time < $bounds[0] ) {
			$formatted = "{$time} s";
		}

		// minutes
		else if ( $time < $bounds[1] ) {
			$time /= 60;

			if ( $time < 60 ) {
				$formatted = round( $time ) . " m";
			}
		}

		// hours < 10h
		else if ( $time < 60 * 60 * 10 ) {
			$time /= 60 * 60;
			$formatted = self::fNumber( $time, 1 ) . " h";
		}

		else {
			$time /= 60 * 60;
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

	/**
	 * Delete all files recursively. Deletes hidden files. Checks for empty or
	 * "/" (or root) $target, so that it doesn't accidentally delete the whole server.
	 *
	 * @param $target
	 */
	public static function deleteFiles ( $target ) {
		// Sanity checks (for not accidentally deleting the server root directory)
		if ( !$target or $target === '' or $target == '/' or $target == '\\' ) {
			return;
		}

		// Check if directory (handled differently from a file)
		// Will ignore symbolic links, because rmdir can't delete symlinks
		if ( !is_link( $target ) && is_dir( $target ) ) {
			// it's a directory; recursively delete everything in it
			// array_diff() removes the ., .. entries, which would cause the code to recurse forever
			$files = array_diff(
				scandir( $target ),
				[ '.', '..' ]
			);

			foreach ( $files as $file ) {
				self::deleteFiles( "$target/$file" );
			}

			rmdir( $target );
		}
		// Check if file
		else if ( is_file( $target ) ) {
			unlink( $target );
		}
	}

}
