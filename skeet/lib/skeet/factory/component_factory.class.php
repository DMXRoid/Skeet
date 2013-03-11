<?
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet;
	/**
	 * Component factory 
	 */

	class ComponentFactory {
		
		public static function getComponent($componentName) {
			switch($componentName) {
				
					case "mainbody":
					switch($page->getPageName()) {
						
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
			return $componentObject;
		}
	}
?>
