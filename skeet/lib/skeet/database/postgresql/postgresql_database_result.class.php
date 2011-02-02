<?
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */
	
	/**
	 * PostgreSQL DB result object
	 */

	class PostgresqlDatabaseResult extends AbstractDatabaseResult {
		/**
		 * Get the next row in the result set
		 *
		 * @return array
		 */
		
		public function getRow() {
			return pg_fetch_assoc($this->result);
		}

		/**
		 * Get the number of rows in the result
		 *
		 * @return integer
		 */
		
		public function getNumRows() {
			return pg_num_rows($this->result);
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