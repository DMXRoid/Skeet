<?
class PostgresqlDatabase extends AbstractDatabase  {
		/**
		 * change the current database
		 *
		 * @param string $dbName
		 */
	
		public function selectDB($dbName) {
			$this->dbName = $dbName;
			$this->initConnection();
		}
		
		/**
		 * generate the connection string and connect to the db
		 *
		 */
		public function initConnection() {
			$dbName = $this->dbName;
			$connectionArray = array();
			if($this->dbHost) {
				$connectionArray[] = "host=" . $this->dbHost;
			}
			
			if($this->dbName) {
				$connectionArray[] = "dbname=" . $dbName;
			}
			
			if($this->dbUser) {
				$connectionArray[] = "user=" . $this->dbUser;
			}
			
			if($this->dbPassword) {
				$connectionArray[] = "password=" . $this->dbPassword;
			}
			
			$connectionString = implode(" ",$connectionArray);
			$this->connection = pg_connect($connectionString) or die($this->generateError(ERROR_CONNECT));
		}
		
		/**
		 * alias to selectDB
		 *
		 * @param string $dbName
		 */
		
		public function dbSelect($dbName) {
			$this->selectDB($dbName);
		}

		/**
		 * quote a string for sql injection prevention
		 *
		 * @param string $value
		 * @return string
		 */
		
		public function quote($value) {

			// Handle special PHP values
			if ($value === null) {
				return 'NULL';
			} else if ($value === false) {
				return '0';
			} else if ($value === true) {
				return '1';
			}

			// Handle all other cases
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			
			$value = '"' . pg_escape_string($this->connection,$value) . '"';
			
			return $value;
		}
		
		/**
		 * Just escape a value, don't quote it
		 *
		 * @param string $value
		 * @return string
		 */
		
		public function escape($value) {

			// Handle special PHP values
			if ($value === null) {
				return 'NULL';
			} else if ($value === false) {
				return '0';
			} else if ($value === true) {
				return '1';
			}

			// Handle all other cases
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			
			$value = pg_escape_string($this->connection,$value);
			
			return $value;
		}
		
		/**
		 * a slightly different quote function used by internal calls
		 *
		 * @param string $value
		 * @return string
		 */
		
		protected function quoteOld($value) {

			// Handle special PHP values
			if ($value === null) {
				return '""';
			} else if ($value === false) {
				return '0';
			} else if ($value === true) {
				return '1';
			}

			// Handle all other cases
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			
			$value = '"' . pg_escape_string($this->connection,$value) . '"';
			
			return $value;
		}
		/**
		 * Execute some SQL
		 *
		 * @param string $sql
		 * @return PostgresqlDatabaseResult
		 */
		
		public function doQuery($sql) {
			
			$this->query = $sql;
			if(DEBUG) {
				$queryBeginTime = microtime(true);
			}
			$result = pg_query($this->connection,$sql) or $this->generateError(self::ERROR_QUERY);
			
			
		/*	if(DEBUG) {
				$queryEndTime = microtime(true);
				$queryLog = array();
				$queryLog["query"] = $sql;
				$dbQueryDebug = new DatabaseQueryDebug();
				$dbQueryDebug->setDatabaseName($this->dbName);
				$dbQueryDebug->setStartTime($queryBeginTime);
				$dbQueryDebug->setEndTime($queryEndTime);
				$dbQueryDebug->setNumRows(mysql_num_rows($result));
				DatabaseDebug::addDatabaseQueryDebug($dbQueryDebug);
			}
		*/	
			
			$resultObject = new DBResult();
			$resultObject->setResult($result);
			
			$this->currentSqlResult = $result;
			return $resultObject;
		}

		public function getAffectedRows() {
			return mysql_affected_rows($this->connection);
		}

		public function changeHost($dbHost,$dbName=NULL,$dbUser=NULL,$dbPassword=NULL) {
			$this->dbHost = $dbHost;
			if(!is_null($dbUser)) {
				$this->dbUser = $dbUser;
			}

			if(!is_null($dbPassword)) {
				$this->dbPassword = $dbPassword;
			}
			if(!is_null($dbName)) {
				$this->dbName = $dbName;
			}

			$this->initConnection();
		}

