<?
	/**
	* @package Skeet
	* @subpackage Database
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/
	
	namespace Skeet\Database\Mysql\Mysqli;
	
	/**
	 * MySQL Database Result Object, using the mysqli driver
	 */

	class MysqliDatabaseResult extends \Skeet\Database\Mysql\MysqlDatabaseResult {
		public function getRow() {
			return $this->getResult()->fetch_assoc();
		}
		
		public function getNumRows() {
			return $this->getResult()->num_rows;
		}
	}
?>