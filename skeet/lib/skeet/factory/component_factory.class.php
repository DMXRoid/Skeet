<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/

	namespace Skeet;
	/**
	 * A factory for display component classes
	 * @package Skeet
	 * @subpackage Factory
	 */

	class ComponentFactory {

		/**
		 * Get a component
		 * @static
		 * @access public
		 * @param string $componentName The name of the componnent
		 * @param \Skeet\Page\AbstractPage|null The calling page object
		 * @return \Skeet\Component\AbstractComponent
		 */
		
		public static function getComponent($componentName,$page=null) {
			if(is_null($page)) {
				$page = \Skeet\PageFactory::getCurrentPage();
			}
			switch($componentName) {
				
					case "mainbody":
					switch($page->getPageName()) {
						
						default:
							if($page instanceof \Canvasser\Page\AbstractCrudManagePage) {
								$componentLabel = "MainbodyCrudManage";
							}
							elseif($page instanceof \Canvasser\Page\AbstractCrudCollectionPage) {
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
