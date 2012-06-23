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
	 * Sqlsrv database result wrapper
	 */
	
	class SqlsrvDatabaseResult extends \Skeet\Database\AbstractDatabaseResult {
		/**
		 * Get the next row in the result set
		 * Automatically sets the fetch type 
		 * to SQLSRV_FETCH_ASSOC
		 * 
		 * @return array
		 */
		public function getRow() {
			return sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC);
		}
		
		public function getNumRows() {
			return sqlsrv_num_rows($this->result);
		}
	}
?>