		public function doInsert($tableName, $fields, $quotes=NULL, $dbName = '', $escape = true) {
			if ($dbName) {
				$this->selectDB($dbName);
			}

			$sql = "";
			$sql .= "INSERT INTO " . $this->dbName . "." . $tableName . " ";
			$fieldList = "(";
			$valueList = "(";
			foreach($fields as $key => $value) {
				$fieldList .= $key . ",";
				if (is_null($quotes)) {
					$value = $this->quote($value);	
				} else if (is_array($quotes) && isset($quotes[$key]) && $quotes[$key]) {
					$value = $this->quoteOld($value);	
				}
				$valueList .= $value . ',';
			}
			$valueList = substr_replace($valueList,'',-1) . ") ";
			$fieldList = substr_replace($fieldList,'',-1) . ") ";
			$sql .= $fieldList . " VALUES " . $valueList;
			
			$this->doQuery($sql);
			$this->insertID = mysql_insert_id($this->connection);
			return $this->getInsertID();
		}

		public function doInsertMultiple($tableName,$fields,$data,$quotes=NULL,$dbName='',$escape = true) {
			$fieldMapArray = array();
			$quoteMapArray = array();

			if ($dbName) {
				$this->selectDB($dbName);
			}
			$sql = "";
			$sql .= "INSERT INTO " . $this->dbName . "." . $tableName . " (";
			$x = 0;
			foreach($fields as $key => $value) {
				$fieldMapArray[$key] = $x;

				if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
					$quoteMapArray[$x] = TRUE;
				}
				$sql .= $key . ",";
				$x++;
			}
			$sql = substr_replace($sql,'',-1) . ") VALUES ";

