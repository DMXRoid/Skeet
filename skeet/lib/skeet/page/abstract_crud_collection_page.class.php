<?
	namespace Skeet\Page;
	abstract class AbstractCrudCollectionPage extends \Skeet\Page\AbstractPage {
		protected $tableName;
		protected $collection;
		protected $primaryKey;
		
		public function getCollection() {
			return $this->collection;
		}
		
		public function getTableName() {
			return $this->tableName;
		}
		
		public function __construct() {
			parent::__construct();
			$this->collection = \Skeet\ModelCollectionFactory::getModelCollection($this->tableName);
			if(getRequestValue("do_create")) {
				$specificObject = \Skeet\ModelFactory::getModel($this->tableName);
				$specificObject->save();
				\Skeet\LinkFactory::getLink(capString($this->tableName) . 'Manage')->addLinkArg($this->primaryKey,$specificObject->getID())->doRedirect();
				die();
			}
			
			if(getRequestValue("do_delete")) {
				$specificObject = \Skeet\ModelFactory::getModel($this->tableName,getRequestValue($this->primaryKey));
				$specificObject->set("is_retired",1);
				$specificObject->save();
				\Skeet\LinkFactory::getLink(capString($this->tableName) . "List")->doRedirect();
				die();
			}
			
		}
	}

?>