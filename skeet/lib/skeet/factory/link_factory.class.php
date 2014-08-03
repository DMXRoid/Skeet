<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/

	namespace Skeet;

	/**
	 * The place to get link objects
	 * @package Skeet
	 * @subpackage Factory
	 */
	class LinkFactory {

		/**
		 * Get a link
		 * @access public
		 * @static
		 * @param string $linkName Name of the link
		 * @return \Skeet\Link\AbstractLink
		 */

		public static function getLink($linkName) {
			switch($linkName) {
				default:
					$linkObject = new \Skeet\Link\GenericLink($linkName);
					break;
			}

			return $linkObject;
		}
	}
?>
