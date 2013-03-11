<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	
	class ModelCollectionFactory extends \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Factory\ModelCollectionFactoryGenerated {
		public static function getModelCollection($tableName,$definitionArray=array()) {
			try {
				switch($tableName) {
					default: 
						$modelObjectCollection = false;
				}
				
				if(!$modelObjectCollection ||  !is_object($modelObjectCollection)) {
					$modelObjectCollection = parent::getModelCollection($tableName,$definitionArray);	
				}
				
				if(!is_object($modelObjectCollection)) {
					die($tableName);
					throw new ModelCollectionNotFoundException();
				}
				else {
					return $modelObjectCollection;
				}
				
			}
			catch(Exception $e) {
				$e->processException();
			}
		}
	}
?>