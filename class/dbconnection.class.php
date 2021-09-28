<?php
declare(strict_types=1);

/**
 * This class handles the database connection.
 *
 * Link for more info on PDO: {@link https://phpdelusions.net/pdo}<br>
 *
 * Link to PHP-manual on PDO: {@link https://secure.php.net/manual/en/book.pdo.php}
 */
class DBConnection {

	/**
	 * For connecting PDO<br>
	 *    "mysql:host={$host};dbname={$database};charset={$charset}"
	 * @var string
	 */
	protected $pdo_dsn = '';

	/**
	 * Optional options for the PDO connection, given to new PDO constructor.<br>
	 * ATTR_ERRMODE : How errors are handled.<br>
	 * ATTR_DEF_FETCH_M : Default return type<br>
	 * ATTR_EMUL_PREP : {@link https://phpdelusions.net/pdo#emulation}<br>
	 * MYSQL_ATTR_FOUND_ROWS : Returns number of found rows
	 * @var array
	 */
	protected $pdo_options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::MYSQL_ATTR_FOUND_ROWS => true,
	];

	/**
	 * @var PDO object
	 */
	protected $connection = null;

	/**
	 * PDO statement, for use in prepared statements.
	 * This variable is used by: prepare_stmt(), run_prepared_stmt(),
	 *  get_next_row(), and close_prepared_stmt() -methods.
	 * Query() method uses a separate object.
	 * @var PDOStatement object
	 */
	protected $prepared_stmt = null;

	/**
	 * Reads necessary information directly from config.ini -file (INI['Database'] -global variable),
	 * if no params provided.
	 *
	 * @param array $config [optional] <p> Order of fields: <br>
	 *                      - If enum: host, name, user, pass (same as in config.ini)<br>
	 *                      - If assoc: Doesn't matter, but field names must be the same as in config.ini -file.
	 */
	public function __construct( array $config = [] ) {
		if ( empty($config) ) {
			$config = INI['Database'];
		}
		elseif ( isset( $config[ 0 ] ) ) {
			$config = [ 'host' => $config[ 0 ], 'name' => $config[ 1 ], 'user' => $config[ 2 ], 'pass' => $config[ 3 ] ];
		}
		$this->pdo_dsn = "mysql:host={$config[ 'host' ]};dbname={$config[ 'name' ]};charset=utf8";

		// Need try-catch for security reason
		// If connection fails, it will print an error message with the credentials visible
		try {
			$this->connection = new PDO( $this->pdo_dsn, $config[ 'user' ], $config[ 'pass' ], $this->pdo_options );
		} catch ( PDOException $e ) {
			echo "PDO connection failed. Please check connection to database, credentials, "
				."and that the db you're trying to connect to exists.";
			die();
		}

	}

	/**
	 * Execute SQL-query using prepared statement. Returns found rows if SELECT, or row count otherwise.
	 * By default returns a generic object (stdClass). If a specific class is wanted, note 4th param.
	 * Note: May not work if too many found rows. So keep in mind if expecting tens of thousands of results.
	 *
	 * @param string $query
	 * @param array  $values         [optional], default = null <p>
	 *                               Type doesn't matter. PDO casts them to string all the same.
	 * @param bool   $fetchAllRows   [optional], default = true
	 * @param string $className      [optional], default generic object <p>
	 *                               IF you want some specific class. Note: returned columns need
	 *                               to have same name as class variables.
	 *
	 * @return array|int|bool|stdClass <p> Returns stdClass[], if SELECT and fetchAllRows == true.
	 *                                 Returns row count if e.g. INSERT or DELETE
	 */
	public function query( string $query, array $values = [], bool $fetchAllRows = true, string $className = '' ) {
		// Check query type, which determines what is returned
		// Trim spaces, take first six characters, all lower characters.
		// Because SELECT is six characters, and we only care about that one (though they are all six char long)
		$query_type = strtolower( substr( ltrim( $query ), 0, 6 ) );

		// Prepare query
		$stmt = $this->connection->prepare( $query );

		// Execute with given values
		$stmt->execute( $values );

		// Returning results
		if ( $query_type === "select" ) {
			// If SELECT, return found rows
			if ( $fetchAllRows ) {
				if ( empty( $className ) ) {
					return $stmt->fetchAll();
				}
				else {
					// Return a specific object
					return $stmt->fetchAll( PDO::FETCH_CLASS, $className );
				}
			}
			else {
				//TODO: deprecate / remove
				if ( empty( $className ) ) {
					return $stmt->fetch();
				}
				else {
					return $stmt->fetchObject( $className );
				}
			}
		}
		// Otherwise just return count of matched rows (not necessarily changed rows, just matched)
		else {
			return $stmt->rowCount();
		}
	}

	/**
	 * Palauttaa PDO-yhteyden manuaalia käyttöä varten.
	 * @return PDO connection
	 */
	public function getConnection () : PDO {
		return $this->connection;
	}
}
