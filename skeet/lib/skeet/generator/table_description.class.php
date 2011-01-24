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
	* Holds descriptions of tables for use in
	* code generation.
	*/

	class TableDescription {
		private $tableName;
		private $fields = array();
		private $oneToManyCollections = array();
		private $manyToManyCollections = array();
		private $targetObjects = array();
		private $displayNameField;
		private $primaryKeyFieldName;

		public function __construct($tableName) {
			$this->setTableName($tableName);
		}

		public function getTableName() {
			return $this->tableName;
		}

		public function getPrimaryKeyFieldName() {
			if(!$this->primaryKeyFieldName) {
				$this->checkPrimaryKeyFieldName();
			}
			return $this->primaryKeyFieldName;
		}

		public function getDisplayNameField() {
			return $this->displayNameField;
		}

		public function getFields() {
			return $this->fields;
		}

		public function getOneToManyCollections() {
			return $this->oneToManyCollections;
		}

		public function getManyToManyCollections() {
			return $this->manyToManyCollections;
		}

		public function getCollections() {
			return array_merge($this->getOneToManyCollections(),$this->getManyToManyCollections());
		}

		public function getTargetObjects() {
			return $this->targetObjects;
		}

		protected function checkPrimaryKeyFieldName() {
			$sql = "DESCRIBE " . $this->getTableName();
			$result = \Skeet\Factory\DatabaseFactory::getDatabase();
			while($row = $result->getRow()) {
				if($row["Key"] == "PRI") {
					$this->setPrimaryKeyFieldName($row["Field"]);
				}
			}
		}

		public function getClassName() {
			return str_replace(" ","",ucwords(str_replace("_"," ",$this->getTableName())));
		}

		public function setTableName($tableName) {
			$this->tableName = $tableName;
		}

		public function addField($fieldName,$dataType,$value,$defaultValue=NULL) {
			$this->fields[$fieldName] = array(
				"field_name" => $fieldName,
				"data_type" => $dataType,
				"value" => $value,
				"default_value" => $defaultValue
			);
		}

		public function setDisplayNameField($displayNameField) {
			$this->displayNameField = $displayNameField;
		}

		public function setPrimaryKeyFieldName($primaryKeyFieldName) {
			$this->primaryKeyFieldName = $primaryKeyFieldName;
		}

		public function addTargetObject($tableName,$targetDescription,$foreignKeyName) {
			$this->targetObjects[$targetDescription] = array(
				"table_name" => $tableName,
				"foreign_key_name" => $foreignKeyName
			);
		}

		public function addOneToManyCollection($tableName,$foreignDescription,$foreignKeyName) {
			$this->oneToManyCollections[$foreignDescription] = array(
				"table_name" => $tableName,
				"foreign_key_name" => $foreignKeyName
			);
		}

		public function addManyToManyCollection($tableName,$joinTableName,$foreignDescription,$foreignKeyName,$foreignJoinKey,$localJoinKey,$extraColumns) {
			$this->manyToManyCollections[$foreignDescription] = array(
				"join_table" => $joinTableName,
				"table_name" => $tableName,
				"foreign_key_name" => $foreignKeyName,
				"foreign_join_key" => $foreignJoinKey,
				"local_join_key" => $localJoinKey,
				"extra_columns" => $extraColumns
			);
		}

		public function arrayToGeneratorText($arrayToConvert) {
			$tempKeyArray = array();
			
			$string = ' array(
						';
			foreach($arrayToConvert as $key => $value) {
				if(is_null($value)) {
					$value = "NULL";
				}
				elseif(is_array($value)) {
					$tempValue = 'array(';
					$tempValueArray = array();
					foreach($value as $key2 => $value2) {
						$tempValueArray[] = '"' . $key2 . '" => "' . $value2 . '"';
					}
					$value = $tempValue . implode(",",$tempValueArray) . ")";
				}
				else {
					$value = '"' . $value . '"';
				}
				$keyString = ' "' . $key . '" => ' . $value;
				$tempKeyArray[] = $keyString;
			
			}
			
			$string .= implode(",\n\t\t\t\t\t\t",$tempKeyArray) . "\n\t\t\t\t\t)";
			return $string;
		}
	}

?>