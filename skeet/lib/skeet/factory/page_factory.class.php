<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/
	
	namespace Skeet;
	use Skeet\Page;
	/**
	 * A factory for page objects
	 * @package Skeet
	 * @subpackage Factory
	 */
	
	class PageFactory {

		/**
		 * The current page, cached
		 * @var \Skeet\Page\AbstractPage
		 * @static
		 * @access public
		 */
		public static $currentPage;
		
		/**
		 * Gets the cached current page
		 * @static
		 * @access public
		 * @return \Skeet\Page\AbstractPage
		 */

		public static function getCurrentPage() {
			return self::$currentPage;
		}
		
		/**
		 * Get a page
		 * @static
		 * @access public
		 * @param string $pageName The name of the page to get
		 * @return \Skeet\Page\AbstractPage
		 */
		public static function getPage($pageName) {
			$pageObject = null;
			switch($pageName) {

			}
			
			
			
			self::$currentPage = $pageObject;
			return $pageObject;

		
		}
	}
?>
