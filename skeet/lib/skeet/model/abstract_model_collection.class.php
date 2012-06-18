<?
	namespace Skeet\Model;
	
	abstract class AbstractModelCollection {
		/**
		 * Counter to step through populated list
		 *
		 * @see hasNext(),getNext()
		 * @var integer
		 * 
		 */
		protected $counter = 0;
		
		/**
		 * Collection container
		 * @see getNext()
		 * @var array
		 */
		
		protected $collection = array();
		
		/**
		 * A cache of the primary key id's of the objects in the collection
		 * @see has()
		 * @var array
		 */
		
		protected $cachedPrimaryKeyIDList = array();
		
		
		/**
		 * The SQL that initialized the collection
		 * @var string
		 */
				
		protected $initSQL;
		
		
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
		 *	The name field of a table, used to order a collection if it
		 * exists
		 * @var string
		 */

		protected $nameField;

		public function __construct($options = array(
									"where"=>array(),
									"join_table"=>array(),
									"order_by"=>array(),
									"create_emtpy"=>NULL,
									"group_by"=>array(),
									"custom_sql" => NULL,
									"show_retired" => false
									)) {
			$this->preConstruct();
			if(!isset($options["create_empty"]) || is_null($options["create_empty"])) {
				$db = \Skeet\DatabaseFactory::getDatabase();
				$collection = array();
				if(!isset($options["show_retired"]) || !$options["show_retired"]) {
					$where = " WHERE " . $this->getTableName() . ".is_retired = 0 ";
				}
				else {
					$where = " WHERE 1=1 ";
				}
				
				if(isset($options["where"]) && count($options["where"]) > 0) {
					foreach($options["where"] as $field => $value) {
						if(!is_array($value)) {
							if(is_null($value)) {
								$where .= " AND " . $field . " IS NULL ";
							}
							else {
								$where .= " AND " . $field . " = '" . $value . "'";
							}
						}
						else {
							$where .= " AND " . $field . " IN (";
							foreach($value as $searchVal) {
								$where .= $searchVal . ",";
							}
							$where = substr_replace($where,'',-1) . ")";
						}
					}
				}
				$order = "";
				if(isset($options["order_by"]) && count($options["order_by"]) > 0) {
					$order = " ORDER BY ";
					$order .= implode(",",$options["order_by"]) . " DESC";
					
				}
				elseif($this->nameField) {
					$order = " ORDER BY " . $this->nameField . " ASC ";
				}
				
				$group = "";
				if(isset($options["group_by"]) && count($options["group_by"]) > 0) {
					$group = " GROUP BY ";
					foreach($options["group_by"] as $grouping) {
						$group .= $grouping . ",";
					}
					$group = substr_replace($group,'',-1);
				}
				
				$joinTableSQL = "";
				
				if(isset($options["join_table"]) && count($options["join_table"])) {
					foreach($options["join_table"] as $joinTable) {
						$joinTableSQL .= $joinTable . "\n";
					}
				}
				
				$sql = "SELECT DISTINCT `" . $this->getTableName() . "`.* FROM " . $this->getTableName() . "
							
							" . $joinTableSQL . "
							" . $where . $group . $order;
				if(isset($options["custom_sql"]) && !is_null($options["custom_sql"])) {
					$sql = $options["custom_sql"];
				}
				$result = $db->doQuery($sql);
				while($row = $result->getRow()) {
					$model =  \Skeet\ModelFactory::getModel($this->getTableName(),NULL,NULL,$row);
					$this->cachedPrimaryKeyIDList[$model->getID()] = $model->getID();
					$this->collection[] = $model;
				}
				$this->initSQL = $sql;
			}										
			$this->postConstruct();
		}

		protected function preConstruct() {	}
		protected function postConstruct() { }
		
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
		 * See if there's another element in the collection
		 * @see getCounter()
		 * @link $counter
		 */
		
		public function hasNext() {
			if(isset($this->collection[$this->getCounter()])) {
				 return TRUE;
			}
			return FALSE;
		}
		
		/**
		 * Get the counter
		 * @see hasNext()
		 * @return integer
		 */
		
		public function getCounter() {
			return $this->counter;
		}
		
		/**
		 * Get the number of objects in the collection
		 * @return integer
		 */
		
		public function getCount() {
			return count($this->collection);
		}
		
		/**
		 * Returns the collection as an array
		 * @return array
		 */
		
		public function getCollection() {
			return $this->collection;
		}
		
		/**
		 * Gets the next object in the collection
		 * @return object|boolean
		 */
		
		public function getNext() {
			if($this->hasNext()) {
				$modelObject = $this->collection[$this->getCounter()];
				$this->counter++;
			}
			else {
				$modelObject = FALSE;
			}
		
			return $modelObject;
		}
		
		public function reInit() {
			$this->counter = 0;
		}
		
		/**
		 * Get the last object in the collection
		 * @return object|boolean
		 */
		
		public function getLast() {
			if(isset($this->collection[(count($this->getCount()) - 1)])) {
				return $this->collection[(count($this->getCount()) - 1)];
			}
		}
		
		/**
		 * See if there's an object in the collection
		 * @var integer
		 * @return boolean
		 */
		
		public function has($model) {
			if(isset($this->cachedPrimaryKeyIDList[$model->getID()])) {
				return true;
			}
			return false;
		}
		
		public function hasID($id) {
			if(isset($this->cachedPrimaryKeyIDList[$id])) {
				return true;
			}
			return false;
		}
		
		/**
		 * Add an object to the collection
		 * @var object
		 */
		
		public function add($object) {
			$this->collection[] = $object;
		}


		public function remove($object) {
			$didRemove = false;
			foreach($this->collection as $key => $tempObject) {
				if(!$didRemove) {
					if($tempObject->getID() == $object->getID()) {
						unset($this->collection[$key]);
						$didRemove = true;
					}
				}
				else {
					$newKey = $key - 1;
					$this->collection[$newKey] = $tempObject;
				}
			}
			$this->reInit();
		}


		public function getDropdown($name,$selected=NULL,$extras=NULL,$skipLabel=false) {
			$this->reInit();
			$output = '<select name="' . $name . '" ' . $extras . '>' . "\n";
			if(!$skipLabel) {
				$output .= '<option value="">--- Select One ---</option>' . "\n";
			}
			while($specificObject = $this->getNext()) {
				if($specificObject->getID() == $selected || (is_array($selected) && in_array($specificObject->getID(),$selected))) {
					$isSelected = " selected ";
				}
				else {
					$isSelected = "";
				}
				
				$output .= '<option value="' . $specificObject->getID() . '" ' . $isSelected . '>' . $specificObject->getDisplayLabel() . '</option>' . "\n";
			}
			$output .= '</select>' . "\n";
			return $output;
		}

		public function toArray() {
			$returnArray = array();
			$this->reInit();
			while($tempObject = $this->getNext()) {
				$returnArray[$tempObject->getID()] = $tempObject->getDisplayLabel();
			}
			return $returnArray;
		}
		
	}
?>