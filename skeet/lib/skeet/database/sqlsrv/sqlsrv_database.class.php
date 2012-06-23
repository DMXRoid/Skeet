<?
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2012, Matthew Schiros
	 */

	namespace Skeet\Database\Sqlsrv;

	/**
	 * SQL Server Database Object.  Uses the sqlsrv_* 
	 * set of functions (http://us3.php.net/sqlsrv)
	 */
	
	class SqlsrvDatabase extends \Skeet\Database\AbstractDatabase {
		
		protected $describeKeyword = "exec sp_columns";
		protected $columnNameLabel = "COLUMN_NAME";
		protected $dataTypeLabel = "TYPE_NAME";
		protected $defaultValueLabel = "COLUMN_DEF";
		
		public function initConnection() {
			/**
			 * Build the connection info array
			 */
			
			$connectionInfo = array(
				"UID" => $this->dbUser,
				"PWD" => $this->dbPassword,
				"Database" => $this->dbName
			);
			
			/**
			 * Create the connection and store it
			 */
			
			$this->connection = sqlsrv_connect($this->dbHost,$connectionInfo) or die(sqlsrv_errors());
		}
		
		/**
		 *	There's no native "change the current DB" method for
		 * sqlsrv, so close the existing connection, change the DB
		 * name stored in the object, and then re-create the connection
		 * 
		 * @param string $dbName 
		 */
		
		public function selectDB($dbName) {
			$this->dbName = $dbName;
			sqlsrv_close($this->connection);
			$this->initConnection();
		}
		
		public function doQuery($sql) {
			$this->query = $sql;
			
			/**
			 * We want a result with a static cursor, 
			 * because that's the only way to use sqlsrv_num_rows()
			 */
			
			$result = sqlsrv_query($this->connection,$this->query,array(),array("Scrollable" => SQLSRV_CURSOR_STATIC)) or die(sqlsrv_errors());
			
			$resultObject = new SqlsrvDatabaseResult();
			$resultObject->setResult($result);
			return $resultObject;
		}
		
		public function getInsertID($tableName) {
			$sql = "SELECT IDENT_CURRENT('" . $tableName . "') AS last_insert_id";
			$row = $this->doQuery($sql)->getRow();
			return $row["last_insert_id"];
		}
		
		public function isColumnPK($columnRow) {
			return ($columnRow["TYPE_NAME"] == "int identity") ? true : false;
		}
	}
?>