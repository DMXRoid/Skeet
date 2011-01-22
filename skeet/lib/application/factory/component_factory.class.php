<?
	namespace Skeet;
	class ComponentFactory {
		
		public static function getComponent($componentName,$page=NULL) {
			if(!$page) {
				$page = PageFactory::getCurrentPage();	
			}
			switch($componentName) {
				
				case "exception":
					$componentObject = new \Skeet\Component\ExceptionComponent();
					break;
					
				case "backtrace":
					$componentObject = new \Skeet\Component\BacktraceComponent();
					break;
				
				case "mainbody":
					switch($page->getPageName()) {
						
						case "ApplicationBuild":
						case "ApplicationName":
						case "ApplicationFunctionality":
						case "ApplicationLogo":
						case "ApplicationSplashScreen":
						case "ApplicationTemplate":
						case "ApplicationWidgets":
						case "ApplicationLogin":
						case "ApplicationSignup":
						case "ApplicationPreview":
						case "ApplicationSave":
						case "ApplicationSaved":
						case "ApplicationComplete":
						case "ApplicationDesignTemplate":
						case "ApplicationDesignCustom":
							$componentObject = new \Skeet\Component\MainbodyApplication();
							break;
						
						
						default:
							if($page instanceof \Skeet\Page\AbstractCrudManagePage) {
								$componentLabel = "MainbodyCrudManage";
							}
							elseif($page instanceof \Skeet\Page\AbstractCrudCollectionPage) {
								$componentLabel = "MainbodyCrudList";
							}
							else {
								$componentLabel = "Mainbody" . ucwords($page->getPageName());	
							}
							
							$componentObject = new \Skeet\Component\GenericComponent($componentLabel);
					}
					break;

				default:
					$componentLabel = str_replace(" ",'_',ucfirst($componentName));
					$componentObject = new \Skeet\Component\GenericComponent($componentLabel);
			}
			$componentObject->setPageObject($page);
			return $componentObject;
		}
	}
?>
