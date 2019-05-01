<?php declare(strict_types=1);

/**
 * Class Language
 * Extends stdClass because we want it's functionality regarding dynamic variables
 * (I think that refers only to the IDE warnings. The code would work just as well without.)
 */
class Language extends stdClass {

	/**
	 * @var string $lang Three character language code ISO 639-2/T
	 */
	public $lang;
	public $page;

	/**
	 * Language constructor.
	 * @param string $lang From PHP $_COOKIES, Three character language code ISO 639-2/T
	 * @param string $page Current page
	 */
	function __construct( string $lang = null, string $page = CURRENT_PAGE ) {

		if ( $lang === null and !empty($_COOKIE[ 'lang' ]) ) {
			switch ( json_decode($_COOKIE[ 'lang' ]) ) {
				case 'eng' :
					$this->lang = 'eng';
					break;
				case 'fin' :
					$this->lang = 'fin';
					break;
				default :
					$this->lang = 'eng';
			}
		}
		else {
			$this->lang = $lang;
		}

		$this->page = $page;

		/*
		 * Load the whole JSON file for one language, which is a bit
		 * different from the SQL-version where we only load needed (page-specific) strings
		 * from the database.
		 */
		$json = json_decode(
			file_get_contents( "lang/{$this->lang}.json", true )
		);

		/**
		 * This would be a bit cleaner with a database,
		 * but with a small enough JSON file probably won't matter.
		 */
		foreach ( $json->pages as $jsonPage ) {
			if ( $jsonPage->page === '_common' or $jsonPage->page === $this->page ) {

				foreach ( $jsonPage->strings as $type => $str ) {
					$this->{$type} = $str;
				}

			}
		}
	}

	/**
	 * Custom _GET for printing custom backup string, in case something is missing.
	 * @param string $name The title, or type, or header of the wanted string.
	 * @return string Either the correct string or "NULL {$str}"
	 */
	function __get( $name ) {
		if ( !isset($this->{$name}) ) {
			return "NULL {$name}";
		}

		return $this->{$name};
	}

}
