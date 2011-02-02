<?
	namespace Skeet;
	class PageFactory extends \Skeet\Generated\Factory\PageFactoryGenerated {
		public static $currentPage;
		
		public static function getCurrentPage() {
			return self::$currentPage;
		}
		
		public static function getPage($pageName) {
			switch($pageName) {

			}
			
			if(!$pageObject) {
				$pageObject = parent::getPage($pageName);
			}
			
			if(!is_object($pageObject)) {
				$pageObject = new \Skeet\Page\GenericPage($pageName);
				self::$currentPage = $pageObject;
				return $pageObject;
				//throw new \Skeet\Exception\PageNotFoundException();
			}
			else {
				self::$currentPage = $pageObject;
				return $pageObject;
			}
		
		}
	}
?>
