<?php
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/

	namespace Skeet;

	/**
	 * A factory for Theme objects
	 * @package Skeet
	 * @subpackage Factory
	 */

	class ThemeFactory {
		/**
		 * The current theme
		 *	@var \Skeet\Theme\AbstractTheme 
		 * @access protected
		 */

		protected static $currentTheme;

		/**
		 * The current theme name
		 *	@var string
		 * @access protected
		 */
		protected static $currentThemeName;

		/**
		 * Get the current theme
		 * Will get the cached version if it exists, otherwise it'll
		 * go get a new object
		 * @access public
		 * @return \Skeet\Theme\AbstractTheme
		 */
		public static function getCurrentTheme() {
			if(!is_object(self::$currentTheme)) {
				self::$currentTheme = self::getTheme(self::$currentThemeName);
			}
			return self::$currentTheme;
		}

		/**
		 * Set the current theme name
		 * @access public
		 * @param string $currentThemeName The current theme name
		 */

		public static function setCurrentThemeName($currentThemeName) {
			self::$currentThemeName = $currentThemeName;
			self::$currentTheme = null;
		}

		/**
		 * Get a theme
		 * @access public
		 * @param string $themeName
		 * @return \Skeet\Theme\AbstractTheme
		 */

		public static function getTheme($themeName) {
			switch($themeName) {
				case "generator":
					$theme = new \Skeet\Theme\GeneratorTheme();
					break;
				
				case "skel":
					$theme = new \Skeet\Theme\SkelTheme();
					break;
				
				default:
					$theme = new \Skeet\Theme\GenericTheme();
					break;
			}
			return $theme;
		}
	}
?>