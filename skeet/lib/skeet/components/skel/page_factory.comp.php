<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	
	class PageFactory extends \Skeet\PageFactory {
	
		public static function getPage($pageName) {
			$pageObject = null;
			switch($pageName) {
			
			}
			if(is_null($pageObject)) {
				$pageObject = parent::getPage($pageName);
			}
			if(!is_null($pageObject)) {
				self::$currentPage = $pageObject;
				return $currentPage;
			}
		}
	}

?>