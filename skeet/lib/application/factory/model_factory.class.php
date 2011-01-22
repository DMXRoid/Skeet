<?
	namespace Skeet;
	class ModelFactory extends \Skeet\Generated\Factory\ModelFactoryGenerated {
		public static function getModel($tableName,$primaryKeyID=NULL,$customSQL=NULL,$dataRow=NULL) {
			try {
				switch($tableName) {
					default: 
						$modelObject = false;
				}
				
				if(!$modelObject || !is_object($modelObject)) {
					$modelObject = parent::getModel($tableName,$primaryKeyID,$customSQL,$dataRow);	
				}
				
				if(!is_object($modelObject)) {
					throw new ModelNotFoundException();
				}
				else {
					return $modelObject;
				}
				
			}
			catch(Exception $e) {
				$e->processException();
			}
		}
	}
?>