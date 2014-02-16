<?
	namespace Skeet;
	use Skeet\Page;
	class PageFactory {
		public static $currentPage;
		
		public static function getCurrentPage() {
			return self::$currentPage;
		}
		
		public static function getPage($pageName) {
			$pageObject = null;
			switch($pageName) {

			}
			
			
			
			self::$currentPage = $pageObject;
			return $pageObject;

		
		}
	}
?>
