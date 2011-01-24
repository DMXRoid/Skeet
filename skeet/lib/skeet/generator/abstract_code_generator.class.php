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
	* AbstractCodeGenerator
	* @abstract
	*/

	abstract class AbstractCodeGenerator {
		protected $tableArray = array();
		protected $tableDescriptionArray = array();

		protected $manyToManyPattern = "/([^2]*)2(.*)/";


		abstract protected function loadTables();
		abstract protected function processTable($tableName);

		public function doGenerate() {
			\Skeet\Factory\ThemeFactory::setCurrentThemeName("generator");
			$this->loadTables();
			$this->processTables();
			$this->generateCode();
		}

		protected function processTables() {
			foreach($this->getTableArray() as $tableName) {
				$this->processTable($tableName);
			}
		}

		protected function generateCode() {
			foreach($this->getTableDescriptionArray() as $tableDescription) {
				ob_start();
				\Skeet\Factory\ComponentFactory::getComponent("model template")->addSetting("table_description",$tableDescription)->render();
				file_put_contents(\Skeet\Skeet::getConfig("application_lib_path") . 'generated/model/' . $tableDescription->getTableName() . ".class.php",ob_get_contents());
				ob_end_clean();
			}
		}

		protected function getTableArray() {
			return $this->tableArray;
		}

		protected function getTableDescriptionArray() {
			return $this->tableDescriptionArray;
		}

		protected function hasTable($tableName) {
			if(isset($this->tableArray[$tableName])) {
				return true;
			}
			return false;
		}

		protected function getManyToManyPattern() {
			return $this->manyToManyPattern;
		}

		/**
		* @access protected
		* @param string $tableName
		* @return TableDescription
		* @see $tableDescriptionArray
		*/
		protected function getTableDescription($tableName) {
			if(!isset($this->tableDescriptionArray[$tableName])) {
				$this->tableDescriptionArray[$tableName] = new TableDescription($tableName);
			}
			return $this->tableDescriptionArray[$tableName];
		}
	}

?>
