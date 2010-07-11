<?
/**
 *	AbstractDatabase class file
 * 
 * @author Matthew Schiros, InvisiHosting LLC <schiros@invisihosting.com>
 * @version 1.0
 *	@package InvisiFramework
 */

/**
 * class AbstractDatabase
 * 
 * All databases within the framework will extend this and implement most/all of its
 * methods
 * @abstract
 * 
 *
 */
abstract class AbstractDatabase {
	
	/**
	 * error handling constants
	 *
	 */
	const ERROR_CONNECT = 1;
	const ERROR_DB_SELECT = 2;
	const ERROR_INSERT = 3;
	const ERROR_UPDATE = 4;
	const ERROR_QUERY = 5;

	/**
	 * query type constants
	 * 
	 *
	 */
	const UPDATE = 1;
	const INSERT = 2;

	/**
	 * The database host (IP or hostname)
	 *
	 * @var string 
	 * @access protected
	 * 
	 */
	
	protected $dbHost;
	/**
	 * The database name to connect to
	 *
	 * @var string
	 */
	
	protected $dbName;
	
	/**
	 * The database user
	 *
	 * @var string
	 */
	protected $dbUser;
	
	/**
	 * database password
	 *
	 * @var string
	 */
	
	protected $dbPassword;
	
	/**
	 * The most recent query run by the DB object
	 *
	 * @var string
	 */
	protected $query;
	
	/**
	 * The primary key ID of the most recent insert
	 *
	 * @var int
	 */
	protected $insertID;
	
	/**
	 * the current open connection
	 *
	 * @var MySQLResource|PostgreSQLResource
	 */
	protected $connection;
	
	/**
	 * The query log
	 * 
	 * Where queries get logged when debugging is turned on
	 *
	 * @var array
	 */
	
	protected $queryLog = array();
	
	/**
	 * Total query time
	 * 
	 * The amount of time spent executing MySQL queries.  For debugging
	 *
	 * @var float
	 */
	protected $totalQueryTime = 0;
	
	/**
	 * It's constructor time!
	 * 
	 * Pass all the necessary information to connect, and sets the class variables accordingly.  Really, this shouldn't be
	 * called directly, let DatabaseFactory handle all this.
	 *
	 * @param string $dbName
	 * @param string $dbHost
	 * @param string $dbUser
	 * @param string $dbPassword
	 * @abstract 
	 */
	
	public function __construct($dbName,$dbHost,$dbUser,$dbPassword) {
		$this->dbName = $dbName;
		$this->dbHost = $dbHost;
		$this->dbUser = $dbUser;
		$this->dbPassword = $dbPassword;
		$this->initConnection();
	}
	
	/**
	 * Change your database
	 *
	 * @param string $dbName
	 * @abstract
	 */
	
	public function selectDB($dbName) {}
	
	/**
	 * init the connection
	 *
	 * @abstract
	 */
	
	public function initConnection () {}
	
	/**
	 * @see selectDB()
	 *
	 * @param string $dbName
	 * @abstract
	 */
	
	public function dbSelect($dbName) {} 
	
	/**
	 * Quote the string appropriately to prevent SQL injection and issues caused
	 * by ''s, "'s, etc..
	 *
	 * @param string $string
	 * @abstract
	 */
	
	public function quote($string) {}
	
	/**
	 * Execute the damn query!
	 *
	 * @param string $sql
	 * @return DBResult
	 * @abstract
	 */
	
	public function doQuery($sql) {}
	
	/**
	 * return the number of rows affected by the last query
	 *
	 * @return integer
	 * @abstract
	 */
	public function getAffectedRows() {}
	
	/**
	 * Insert a row into the database
	 *
	 * @param string $tableName
	 * @param array $fields
	 * @param array $quotes
	 * @abstract
	 */
	public function doInsert($tableName,$fields,$quotes) {}
	/**
	 * Execute a REPLACE statement
	 *
	 * @param string $tableName
	 * @param array $fields
	 * @param array $quotes
	 * @abstract
	 */
	
	public function doReplace($tableName,$fields,$quotes) {}
	
	/**
	 * Run an update on a row
	 *
	 * @param string $tableName
	 * @param array $fields
	 * @param array $quotes
	 * @param array $wheres
	 * @abstract
	 */
	
	public function doUpdate($tableName,$fields,$quotes,$wheres) {} 
	
	/**
	 * do an insert or update depending on whether the conditions in $wheres is met
	 *
	 * @param string $tableName
	 * @param array $fields
	 * @param array $quotes
	 * @param array $wheres
	 * @abstract
	 */
	
	public function doInsertOrUpdate($tableName,$fields,$quotes,$wheres) {}
	
	/**
	 * generate a set of criteria from $fields and $quotes
	 *
	 * @param array $fields
	 * @param array $quotes
	 * @abstract
	 */
	
	public function generateSet($fields,$quotes) {}
	
	/**
	 * Generate a where statement from the criteria and quote it appropriately
	 *
	 * @param array $where
	 * @param array $quotes
	 * @abstract
	 */
	
	public function generateWhere($where=array(),$quotes=array()) {}
	
	/**
	 * gracefully handle null checks
	 *
	 * @param string $value
	 * @return string 
	 */
	
	public function getTestForMatch($value) {
		if ($value == 'NULL') {
			return ' IS ';
		} else {
			return ' = ';
		}
	}
	
	/**
	 * delete a row
	 *
	 * @param string $tableName
	 * @param array $wheres
	 * @abstract 
	 */
	
	public function doDelete($tableName,$wheres) {}
	
	/**
	 * get the primary key ID of the most recent insert
	 *
	 * return integer
	 */
	
	public function getInsertID() {}
	
	
	private function generateError($errorType) {
		throw new DatabaseException($this,$errorType);
	}

	public function testError($errorType) {
		$this->generateError($errorType);
	}
	public function getQuery() {
		return $this->query;
	}
	
	public function getDBHost() {
		return $this->dbHost;
	}
	
	public function getDBName() {
		return $this->dbName;
	}
	
	public function getConnection() {
		return $this->connection;
	}
	public function numRowsAffected() {
		return $this->getAffectedRows();
	}
}
?>