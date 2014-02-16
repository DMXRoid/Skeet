<?php
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet;

	/**
	 * ThemeFactory
	 */
	class ThemeFactory {
		protected static $currentTheme;
		protected static $currentThemeName;
		public static function getCurrentTheme() {
			if(!is_object(self::$currentTheme)) {
				self::$currentTheme = self::getTheme(self::$currentThemeName);
			}
			return self::$currentTheme;
		}

		public static function setCurrentThemeName($currentThemeName) {
			self::$currentThemeName = $currentThemeName;
			self::$currentTheme = null;
		}

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