<?
/**
* @package Skeet
* @version 1.0
* @author Matthew Schiros <schiros@invisihosting.com>
* @copyright Copyright (c) 2011, Matthew Schiros
*/

	namespace Skeet;

	/*
	* ThemeFactory is the controller for getting back arbitrary themes,
	 * as well as the current theme.
	*/

	class ThemeFactory {
		/**
		 *  Default theme name.  Change to whatever if you want.
		 *  @var mixed
		 *  @static
		 */

		const DEFAULT_THEME_NAME = "default";

		/**
		 *	The current theme object.
		 *
		 * @static
		 * @access private
		 * @var AbstractTheme
		 * @see getCurrentTheme()
		 * @see getTheme()
		 */

		private static $currentTheme;

		/**
		 * The current theme name.
		 *
		 * @static
		 * @access private
		 * @var mixed
		 * @see getCurrentTheme()
		 * @see getTheme()
		 * @see setCurrentThemeName()
		 */
		
		private static $currentThemeName;

		/**
		 * Get the current theme.  If one isn't set, set {@link $currentTheme} using
		 * {@link $currentThemeName} passed to {@link getCurrentTheme()}.
		 *
		 * @static
		 * @access public
		 * @return AbstractTheme
		 * @see $currentTheme
		 * @see $currentThemeName
		 * @see getTheme()
		 */
		
		public static function getCurrentTheme() {
			if(!is_object(self::$currentTheme)) {
				self::$currentTheme = self::getTheme(self::$currentThemeName);
			}
			return self::$currentTheme;
		}
		/**
		 * Set the current theme name.
		 *
		 * @static
		 * @access public
		 * @param mixed $currentThemeName
		 * @see $currentThemeName
		 */

		public static function setCurrentThemeName($currentThemeName) {
			self::$currentThemeName = $currentThemeName;
		}

		/**
		 *	Get a theme based on the name you pass to it.
		 *
		 * @static
		 * @access public
		 * @param mixed $themeName
		 * @return AbstractTheme
		 */

		public static function getTheme($themeName=null) {
			if(!$themeName) {
				$themeName = ThemeFactory::DEFAULT_THEME_NAME;
			}
			switch($themeName) {
				case ThemeFactory::DEFAULT_THEME_NAME:
				default:
					$theme = new \Skeet\Theme\DefaultTheme();
					break;
			}
			return $theme;
		}
	}
?>
