<?
	/**
	 * mysql_database_result.class.php
	 * 
	 * @package InvisiFramework
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @version 1.0
	 *
	 */

	namespace Skeet\Database;
	class MysqlDatabaseResult extends AbstractDatabaseResult {
		/**
		 * Get the next row in the result set
		 *
		 * @return array
		 */
		
		public function getRow() {
			return mysql_fetch_assoc($this->result);
		}
		
		/**
		 * Get the number of rows in the result
		 *
		 * @return integer
		 */

		public function getNumRows() {
			return mysql_num_rows($this->result);
		}

		
		/**
		 * alias to getNumRows()
		 *
		 * @return integer
		 */
		
		public function numRows() {
			return $this->getNumRows();
		}
	}
?>