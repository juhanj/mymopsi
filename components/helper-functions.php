<?php
declare(strict_types=1);

/**
 * Checks whether a given random unique ID is used anywhere in the MyMopsi DB.
 * Duplicated so rare that might as well check all the tables, even though
 * a identical RUID would be alloved in two different tables.
 * @param DBConnection $db
 * @param $ruid
 * @return bool
 */
function checkRandomUIDAvailable ( DBConnection $db, $ruid ) {
	$sql = "select 
        exists (select 1 from mymopsi_user where random_uid = ?) or 
        exists (select 1 from mymopsi_collection where random_uid = ?) or 
        exists (select 1 from mymopsi_img where random_uid = ?)
        as found";
	$result = $db->query( $sql, [ $ruid, $ruid, $ruid ] );

	return !$result->found;
}


/**
 * Returns a unique, random N character string
 * //TODO: Does not work for odd number of characters, always returns even number
 * @param DBConnection $db
 * @param int $length How long string returned
 * @param bool $checkIfUsed Check if given RUID already used in DB.
 * @return string Random unique N character identifier
 */
function createRandomUID ( DBConnection $db, $length = 20, $checkIfUsed = true ) {
	$ruid = null;
	do {
		try {
			$ruid = bin2hex( random_bytes( (int)($length / 2) ) );
		} catch ( Exception $e ) {
			$ruid = substr( str_shuffle( '123456789QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm' ), 0, $length );
		}
	} while ( $checkIfUsed and !checkRandomUIDAvailable( $db, $ruid ) );

	return $ruid;
}

/**
 * @param mixed $var
 * @param bool $var_dump
 */
function debug ( $var, bool $var_dump = false ) {
	echo "<br><pre>Print_r ::<br>";
	print_r( $var );
	echo "</pre>";
	if ( $var_dump ) {
		echo "<br><pre>Var_dump ::<br>";
		var_dump( $var );
		echo "</pre><br>";
	};
}

/**
 * Prints formatted number: 1.000[,00]
 * @param mixed $number
 * @param int $dec_count [optional] default=2 <p> Number of decimals.
 * @return string
 */
function fNumber ( $number, int $dec_count = 2 ): string {
	return number_format( (float)$number, $dec_count, ',', '.' );
}

/**
 * Check feedback variable, and prevent resending form on page refresh or back button.
 * @return string $feedback
 */
function check_feedback_POST() {
	// Stop form resending
	if ( !empty($_POST) or !empty($_FILES) ){
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit();
	}

	// Check the feedback from Session data
	$feedback = isset($_SESSION["feedback"]) ? $_SESSION["feedback"] : "";
	unset($_SESSION["feedback"]);
	return $feedback;
}