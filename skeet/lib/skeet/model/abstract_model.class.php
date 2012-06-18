<?
	/**
	 * abstract_model.class.php
	 * 
	 * Abstract data driven object class
	 * 
	 * @package Skeet
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2010, Matthew Schiros
	 * @license http://www.invisihosting.com/skeet/license.html 
	 * 
	 * 
	 */
	namespace Skeet\Model;

	abstract class AbstractModel {
		
		/**
		 * Database name for the table
		 * @access protected
		 * @var string
		 */
		protected $databaseName;
		
		/**
		 * Name of the table the data is pulled from
		 * 
		 * @access protected
		 * @var string
		 */
		protected $tableName;
		
		/**
		 * Primary key value for the current object
		 * in the format:
		 * array("primary_key_field_name" => "field_name", "primary_key_value" => "field_value")
		 * 
		 * @var array
		 * @access protectedS
		 */
		protected $primaryKey = array("primary_field_key_name" => "","primary_key_value" => 0);
		
		/**
		 * A list of fields and datatypes in (@link $tableName)
		 * the get() and set() methods will compare against this
		 * 
		 * In the format:
		 * 
		 * $dataStructure["field_name"] = array("data_type" => "field_data_type", "length" => "field_length","value" => "field_value");
		 * @see get(), set()
		 * @access protected
		 * @var array
		 */
		
		protected $dataStructure = array();
		
		/**
		 * A list of fields that have been updated
		 * @see save()
		 * @access protected
		 * @var array
		 */
		
		protected $updatedFields = array();
		
		
		/**
		 * Collection definitions
		 * @see getCollection()
		 * @access protected
		 * @var arary
		 */
		
		protected $collectionDefinitions = array();
		
		/**
		 * Collection holder
		 * @see getCollection()
		 * @access protected
		 * @var array
		 */
		
		protected $collections = array();
		
		/**
		 *	Sometimes you just want a count of a collection without
		 * loading the whole damn thing into memory.
		 * 
		 * @see getCollectionCount()
		 * @access protected
		 * @var array
		 */

		protected $collectionCounts = array();

		/**
		 * One to one object definitions
		 * @see getTargetObject()
		 * @access protected 
		 * @var array
		 */
		
		protected $targetObjectDefinitions = array();
		
		/**
		 * One to one object holder
		 * @see getTargetObject()
		 * @access protected
		 * @var array
		 */
		
		protected $targetObjects = array();
		
		
		/**
		 * Required fields for submit, can be set at will
		 * or in the subclass
		 * @see addRequiredField(),getRequiredFields(),validateInput()
		 * @access protected
		 * @var arary
		 */
		
		protected $requiredFields = array();
		
		/**
		 * Specific validation types.  If a field does
		 * not exist, falls back to the datatype in (@link $dataStructure)
		 * @see validateInput(),setValidationType()
		 * @access protected
		 * @var array
		 */
		
		protected $validationTypes = array();
		
		/**
		 * Validation errors
		 * @see validateInput(),getValidationErrors()
		 * @access protected
		 * @var array
		 */
		
		protected $validationErrors = array();
		
		
		/**
		 * Display name for fields in CRUD functionality.
		 * Optional.  If they don't exist, just the field name 
		 * will be used
		 * 
		 * @see getDisplayName()
		 * @access protected
		 * @var array
		 */
		
		protected $displayNames = array();
		
		/**
		 * Display errors.  If you want to standardize error output for 
		 * a field across the application, you can do so in the subclasses
		 * 
		 * @see validateInput()
		 * @access protected
		 * @var array
		 */
		
		protected $displayErrors = array();

		protected $displayNameField;

		protected $notes;

		protected $events;

		protected $documents;
		
		/**
		 * Generic Constructor
		 * @access public
		 * @var integer|NULL
		 * @var string|NULL
		 * @var array|NULL
		 */
		public function __construct($primaryKeyID=NULL,$customSQL=NULL,$dataRow=NULL) {
			$this->preConstruct();
			$this->databaseName = \Skeet\Skeet::getConfig("database.default.database_name");
			$db = \Skeet\DatabaseFactory::getDatabase();
			if($primaryKeyID) {
				$sql = "SELECT * FROM `" . $this->getDatabaseName() . "`.`" . $this->getTableName() . "`
							WHERE `" . $this->getPrimaryKeyField() . "` = " . $db->quote($primaryKeyID);
				$dataRow = $db->doQuery($sql)->getRow();
			}
			elseif($customSQL) {
				$dataRow = $db->doQuery($customSQL)->getRow();
			}
			
			if($dataRow) {
				foreach($dataRow as $key => $value) {
					if($key == $this->getPrimaryKeyField()) {
						$this->setPrimaryKeyID($value);
					}
					$this->set($key,$value);
				}
			}
			$this->postConstruct();
		}

		protected function preConstruct() { }
		protected function postConstruct() { }
		public function getPrimaryKeyField() {
			return $this->primaryKey["primary_key_field_name"];
		}
		
		public function setPrimaryKeyID($primaryKeyID) {
			$this->primaryKey["primary_key_value"] = $primaryKeyID;
		}
			
		/**
		 * Gets (@link $tableName)
		 * @access public
		 * @return string
		 */
		
		public function getTableName() {
			return $this->tableName;
		}
		
		/**
		 * Gets (@link $databaseName)
		 * @access public
		 * @return string
		 */
		
		public function getDatabaseName() {
			return $this->databaseName;
		}
		
		/**
		 * Gets the (@link $primaryKeyID)
		 * @access public
		 * @return integer
		 */
		
		public function getID() {
			return $this->primaryKey["primary_key_value"];
		}
		
		/**
		 * Gettor for data values.  Checks (@link $dataStructure) for the valid existence of a field
		 * @access public
		 * @return mixed
		 */
		
		public function get($key) {
			try {
				if(isset($this->dataStructure[$key]) && is_array($this->dataStructure[$key])) {
					return $this->dataStructure[$key]["value"];
				}
				else {
					error_log($key);
					throw new \Skeet\Exception\InvalidGetCallException($key);
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Settor for data values.  Checks (@link $dataStructure) for the valid existence of a field
		 * @access public 
		 * @var string
		 * @var mixed
		 */
		
		public function set($key,$value) {
			try {
				if(isset($this->dataStructure[$key]) && is_array($this->dataStructure[$key])) {
					$this->dataStructure[$key]["value"] = $value;
					$this->updatedFields[$key] = $value;
				}
				else {
					throw new \Skeet\Exception\InvalidSetCallException($key);
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Save function.  Writes values in (@link $updatedFields) to the database
		 * @access public
		 */
	
		
		public function save() {
			$db = \Skeet\DatabaseFactory::getDatabase();
			$tableName = $this->getTableName();
			$fields = array();
			$quotes = array();
			
			foreach($this->updatedFields as $key => $value) {
				if(!$value) {
					if(0 && is_null($this->dataStructure[$key]["default_value"])) {
						$fields[$key] = "NULL";
						$quotes[$key] = false;
					}
					else {
						$fields[$key] = $value;	
						$quotes[$key] = true;	
					}
				}
				else {
					$fields[$key] = $value;	
					$quotes[$key] = true;	
				}
				
			}
			$fields["last_modified_date"] = date("Y-m-d H:i:s");
			$quotes["last_modified_date"] = true;
			if($this->getID()) {
				$wheres = array($this->getPrimaryKeyField() => $this->getID());
				$db->doInsertOrUpdate($tableName,$fields,$quotes,$wheres);
			}
			else {
				$fields["creation_datetime"] = "NOW()";
				$db->doInsert($tableName,$fields,$quotes);
				$this->setPrimaryKeyID($db->getInsertID());
			}
		}

		/**
		 *
		 * implement this for real at some poitn
		 */

		public function getCollectionCount($tableName) {
			try {
				if(isset($this->collectionDefinitions[$tableName])) {
					if(!isset($this->collectionCounts[$tableName])) {
						$this->checkCollection($tableName);
					}
					return $this->collections[$tableName];
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch(Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Gets a collection model object.  Calls the checkCollection function if 
		 * one isn't stored yet
		 * 
		 * @see checkCollection()
		 * @link $collections $collectionDefinitions
		 * @var string
		 * @return AbstractCollection
		 */
		
		public function getCollection($tableName) {
			try {
				if(isset($this->collectionDefinitions[$tableName])) {
					if(!isset($this->collections[$tableName]) || !is_object($this->collections[$tableName])) {
						$this->checkCollection($tableName);
					}
					return $this->collections[$tableName];
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch(Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Checks a collection via ModelCollectionFactory
		 * 
		 * @see getCollection()
		 * @link $collections $collectionDefinitions
		 * @access protected
		 * @var string
		 */
		
		protected function checkCollection($tableName) {
			
			//$this->collections[$tableName] = \Skeet\ModelCollectionFactory::getModelCollection($this->collectionDefinitions[$tableName]["table_name"],$this->collectionDefinitions[$tableName]["collection_options"]);
			$this->collections[$tableName] = \Skeet\ModelCollectionFactory::getModelCollection($this->collectionDefinitions[$tableName]["table_name"],$this->getCollectionOptionArray($tableName));
		}
		
		protected function getCollectionOptionArray($tableName) {
			if(!isset($this->collectionDefinitions[$tableName]["collection_options"]) || !is_array($this->collectionDefinitions[$tableName]["collection_options"])) {
				$this->checkCollectionOptionArray($tableName);
			}
			return $this->collectionDefinitions[$tableName]["collection_options"];
		}
		
		protected function checkCollectionOptionArray($tableName) {
			$collectionOptionArray = array();
			switch($this->collectionDefinitions[$tableName]["collection_type"]) {
				case \Skeet\Skeet::COLLECTION_TYPE_MANY_TO_MANY:
					$joinSQL = " INNER JOIN `" . $this->collectionDefinitions[$tableName]["join_table"] . "` 
									 ON `" .  $this->collectionDefinitions[$tableName]["table_name"] . "`." . $this->collectionDefinitions[$tableName]["foreign_key_name"] . " = `" . $this->collectionDefinitions[$tableName]["join_table"] . "`." . $this->collectionDefinitions[$tableName]["foreign_join_key"] . "
									 AND `" . $this->collectionDefinitions[$tableName]["join_table"] . "`.is_retired = 0 
									 AND `" . $this->collectionDefinitions[$tableName]["join_table"] . "`." . $this->collectionDefinitions[$tableName]["local_join_key"] . " = '" . $this->getID() . "'";
					$collectionOptionArray["join_table"] = array($joinSQL);
					$where = array();
					if(!isset($this->collectionDefinitions[$tableName]["show_retired"]) || !$this->collectionDefinitions[$tableName]["show_retired"]) {
						$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`.is_retired"] = "0";
					}
					else {
						$collectionOptionArray["show_retired"] = true;
					}
					$collectionOptionArray["where"] = $where;
					if(isset($this->collectionDefinitions[$tableName]["order_by"])) {
						$collectionOptionArray["order_by"] = $this->collectionDefinitions[$tableName]["order_by"];
					}
					break;
					
				case \Skeet\Skeet::COLLECTION_TYPE_ONE_TO_MANY:
				default:
					$where = array();
					$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`." . $this->collectionDefinitions[$tableName]["foreign_key_name"]] = $this->getID();
					$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`.is_retired"] = "0";
					$collectionOptionArray["where"] = $where;
					if(isset($this->collectionDefinitions[$tableName]["order_by"])) {
						$collectionOptionArray["order_by"] = $this->collectionDefinitions[$tableName]["order_by"];
					}
					break;
			}
			$this->collectionDefinitions[$tableName]["collection_options"] = $collectionOptionArray;
		}
		
		/**
		 * Gets a one to one object.  Calls checkTargetObject if one isn't 
		 * stored yet
		 * 
		 * @see checkTargetObject()
		 * @link $targetObjects $targetObjectDefinitions
		 * @access public
		 * @var string
		 */
		
		public function getTargetObject($tableName) {
			try {
				if(isset($this->targetObjectDefinitions[$tableName])) {
					if(!isset($this->targetObjects[$tableName]) || !is_object($this->targetObjects[$tableName])) {
						$this->targetObjects[$tableName] = \Skeet\ModelFactory::getModel($this->targetObjectDefinitions[$tableName]["table_name"],$this->get($this->targetObjectDefinitions[$tableName]["foreign_key_name"]));
					}
					return $this->targetObjects[$tableName];
				}
				else {
					error_log($this->getTableName() . "->" . $tableName);
					throw new InvalidGetTargetObjectException();
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		public function addObjectToCollection($tableName,$modelObject) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
					
					$db = \Skeet\DatabaseFactory::getDatabase();
					
					$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
					$fields = array();
					$quotes = array();
					$wheres = array();
					
					$fields[$this->collectionDefinitions[$tableName]["local_join_key"]] = $this->getID();
					$fields[$this->collectionDefinitions[$tableName]["foreign_join_key"]] = $modelObject->getID();
					
					$wheres = $fields;
					
					$fields["is_retired"] = "0";
					
					$fields["creation_datetime"] = date("Y-m-d H:i:s");
					$quotes["creation_datetime"] = true;

					$this->getCollection($tableName)->add($modelObject);
					$db->doInsertOrUpdate($targetTable,$fields,$quotes,$wheres);
					
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch (Exception $e){
				$e->processException();
			}
		}
		
		public function removeObjectFromCollection($tableName,$modelObject) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
					$db = \Skeet\DatabaseFactory::getDatabase();
					
					$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
					$fields = array();
					$quotes = array();
					$wheres = array();

					$fields["is_retired"] = "1";
					
					$wheres[$this->collectionDefinitions[$tableName]["local_join_key"]] = $this->getID();
					$wheres[$this->collectionDefinitions[$tableName]["collection_join_key"]] = $modelObject->getID();
					
					
					$this->getCollection($tableName)->remove($modelObject);
					$db->doUpdate($targetTable,$fields,$quotes,$wheres);
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch (Exception $e){
				$e->processException();
			}
		}
		
		public function setExtraCollectionField($tableName,$fieldName,$modelObject,$value) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
					if(isset($this->collectionDefinitions[$tableName]["extra_fields"][$fieldName])) {
						$db = \Skeet\DatabaseFactory::getDatabase();
						
						$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
						$fields = array();
						$quotes = array();
						$wheres = array();
						
						$fields[$fieldName] = $value;
						$quotes[$fieldName] = true;
						
						$wheres[$this->collectionDefinitions[$tableName]["local_join_key"]] = $this->getID();
						$wheres[$this->collectionDefinitions[$tableName]["collection_join_key"]] = $modelObject->getID();
						
						$db->doUpdate($targetTable,$fields,$quotes,$wheres);
					}
					else {
						throw new InvalidGetExtraCollectionFieldException();
					}
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		public function setNonExtraCollectionField($tableName,$fieldName,$modelObject,$value) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
						$db = \Skeet\DatabaseFactory::getDatabase();
						
						$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
						$fields = array();
						$quotes = array();
						$wheres = array();
						
						$fields[$fieldName] = $value;
						$quotes[$fieldName] = true;
						
						$wheres[$this->collectionDefinitions[$tableName]["local_join_key"]] = $this->getID();
						$wheres[$this->collectionDefinitions[$tableName]["collection_join_key"]] = $modelObject->getID();
						
						$db->doUpdate($targetTable,$fields,$quotes,$wheres);
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		public function getExtraCollectionField($tableName,$fieldName,$modelObject) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
					if(isset($this->collectionDefinitions[$tableName]["extra_fields"][$fieldName])) {
						$db = \Skeet\DatabaseFactory::getDatabase();
						
						$sql = "SELECT `" . $fieldName . "` 
									FROM `" . $this->collectionDefinitions[$tableName]["join_table"] . "`
									WHERE 1=1
									AND " . $this->collectionDefinitions[$tableName]["local_join_key"] . " = " . $db->quote($this->getID()) . "
									AND " . $this->collectionDefinitions[$tableName]["collection_join_key"] . " = " . $db->quote($modelObject->getID());
						if($row = $db->doQuery($sql)->getRow()) {
							return $row[$fieldName];
						}
						return false;
					}
					else {
						throw new InvalidGetExtraCollectionFieldException();
					}
				}
				else {
					throw new InvalidGetCollectionException();
				}
			}
			catch (Exception $e) {
				$e->processException();
			}			
		}
		
		public function validateInput($inputArray) {
			$errorMessages = array();
			foreach($this->getRequiredFields() as $requiredField) {
				if(!isset($inputArray[$requiredField])) {
					$errorMessages[$requiredField] = "You did not enter a value for " . $this->getDisplayName($requiredField);
				}
			}
			
			foreach($inputField as $key => $value) {
				if(isset($this->dataStructure[$key])) {
					if(isset($this->validationTypes[$key])) {
						$validationType = $this->validationTypes[$key];
					}
					else {
						$validationType = $this->dataStructure[$key]["data_type"];
					}
					
					if(!\Skeet\ValidationFactory::validate($value,$validationType)) {
						if(isset($this->displayErrors[$key])) {
							$errorMessages[$key] = $this->displayErrors[$key];
						}
						else {
							$errorMessages[$key] = "Invalid input for " . $this->getDisplayName($key) . ".";
						}
					}
				}
			}
			$this->validationErrors = $errorMessages;
			return $errorMessages;
		}
		
		public function addRequiredField($requiredField) {
			if(isset($this->dataStructure[$requiredField])) {
				$this->requiredFields[$requiredField] = $requiredField;
			}
		}
		
		public function getRequiredFields() {
			return $this->requiredFields;
		}
		
		/**
		 * Returns (@link $validationErrors)
		 * @return array
		 */
		
		public function getValidationErrors() {
			return $this->validationErrors;
		}
		
		public function getDisplayName($field) {
			if(!isset($this->displayNames[$field]) || !$this->displayNames[$field]) {
				if(isset($this->dataStructure[$field])) {
					if(substr($field,-3) == "_id") {
						$field = substr_replace($field,"",-3);
					}
					return ucwords(str_replace("_"," " ,$field));
				}
			}
			return $this->displayNames[$field];
		}
		
		/**
		 * Returns a label to be used for displaying in the 
		 * collection getDropdown() functions (or wherever 
		 * else you want), for example the name or a concatenation
		 * of various tables
		 * 
		 * @return string
		 */
		
		public function getDisplayLabel() {
			return '';
		}
		
		public function getImages() {
			if(!is_object($this->images)) {
				$this->checkImages();
			}
			return $this->images;
		}
		
		protected function checkImages() {
			$db = \Skeet\DatabaseFactory::getDatabase();
			$sql = "SELECT * FROM image
					  WHERE target_table = " . $db->quote($this->getTableName()) . "
					  AND target_table_key_id = " . $db->quote($this->getID()) . " 
					  AND is_retired = 0";
			$this->images = \Skeet\ModelCollectionFactory::getModelCollection("image",array("custom_sql" => $sql));
		}


		public function getNotes() {
			if(!is_object($this->notes)) {
				$this->checkNotes();
			}
			return $this->notes;
		}

		protected function checkNotes() {
			$sql = "SELECT * FROM note
						WHERE target_table_name = '" . $this->getTableName() . "'
						AND target_table_key_id = '" . $this->getID() . "'
						AND is_retired = 0
						ORDER BY creation_datetime DESC";
			$this->notes = \Skeet\ModelCollectionFactory::getModelCollection("note",array("custom_sql" => $sql));
		}

		public function addNote($noteText) {
			$note = \Skeet\ModelFactory::getModel("note");
			$note->set("user_id",\Skeet\UserFactory::getCurrentUser()->getID());
			$note->set("target_table_name",$this->getTableName());
			$note->set("target_table_key_id",$this->getID());
			$note->set("note_text",$noteText);
			$note->save();
		}

		public function getEvents() {
			if(!is_object($this->events)) {
				$this->checkEvents();
			}
			return $this->events;
		}

		protected function checkEvents() {
			$sql = "SELECT event.*
					  FROM event
					  INNER JOIN event_target_type ON event.event_target_type_id = event_target_type.event_target_type_id
					  WHERE 1=1
					  AND event_target_type.event_target_type_table_name = '" . $this->getTableName() . "'
					  AND event.event_target_type_key_id = '" . $this->getID() . "'
					  AND event.is_retired = 0
					  ORDER BY event.creation_datetime DESC";
			$this->events = \Skeet\ModelCollectionFactory::getModelCollection("event",array("custom_sql" => $sql));
		}


		public function getDocuments() {
			if(!is_object($this->documents)) {
				$this->checkDocuments();
			}
			return $this->documents;
		}

		protected function checkDocuments() {
			$sql = "SELECT * FROM document
						WHERE target_table_name = '" . $this->getTableName() . "'
						AND target_table_key_id = '" . $this->getID() . "'
						AND is_retired = 0
						ORDER BY creation_datetime DESC";
			$this->documents = \Skeet\ModelCollectionFactory::getModelCollection("document",array("custom_sql" => $sql));
		}

		public function addDocument($uploadArray,$description) {
			$fileLocation = $uploadArray["tmp_name"];
			if(file_exists($fileLocation)) {
				$fileName = sha1_file($fileLocation);
				$document = \Skeet\ModelFactory::getModel("document");
				$document->set("user_id",\Skeet\UserFactory::getCurrentUser()->getID());
				$document->set("target_table_name",$this->getTableName());
				$document->set("target_table_key_id",$this->getID());
				$document->set("file_name",$fileName);
				$document->set("original_file_name",$uploadArray["name"]);
				$document->set("description",$description);
				$document->save();
				rename($fileLocation,DOCUMENT_DIR . $fileName);
			}
		}

		public function getDisplayNameField() {
			return $this->displayNameField;
		}

		public function getValuesAsArray() {
			$valuesArray = array();
			foreach($this->dataStructure as $fieldName => $structure) {
				$valuesArray[$fieldName] = $this->get($fieldName);
			}
			return $valuesArray;
		}

		public function compareValueArray($oldValues) {
			$newValues = $this->getValuesAsArray();
			$compareArray = array();
			foreach($this->dataStructure as $fieldName => $structure) {
				if($oldValues[$fieldName] != $newValues[$fieldName]) {
					$compareArray[] = prettifyFieldName($fieldName) . ":" . $oldValues[$fieldName] . " -> " . $newValues[$fieldName];
				}
			}
			return $compareArray;
		}


		public function getHash() {
			$hashString = '';
			foreach($this->dataStructure as $fieldName => $structure) {
				$hashString .= $this->get($fieldName);
			}
			return sha1($hashString);
		}
	}
?>