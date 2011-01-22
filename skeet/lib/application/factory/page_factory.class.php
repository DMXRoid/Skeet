<?
	namespace Skeet;
	class PageFactory extends \Skeet\Generated\Factory\PageFactoryGenerated {
		public static $currentPage;
		
		public static function getCurrentPage() {
			return self::$currentPage;
		}
		
		public static function getPage($pageName) {
			switch($pageName) {

				case "Blog":
					$pageObject = new \Skeet\Page\BlogPage();
					break;
				case "Login":
					$pageObject = new \Skeet\Page\LoginPage();
					break;
					
				case "application build":
					$pageObject = new \Skeet\Page\ApplicationBuildPage();
					break;
					
				case "application name":
					$pageObject = new \Skeet\Page\ApplicationNamePage();
					break;
					
				case "application functionality":
					$pageObject = new \Skeet\Page\ApplicationFunctionalityPage();
					break;
					
				case "application logo":
					$pageObject = new \Skeet\Page\ApplicationLogoPage();
					break;
					
				case "application splash screen":
					$pageObject = new \Skeet\Page\ApplicationSplashScreenPage();
					break;

				case "application signup":
					$pageObject = new \Skeet\Page\ApplicationSignupPage();
					break;

				case "application preview":
					$pageObject = new \Skeet\Page\ApplicationPreviewPage();
					break;

				case "application design template":
					$pageObject = new \Skeet\Page\ApplicationDesignTemplatePage();
					break;

				case "application save":
					$pageObject = new \Skeet\Page\ApplicationSavePage();
					break;

				case "application saved":
					$pageObject = new \Skeet\Page\ApplicationSavedPage();
					break;

				case "application complete":
					$pageObject = new \Skeet\Page\ApplicationCompletePage();
					break;

				case "application template":
					$pageObject = new \Skeet\Page\ApplicationTemplatePage();
					break;

				case "application widgets":
					$pageObject = new \Skeet\Page\ApplicationWidgetsPage();
					break;

				case "application login":
					$pageObject = new \Skeet\Page\ApplicationLoginPage();
					break;

				case "AjaxAction":
					$pageObject = new \Skeet\Page\AjaxActionPage();
					break;
					
				default:
					$pageObject = false;
					break;
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
