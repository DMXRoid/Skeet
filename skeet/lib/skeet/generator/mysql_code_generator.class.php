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
	* MySQL-based code generator
	*/

	class MysqlCodeGenerator extends AbstractCodeGenerator {

		protected function loadTables() {
			$tableArray = array();
			$sql = "SHOW TABLES";
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			while($row = $result->getRow()) {
				$rowKey = array_shift(array_keys($row));
				$tableArray[$row[$rowKey]] = $row[$rowKey];
			}
			$this->tableArray = $tableArray;
		}

	}

?>
