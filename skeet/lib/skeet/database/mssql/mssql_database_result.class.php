<?

	namespace Skeet\Database\Mssql;
	
	class MssqlDatabaseResult extends \Skeet\Database\AbstractDatabaseResult {
		public function getRow() {
			return mssql_fetch_assoc($this->result);
		}
		
		public function getNumRows() {
			return mssql_num_rows($this->result);
		}
	}
?>