			foreach($data as $valueArray) {
				$sql .= "(";
				$sortedArray = array();
				foreach($valueArray as $key => $value) {
					$position = $fieldMapArray[$key];
					$sortedArray[$position] = $value;
				}
				foreach($sortedArray as $position => $value) {
					if(isset($quoteMapArray[$position]) && $quoteMapArray[$position])  {
						$value = $this->quote($value);
					}
				}
				$sql .= "),";
			}
			$sql = substr_replace($sql,'',-1);
			$this->doQuery($sql);

		}

		public function doReplaceMultiple($tableName,$fields,$data,$quotes=NULL,$dbName='',$escape = true) {
			$fieldMapArray = array();
			$quoteMapArray = array();

			if ($dbName) {
				$this->selectDB($dbName);
			}
			$sql = "";
			$sql .= "REPLACE INTO " . $this->dbName . "." . $tableName . " (";
			$x = 0;
			foreach($fields as $key => $value) {
				$fieldMapArray[$key] = $x;
				if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
					$quoteMapArray[$x] = TRUE;
				}
				
				$sql .= $key . ",";
				$x++;
			}
			$sql = substr_replace($sql,'',-1) . ") VALUES ";

			foreach($data as $valueArray) {
				$sql .= "(";
				$sortedArray = array();
				foreach($valueArray as $key => $value) {
					$position = $fieldMapArray[$key];
					$sortedArray[$position] = $value;
				}
				foreach($sortedArray as $position => $value) {
					if(isset($quoteMapArray[$position]) && $quoteMapArray[$position])  {
						$value = $this->quote($value);
					}
				}
				$sql .= "),";
			}
			$sql = substr_replace($sql,'',-1);
			$this->doQuery($sql);

		}


		public function doReplace($tableName, $fields, $quotes=NULL) {
			$sql = "";
			$sql .= "REPLACE INTO " . $this->dbName . "." . $tableName . " ";
			$fieldList = "(";
			$valueList = "(";
			foreach($fields as $key => $value) {
					$fieldList .= $key . ",";
					if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key]))  {
						$value = $this->quote($value);
					}
					$valueList .= $value . ',';
			}
			$valueList = substr_replace($valueList,'',-1) . ") ";
			$fieldList = substr_replace($fieldList,'',-1) . ") ";
			$sql .= $fieldList . " VALUES " . $valueList;
			$this->doQuery($sql);

		}

	
	
		public function doUpdate($tableName, $fields, $quotes=NULL, $where, $whereQuotes = NULL, $dbName = '', $escape = true) {
			// TODO: Finish making this backward compatible to Persistent. I'm only adding the $dbName fix. (see bug 4693)
			if ($dbName != '') {
				$this->selectDb($dbName);
			}

			$sql = "";
			$sql .= "UPDATE " . $this->dbName . "." . $tableName . " SET ";
			$sql .= $this->generateSet($fields, $quotes);
			$sql .= $this->generateWhere($where, $whereQuotes);

			$this->doQuery($sql);
			return true;
		}

		public function doInsertOrCancel($tableName, $fields, $quotes=NULL, $where) {
			$sql = 'SELECT * FROM ' . $tableName . ' ' . $this->generateWhere($where);
			$result = $this->doQuery($sql);
			if($result->getNumRows() > 0) {
				return FALSE;
			}
			else {
				return $this->doInsert($tableName,$fields,$quotes);
			}
		}

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

		public function generateSet($fields, $quotes=NULL) {
			$setList = '';
			foreach($fields as $key => $value) {
				if (is_null($quotes)) {
					$value = $this->quote($value);	
				} else if (is_array($quotes) && isset($quotes[$key]) && $quotes[$key]) {
					$value = $this->quoteOld($value);	
				}
				$setList .= $key . ' = ' . $value . ',';
			}
			return substr_replace($setList,'',-1) . ' ';
		}
		
		public function generateWhere($where=array(), $quotes=NULL) {
			if(empty($where)) {
				return '';
			}
			else {
				$whereClause = 'WHERE ';
				foreach($where as $key => $value) {
					if (is_null($quotes)) {
						$value = $this->quote($value);
					} else if (is_array($quotes) && isset($quotes[$key]) && $quotes[$key]) {
						$value = $this->quoteOld($value);
					}
					$whereClause .= $key . $this->getTestForMatch($value) . $value . ' AND ';
				}
				$whereClause = substr_replace($whereClause, '', -5);
			}
			return $whereClause;
		}
		
		public function getTestForMatch($value) {
			if ($value == 'NULL') {
				return ' IS ';
			} else {
				return ' = ';
			}
		}

		public function getTestForNonMatch($value) {
			if ($value == 'NULL') {
				return ' IS NOT ';
			} else {
				return ' != ';
			}
		}

		public function doSelect($tableName,$fields=array(),$where=array()) {
			if (empty($fields)) {
				$selected = '*';
			} else if (is_array($fields)) {
				$selected = implode(', ',$fields);	
			} else {
				// assume string
				$selected = $fields;
			}
			
			$sql = 'SELECT ' . $selected . ' FROM ' . $this->dbName . '.' . $tableName . ' ' . $this->generateWhere($where);
			$result = $this->doQuery($sql);
			return $result;
		}

		public function doDelete($tableName,$where) {
			$sql = 'DELETE FROM ' . $tableName . ' ' . $this->generateWhere($where);
			$this->doQuery($sql);
		}

		public function getInsertID() {
			if(!$this->insertID){
				$this->insertID = mysql_insert_id($this->connection);
			}
			return $this->insertID;
		}

		public function getRow() {
			return mysql_fetch_assoc($this->mysqlResult);
		}
		
		public function getMySQLError() {
			return mysql_error($this->connection);
		}

		public function getNumRows() {
			return mysql_num_rows($this->mysqlResult);
		}

		public function numRows() {
			return $this->getNumRows();
		}
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


	class DBResult {
		private $result;


		public function setResult($result) {
			$this->result = $result;
		}

		public function getResultAsArray() {
			$returnArray = array();
			while($row = $this->getRow()) {	
				$returnArray[] = $row;	
			}
			return $returnArray;
		}

		public function getRow() {
			return mysql_fetch_assoc($this->result);
		}

		public function getNumRows() {
			return mysql_num_rows($this->result);
		}

		public function numRows() {
			return $this->getNumRows();
		}
	}

?>
