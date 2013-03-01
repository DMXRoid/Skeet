<?
	/**
	* @package Skeet
	* @subpackage Database
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Database\Mysql;
	/**
	 * MySQL Database Result Object, using the mysql driver
	 */

	class MysqlDatabaseResult extends \Skeet\Database\AbstractDatabaseResult {
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