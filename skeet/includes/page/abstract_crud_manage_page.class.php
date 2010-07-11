<?
	namespace Skeet\Page;
	class AbstractCrudManagePage extends \Skeet\Page\AbstractPage {
		protected $tableName;
		protected $primaryKey;
		protected $crudObject;
		protected $errorMessages;
		protected $specificObject;
		
		
		public function __construct() {
			parent::__construct();
			$this->specificObject = \Skeet\ModelFactory::getModel($this->getTableName(),getRequestValue($this->primaryKey));
			
			if(!$this->specificObject->getID()) {
				die();
			}
			else {
				if(getRequestValue("do_update")) {
					$this->processInput();
				}
				elseif(getRequestValue("collection_object_add")) {
					$targetObject = \Skeet\ModelFactory::getModel(getRequestValue("collection_object_table"),getRequestValue('target_join_key'));
					$this->getSpecificObject()->addObjectToCollection(getRequestValue("collection_object_table"),$targetObject);
					\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey,$this->getSpecificObject()->getID())->doRedirect();
					die();					
				}
				elseif(getRequestValue("add_image")) {
					if(isset($_FILES["new_image"]) && file_exists($_FILES["new_image"]["tmp_name"])) {
						$image = \Skeet\ModelFactory::getModel("image");
						$image->set("target_table",$this->getTableName());
						$image->set("target_table_key_id",$this->getSpecificObject()->getID());
						$image->save();
						$image->setImageFromFile($_FILES["new_image"]["tmp_name"]);
						
					}
				}
				elseif(getRequestValue("collection_object_set_extra_field")) {
					
					foreach($_REQUEST["extra_field"] as $extraField => $value) {
						$this->specificObject->setExtraCollectionField(getRequestValue("collection_object_table"),$extraField,\Skeet\ModelFactory::getModel(getRequestValue("collection_object_table"),getRequestValue("collection_object_join_key_value")),$value);
					}
					\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey,$this->getSpecificObject()->getID())->doRedirect();
					die();
				}
			}
		}
		
		public function getTableName() {
			return $this->tableName;
		}

		/**
		 *
		 * @return AbstractModel
		 */

		public function getSpecificObject() {
			return $this->specificObject;
		}
		
		public function getErrorMessages() {
			return $this->errorMessages;
		}
		
		public function checkInput() {
			$this->errorMessages = $this->getSpecificObject()->validateInput(getRequestValue("update_values"));
		}
		
		public function processInput() {
			$this->checkInput();
			if(count($this->getErrorMessages()) == 0) {
				foreach(getRequestValue("update_values") as $key => $value) {
					$this->getSpecificObject()->set($key,$value);
				}
				$this->getSpecificObject()->save();
				\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey,$this->getSpecificObject()->getID())->doRedirect();
				die();
			}
		}
	}
?>