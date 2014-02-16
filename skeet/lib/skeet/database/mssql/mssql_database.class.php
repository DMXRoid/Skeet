<?

	namespace Skeet\Database\Mssql;
	
	class MssqlDatabase extends \Skeet\Database\AbstractDatabase {
		protected $describeKeyword = "exec sp_columns";
		protected $columnNameLabel = "COLUMN_NAME";
		protected $dataTypeLabel = "TYPE_NAME";
		protected $defaultValueLabel = "COLUMN_DEF";
		protected $escapeOpenCharacter = '[';
		protected $escapeCloseCharacter = ']';
		
		
		public function initConnection() {
			if($this->connection = mssql_connect($this->dbHost,$this->dbUser,$this->dbPassword)) {
			}
			else {
				die(mssql_get_last_message());
			}
		}
		
		public function quote($value) {
			return "'" . addslashes($value) . "'";
		}
		
		public function doQuery($sql) {
			$this->query = $sql;
			
			/**
			 * We want a result with a static cursor, 
			 * because that's the only way to use sqlsrv_num_rows()
			 */
			
			$result = mssql_query($this->query,$this->connection) or die($this->generateError(self::ERROR_QUERY));
			
			$resultObject = new MssqlDatabaseResult();
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