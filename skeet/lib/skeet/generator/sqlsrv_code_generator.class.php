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
				if(!preg_match("/[^a-z_2]/",$row["TABLE_NAME"]) || in_array($row["TABLE_NAME"],$this->additionalTablesToLoad)) { 
					$tableArray[$row["TABLE_NAME"]] = $row["TABLE_NAME"];
				}
			}
			$this->tableArray = $tableArray;
		}
		
		
	}
