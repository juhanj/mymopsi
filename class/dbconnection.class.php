<?php
declare(strict_types=1);

/**
 * This class handles the database connection. A wrapper for PDO, if you will.
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
	 * Optional options for the PDO connection, given at new PDO(...).
	 * ATTR_* : attributes<br>
	 *    _ERRMODE : How errors are handled.<br>
	 *    _DEF_FETCH_M : Default return type<br>
	 *    _EMUL_PREP : {@link https://phpdelusions.net/pdo#emulation}
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
	 * Connection, that all methods use
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
	 * Reads necessary information directly from config.ini -file.
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
		$this->connection = new PDO( $this->pdo_dsn, $config[ 'user' ], $config[ 'pass' ], $this->pdo_options );
	}

	/**
	 * Suorittaa SQl-koodin prepared stmt:ia käytttäen. Palauttaa haetut rivit (SELECT),
	 * tai muutettujen rivien määrän muussa tapauksessa. <br>
	 * Defaultina palauttaa yhden rivin. Jos tarvitset useamman, huom. kolmas parametri.<p><p>
	 * Huom. Liian suurilla tuloksilla saattaa kaatua. Älä käytä FetchAll:ia jos odotat kymmeniä tuhansia tuloksia.<p>
	 * Ilman neljättä parametria palauttaa tuloksen geneerisenä objektina.
	 *
	 * @param string $query
	 * @param array  $values         [optional], default = null <p>
	 *                               Muuttujien tyypilla ei ole väliä. PDO muuttaa ne stringiksi, jotka sitten
	 *                               lähetetään tietokannalle.
	 * @param bool   $fetchAllRows   [optional], default = false <p>
	 *                               Haetaanko kaikki rivit, vai vain yksi.
	 * @param string $className      [optional] <p> Jos haluat jonkin tietyn luokan olion. <p>
	 *                               Huom: haun muuttujien nimet pitää olla samat kuin luokan muuttujat.
	 *
	 * @return array|int|bool|stdClass <p> Palauttaa stdClass[], jos SELECT ja fetchAllRows==true.
	 *                               Palauttaa stdClass-objektin, jos haetaan vain yksi.<br>
	 *                               Palauttaa <code>$stmt->rowCount</code> (muutettujen rivien määrä), jos esim.
	 *                               INSERT tai DELETE.<br>
	 */
	public function query( string $query, array $values = [], bool $fetchAllRows = false, string $className = '' ) {
		// Katsotaan mikä hakutyyppi kyseessä, jotta voidaan palauttaa hyödyllinen vastaus tyypin mukaan.
		// Kaikki haku-tyypit ovat 6 merkkiä pitkiä. Todella käytännöllistä.
		$query_type = strtolower( substr( ltrim( $query ), 0, 6 ) );
		// Valmistellaan query
		$stmt = $this->connection->prepare( $query );
		//Toteutetaan query varsinaisilla arvoilla
		$stmt->execute( $values );

		if ( $query_type === "select" ) {
			if ( $fetchAllRows ) {
				if ( empty( $className ) ) {
					return $stmt->fetchAll();
				}
				else {
					// Palautetaan tietyn luokan olioina
					return $stmt->fetchAll( PDO::FETCH_CLASS, $className );
				}
			}
			// Haetaan vain yksi rivi
			else {

				if ( empty( $className ) ) {
					return $stmt->fetch();
				}
				else {
					// Palautetaan tietyn luokan oliona
					return $stmt->fetchObject( $className );
				}
			}
		}
		// Palautetaan vain muutettujen rivien määrän.
		else {
			return $stmt->rowCount();
		}
	}

	/**
	 * Valmistelee erillisen haun, jota voi sitten käyttää {@see run_prep_stmt()}-metodilla.
	 * @param string $query
	 */
	public function prepare_stmt( string $query ) {
		$this->prepared_stmt = $this->connection->prepare( $query );
	}

	/**
	 * Suorittaa valmistellun sql-queryn (valmistelu {@see prepare_stmt()}-metodissa).
	 * Hae tulos {@see get_next_row()}-metodilla.
	 * @param array $values [optional], default=NULL<p>
	 *                      queryyn upotettavat arvot
	 * @return bool
	 */
	public function run_prepared_stmt( array $values = [] ) : bool {
		return $this->prepared_stmt->execute( $values );
	}

	/**
	 * Palauttaa seuraavan rivin viimeksi tehdystä hausta.
	 * Huom. ei toimi query()-metodin kanssa. Käytä vain prep.stmt -metodien kanssa.<br>
	 * Lisäksi, toisen haun tekeminen millä tahansa muulla metodilla nollaa tulokset.
	 * @param string $className  [optional] <p> Jos haluat jonkin tietyn luokan olion. <p>
	 *                           Huom: haun muuttujien nimet pitää olla samat kuin luokan muuttujat.
	 * @return mixed|stdClass
	 */
	public function get_next_row( string $className = '' ) {
		return (empty( $className ))
			? $this->prepared_stmt->fetch()
			: $this->prepared_stmt->fetchObject( $className ) ;
	}

	/**
	 * Palauttaa PDO-yhteyden manuaalia käyttöä varten.
	 * @return PDO connection
	 */
	public function getConnection () : PDO {
		return $this->connection;
	}
}
