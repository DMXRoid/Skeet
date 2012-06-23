<?
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	 namespace Skeet\Database;

	/**
	 * class AbstractDatabase
	 *
	 * All databases within the framework will extend this and implement most/all of its
	 * methods
	 * @abstract
	 */
	abstract class AbstractDatabase {

		/**
		 * error constants
		 */
		const ERROR_CONNECT = 1;
		const ERROR_DB_SELECT = 2;
		const ERROR_INSERT = 3;
		const ERROR_UPDATE = 4;
		const ERROR_QUERY = 5;

		/**
		 * query type constants
		 */
		const UPDATE = 1;
		const INSERT = 2;

		/**
		 * The database host (IP or hostname)
		 *
		 * @var string
		 * @access protected
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
		 * The "describe table" keyword, or its
		 * equivalent, for a given database instance.
		 * 
		 * @var string
		 */
		protected $describeKeyword = "DESCRIBE";
		

		/**
		 * The label for a column's name 
		 * in a describe result
		 * @var string
		 */
		
		protected $columnNameLabel = "Field";
		
		/**
		 *	The label for a column's datatype
		 * @var string
		 */
		
		protected $dataTypeLabel = "Type";
		
		/**
		 *	The label for a column's default value
		 * @var string
		 */
		
		protected $defaultValueLabel = "Default";
		
		
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
		 * 
		 */

		public function __construct($dbName,$dbHost,$dbUser,$dbPassword) {
			$this->dbName = $dbName;
			$this->dbHost = $dbHost;
			$this->dbUser = $dbUser;
			$this->dbPassword = $dbPassword;
			$this->initConnection();
		}
		
		/**
		 * 
		 * @param array $columnRow
		 */
		
		abstract public function isColumnPK($columnRow);

		/**
		 * Returns (@see $describeKeyword)
		 * @return string
		 */
		
		public function getDescribeKeyword() {
			return $this->describeKeyword;
		}
		
		/**
		 * Returns (@see $columnNameLabel)
		 * @return string 
		 */
		
		public function getColumnNameLabel() {
			return $this->columnNameLabel;
		}
		
		/**
		 *	Returns (@see $dataTypeLabel)
		 * @return string
		 */
		
		public function getDataTypeLabel() {
			return $this->dataTypeLabel;
		}
		
		/**
		 *	Returns {@see $defaultValueLabel}
		 * @return string
		 */
		
		public function getDefaultValueLabel() {
			return $this->defaultValueLabel;
		}
		
		/**
		 * Change your database
		 *
		 * @param string $dbName
		 * 
		 */

		public function selectDB($dbName) {}

		/**
		 * init the connection
		 *
		 * 
		 */

		public function initConnection () {}

		/**
		 * @see selectDB()
		 *
		 * @param string $dbName
		 * 
		 */

		public function dbSelect($dbName) {}

		/**
		 * Quote the string appropriately to prevent SQL injection and issues caused
		 * by ''s, "'s, etc..
		 *
		 * @param string $string
		 * @return string
		 */

		public function quote($string) {
			return addslashes($string);
		}

		/**
		 *
		 * @param string $sql
		 * @return DBResult
		 * 
		 */

		public function doQuery($sql) {}

		/**
		 * return the number of rows affected by the last query
		 *
		 * @return integer
		 * 
		 */
		public function getAffectedRows() {}

		/**
		 * Insert a row into the database.  The default here,
		 * as far as I know, should work with all SQL compliant 
		 * databases (postgres,mysql,sqlserver,etc...), but you 
		 * can override it as needed.
		 *
		 * @param string $tableName
		 * @param array $fields
		 * @param array $quotes
		 * 
		 */
		public function doInsert($tableName,$fields,$quotes=array()) {
			$sql = "";
			$sql .= "INSERT INTO " . $this->dbName . "." . $tableName . " ";
			$fieldArray = $fields;
			$valueArray = array();
			
			foreach($fields as $key => $value) {
				$valueArray[] = ((isset($quotes[$key]) && $quotes[$key])) ? $this->quote($value) : $value;
			}
			$valueList = "(" . explode(",",$valueList) . ")";
			$fieldList = "(" . explode(",",$fieldList) . ")";
			$sql .= $fieldList . " VALUES " . $valueList;
			
			$this->doQuery($sql);
			$this->insertID = $this->getInsertID();
			return $this->getInsertID();	
		}
		
		/**
		 * Execute a REPLACE statement
		 *
		 * @param string $tableName
		 * @param array $fields
		 * @param array $quotes
		 * 
		 */

		public function doReplace($tableName,$fields,$quotes) {}

		/**
		 * Run an update on a row.  Should work with all SQL
		 * compliant databases (@see doInsert())
		 *
		 * @param string $tableName
		 * @param array $fields
		 * @param array $quotes
		 * @param array $wheres
		 * 
		 */

		public function doUpdate($tableName, $fields, $quotes=NULL, $where, $whereQuotes = NULL, $dbName = '', $escape = true) {
			$sql = "";
			$sql .= "UPDATE " . $this->dbName . "." . $tableName . " SET ";
			$sql .= $this->generateSet($fields, $quotes);
			$sql .= $this->generateWhere($where, $whereQuotes);
			$this->doQuery($sql);
			return true;
		}

		/**
		 * do an insert or update depending on whether the conditions in $wheres is met
		 *
		 * @param string $tableName
		 * @param array $fields
		 * @param array $quotes
		 * @param array $wheres
		 * 
		 */

		public function doInsertOrUpdate($tableName, $fields, $quotes=NULL, $where) {
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $tableName . ' ' . $this->generateWhere($where);
			$result = $this->doQuery($sql);
			if($result->getNumRows() > 0) {
				$this->doUpdate($tableName, $fields, $quotes, $where);
				$type = self::UPDATE;
			}
			else {
				$this->doInsert($tableName,$fields,$quotes);
				$type = self::INSERT;
			}

			return $type;
		}


		
		
		public function doInsertOnDupUpdate($tableName, $fields, $quotes=NULL) {
				$sets = $this->generateSet($fields, $quotes);
				$sql = 'INSERT INTO ' . $this->dbName . '.' . $tableName . ' SET ' . $sets . ' ON DUPLICATE KEY UPDATE ' . $sets;
				return $this->doQuery($sql);
			}
		
		/**
		 * generate a set of criteria from $fields and $quotes
		 *
		 * @param array $fields
		 * @param array $quotes
		 * 
		 */

		public function generateSet($fields,$quotes) {}

		/**
		 * Generate a where statement from the criteria and quote it appropriately
		 *
		 * @param array $where
		 * @param array $quotes
		 * 
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
		 * 
		 */

		public function doDelete($tableName,$wheres) {}

		/**
		 * get the primary key ID of the most recent insert
		 *
		 * @return integer|null
		 */

		public function getInsertID() {}
		
		/**
		 *	Generate a SET statement from values
		 * for use in an UPDATE
		 * @param array $fields
		 * @param array $quotes
		 * @return string 
		 */
		
		public function generateSet($fields, $quotes=NULL) {
			$setArray = array();
			foreach($fields as $key => $value) {
				$value = ((isset($quotes[$key]) && $quotes[$key])) ? $this->quote($value) : $value;
				$setArray[] = $key . ' = ' . $value;
			}
			return implode(", ",$setArray) . " ";
		}
			
		/**
		 * Generate a WHERE statement 
		 * from values for use in an UPDATE
		 * @param array $where
		 * @param array $quotes
		 * @return string 
		 */
		
		public function generateWhere($where=array(), $quotes=NULL) {
			$whereArray = array();
			foreach($where as $key => $value) {
				$value = ((isset($quotes[$key]) && $quotes[$key])) ? $this->quote($value) : $value;
				$whereArray[] = $key . $this->getTestForMatch($value) . $value;
			}
			return "WHERE " . implode(" AND ",$whereArray);
		}
		
		
		/**
		 *
		 * @param type $errorType 
		 */

		protected function generateError($errorType) {
			throw new \Skeet\Exception\DatabaseException($this,$errorType);
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