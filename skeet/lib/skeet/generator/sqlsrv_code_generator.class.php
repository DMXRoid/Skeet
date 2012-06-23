<?
	/**
	* @package Skeet
	* @subpackage Generator
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Generator;

	/**
	* Sqlsrv-based code generator
	*/
	
	class SqlsrvCodeGenerator extends AbstractCodeGenerator {
		
		/**
		 * Grab the list of tables in the current database.
		 */
		
		protected function loadTables() {
			$tableArray = array();
			$sql = "exec sp_tables @table_type = \"'TABLE'\"";
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			while($row = $result->getRow()) {
				$tableArray[$row["TABLE_NAME"]] = $row["TABLE_NAME"];
			}
			$this->tableArray = $tableArray;
		}
		
		/**
		 *	Process a table into a TableDescription
		 * object, draw the correlations between it 
		 * and other tables, etc.  
		 * 
		 * @param string $tableName 
		 */
		protected function processTable($tableName) {
			/**
			 * Get the table description
			 */
			$sql = "exec sp_columns " . $tableName;
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			
			$manyToManyMatches = array();
		}
	}
