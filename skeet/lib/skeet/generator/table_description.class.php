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
		protected $databaseName;
		protected $tableName;
		protected $fields = array();
		protected $oneToManyCollections = array();
		protected $manyToManyCollections = array();
		protected $targetObjects = array();
		protected $targetObjectFields = array();
		protected $displayNameField;
		protected $primaryKeyFieldName;

		public function __construct($tableName,$databaseName=null) {
			$this->setTableName($tableName);
			if(!$databaseName) {
				$databaseName = \Skeet\DatabaseFactory::getDatabase()->getDBName();
			}
			$this->setDatabaseName($databaseName);
		}

		public function getTableName() {
			return $this->tableName;
		}

		public function getDatabaseName() {
			return $this->databaseName;
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
			$db = \Skeet\DatabaseFactory::getDatabase();
			$sql = $db->getDescribeKeyword() . " " . $db->getEscapeOpenCharacter() . $this->getTableName() . $db->getEscapeCloseCharacter() .  "";
			$result = $db->doQuery($sql);
			while($row = $result->getRow()) {
				if(\Skeet\DatabaseFactory::getDatabase()->isColumnPK($row)) {
					$this->setPrimaryKeyFieldName($row[\Skeet\DatabaseFactory::getDatabase()->getColumnNameLabel()]);
				}
			}
		}
		
		

		public function getClassName() {
			return str_replace(" ","",ucwords(str_replace("_"," ",$this->getTableName())));
		}

		public function setTableName($tableName) {
			$this->tableName = $tableName;
		}

		public function setDatabaseName($databaseName) {
			$this->databaseName = $databaseName;
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
			$this->targetObjectFields[$foreignKeyName] = $targetDescription;
		}

		public function addOneToManyCollection($tableName,$foreignDescription,$foreignKeyName) {
			$this->oneToManyCollections[$foreignDescription] = array(
				"table_name" => $tableName,
				"collection_type" => \Skeet\Skeet::COLLECTION_TYPE_ONE_TO_MANY,
				"foreign_key_name" => $foreignKeyName,
				"where" => array()
			);
		}

		public function addManyToManyCollection($tableName,$joinTableName,$foreignDescription,$foreignKeyName,$foreignJoinKey,$localJoinKey,$extraColumns) {
			$this->manyToManyCollections[$foreignDescription] = array(
				"join_table" => $joinTableName,
				"table_name" => $tableName,
				"foreign_key_name" => $foreignKeyName,
				"foreign_join_key" => $foreignJoinKey,
				"local_join_key" => $localJoinKey,
				"class_name" => str_replace(" ","",ucwords(str_replace("_"," ",$tableName))),
				"collection_type" => \Skeet\Skeet::COLLECTION_TYPE_MANY_TO_MANY,
				"extra_fields" => $extraColumns,
				"where" => array()
			);
		}

		public function getTargetObjectByField($fieldName) {
			return (isset($this->targetObjectFields[$fieldName])) ? $this->targetObjectFields[$fieldName] : null;
		}

		public function hasTargetObjectField($fieldName) {
			return isset($this->targetObjectFields[$fieldName]);
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
						if(is_array($value2)) {
							$innerTempValue = 'array(';
							$innerTempValueArray = array();
							foreach($value2 as $key3 => $value3) {
								$innerTempValueArray[] = '"' . $key3 . '" => "' . $value3 . '"';
							}
							$tempValueArray[] = '"' . $key2 . '" => ' . $innerTempValue . implode(",",$innerTempValueArray) . ")";
						}
						else {
							$tempValueArray[] = '"' . $key2 . '" => "' . $value2 . '"';
						}

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