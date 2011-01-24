<?
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Factory;

	/**
	 * Component factory 
	 */

	class ComponentFactory {
		
		public static function getComponent($componentName) {
			switch($componentName) {
				default:
					$componentLabel = str_replace(" ",'_',ucfirst($componentName));
					$componentObject = new \Skeet\Component\GenericComponent($componentLabel);
			}
			return $componentObject;
		}
	}
?>
