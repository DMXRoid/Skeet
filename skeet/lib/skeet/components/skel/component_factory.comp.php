<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	
	class ComponentFactory extends \Skeet\ComponentFactory {
		public static function getComponent($componentName) {
			$componentObject = null;
			switch($componentName) {
			
			}
			if(is_null($componentObject)) {
				$componentObject = parent::getComponent($componentName);
			}
			return $componentObject;
		}
	}
?>