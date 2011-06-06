<?
/**
 * @package Skeet
 * @subpackage Model
 * @version 1.0
 * @author Matthew Schiros <schiros@invisihosting.com>
 * @copyright Copyright (c) 2011, Matthew Schiros
 */
	namespace Skeet\Model;

	abstract class AbstractModel {
		
		/**
		 * Database name for the table
		 * @access protected
		 * @var string
		 */
		protected $databaseName = MODEL_DATABASE_NAME;
		
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
		 * A list of fields and datatypes in (@see $tableName)
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
		 * not exist, falls back to the datatype in (@see $dataStructure)
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
		

		
		/**
		 * Generic Constructor
		 * @access public
		 * @var integer|NULL
		 * @var string|NULL
		 * @var array|NULL
		 */
		public function __construct($primaryKeyID=NULL,$customSQL=NULL,$dataRow=NULL) {
			$this->preConstruct();
			$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
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

		/**
		 * These two functions just exist to make it less necessary to
		 * write object-specific constructors by letting you implement actions
		 * both before and after any database calls or object setup.
		 *
		 * If you find yourself in the situation where you'd like to run the code inside
		 * pre/postConstruct outside of the constructor, put that code in another, public
		 * function within the subclass and call that function from pre/postConstruct.
		 * 
		 */


		/**
		 * To run something at the very beginning of the AbstractModel constructor,
		 * implement preConstruct in your sub-class.
		 * @access protected
		 */

		protected function preConstruct() { }

		/**
		 * To run something at the very end of the AbstractModel constructor, implement
		 * postConstruct in your sub class.
		 * @access protected
		 */

		protected function postConstruct() { }

		/**
		 * Get the field name of the primary key.  Usually, it'll
		 * be (@see $tableName)_id, but whatever.
		 * @return string
		 * @access public
		 */

		public function getPrimaryKeyField() {
			return $this->primaryKey["primary_key_field_name"];
		}

		/**
		 *	Set the primary key on the object.  This should generally
		 * only be used internally by (@see save())
		 * @param integer $primaryKeyID
		 */

		protected function setPrimaryKeyID($primaryKeyID) {
			$this->primaryKey["primary_key_value"] = $primaryKeyID;
		}
			
		/**
		 * Gets (@see $tableName)
		 * @access public
		 * @return string
		 */
		
		public function getTableName() {
			return $this->tableName;
		}
		
		/**
		 * Gets (@see $databaseName)
		 * @access public
		 * @return string
		 */
		
		public function getDatabaseName() {
			return $this->databaseName;
		}
		
		/**
		 * Gets the (@see $primaryKeyID)
		 * @access public
		 * @return integer
		 */
		
		public function getID() {
			return $this->primaryKey["primary_key_value"];
		}
		
		/**
		 * Gettor for data values.  Checks (@see $dataStructure) for the valid existence of a field,
		 * throws an (@see InvalidGetCallException) if it doesn't exist
		 * @access public
		 * @return mixed
		 */
		
		public function get($key) {
			try {
				if(isset($this->dataStructure[$key]) && is_array($this->dataStructure[$key])) {
					return $this->dataStructure[$key]["value"];
				}
				else {
					throw new InvalidGetCallException($key);
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Settor for data values.  Checks (@see $dataStructure) for the valid existence of a field,
		 * throws an (@see InvalidSetCallException) if it doesn't exist
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
					throw new InvalidSetCallException($key);
				}
			}
			catch (Exception $e) {
				$e->processException();
			}
		}
		
		/**
		 * Save function.  Writes values in (@see $updatedFields) to the database.
		 * If there's already a PKID set, calls (@see AbstractDatabase::doInsertOrUpdate()),
		 * otherwise, calls (@see AbstractDatabase::doInsert()) and sets the primary key
		 * (@see setPrimaryKeyID())
		 * @access public
		 */
	
		
		public function save() {
			$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
			$tableName = $this->getTableName();
			$fields = array();
			$quotes = array();
			
			foreach($this->updatedFields as $key => $value) {
				if(!$value) {
					if(is_null($this->dataStructure[$key]["default_value"])) {
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
		 * Gets a collection model object.  Calls the checkCollection function if 
		 * one isn't stored yet
		 * 
		 * @see checkCollection()
		 * @see $collections $collectionDefinitions
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
		 * @see $collections $collectionDefinitions
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
				case COLLECTION_TYPE_MANY_TO_MANY:
					$joinSQL = " INNER JOIN `" . $this->collectionDefinitions[$tableName]["join_table"] . "` 
									 ON `" .  $this->collectionDefinitions[$tableName]["table_name"] . "`." . $this->collectionDefinitions[$tableName]["target_join_key"] . " = `" . $this->collectionDefinitions[$tableName]["join_table"] . "`." . $this->collectionDefinitions[$tableName]["collection_join_key"] . "
									 AND `" . $this->collectionDefinitions[$tableName]["join_table"] . "`.is_retired = 0 
									 AND `" . $this->collectionDefinitions[$tableName]["join_table"] . "`." . $this->collectionDefinitions[$tableName]["local_join_key"] . " = '" . $this->getID() . "'";
					$collectionOptionArray["join_table"] = array($joinSQL);
					$where = array();
					$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`.is_retired"] = "0";
					$collectionOptionArray["where"] = $where;
					if(isset($this->collectionDefinitions[$tableName]["order_by"])) {
						$collectionOptionArray["order_by"] = $this->collectionDefinitions[$tableName]["order_by"];
					}
					break;
					
				case COLLECTION_TYPE_ONE_TO_MANY:
				default:
					$where = array();
					$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`." . $this->collectionDefinitions[$tableName]["primary_key"]] = $this->getID();
					$where["`" . $this->collectionDefinitions[$tableName]["table_name"] . "`.is_retired"] = "0";
					$collectionOptionArray["where"] = $where;
					break;
			}
			$this->collectionDefinitions[$tableName]["collection_options"] = $collectionOptionArray;
		}
		
		/**
		 * Gets a one to one object.  Calls checkTargetObject if one isn't 
		 * stored yet
		 * 
		 * @see checkTargetObject()
		 * @see $targetObjects $targetObjectDefinitions
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
					
					$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
					
					$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
					$fields = array();
					$quotes = array();
					$wheres = array();
					
					$fields[$this->collectionDefinitions[$tableName]["local_join_key"]] = $this->getID();
					$fields[$this->collectionDefinitions[$tableName]["collection_join_key"]] = $modelObject->getID();
					
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
					$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
					
					$targetTable = $this->collectionDefinitions[$tableName]["join_table"];
					$fields = array();
					$quotes = array();
					$wheres = array();

					$fields["is_retired"] = "0";
					
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
						$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
						
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
		
		public function getExtraCollectionField($tableName,$fieldName,$modelObject) {
			try {
				if(isset($this->collectionDefinitions[$tableName]) && is_array($this->collectionDefinitions[$tableName])) {
					if(isset($this->collectionDefinitions[$tableName]["extra_fields"][$fieldName])) {
						$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
						
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
		 * Returns (@see $validationErrors)
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
			$db = \Skeet\DatabaseFactory::getDatabase($this->getDatabaseName());
			$sql = "SELECT * FROM image
					  WHERE target_table = " . $db->quote($this->getTableName()) . "
					  AND target_table_key_id = " . $db->quote($this->getID()) . " 
					  AND is_retired = 0";
			$this->images = \Skeet\ModelCollectionFactory::getModelCollection("image",array("custom_sql" => $sql));
		}
		
			
	}
?>
