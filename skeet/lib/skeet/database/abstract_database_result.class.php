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
	 * Abstract database result class
	 * 
	 * Parent class of all database result classes
	 * @abstract
	 */
	
	

	abstract class AbstractDatabaseResult {
		/**
		 * Enter description here...
		 *
		 * @var MySQLResult|PGSQLResult
		 * @access protected
		 */
		protected $result;

		/**
		 * Set the object $result var
		 *
		 * @param unknown_type $result
		 * @access public
		 */

		public function setResult($result) {
			$this->result = $result;
		}

		/**
		 * Dump all the rows in the result into an array
		 *
		 * @access public
		 * @return array
		 */
		
		public function getResultAsArray() {
			$returnArray = array();
			while($row = $this->getRow()) {	
				$returnArray[] = $row;	
			}
			return $returnArray;
		}
		/**
		 * Return a row. Must be defined in child classes
		 *
		 * @return array
		 * @access public
		 */
		
		public function getRow() { }

		/**
		 * Return the number of rows retreived in the $result
		 *
		 * @access public
		 * @return integer
		 */
		
		public function getNumRows() { }

		/**
		 * alias to getNumRows()
		 *
		 * @return integer
		 * @access public
		 */
		
		public function numRows() { }
	}
?>