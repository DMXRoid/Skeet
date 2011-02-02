<?php
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Factory;

	/**
	 * ThemeFactory
	 */
	class ThemeFactory {
		private static $currentTheme;
		private static $currentThemeName;
		public static function getCurrentTheme() {
			if(!is_object(self::$currentTheme)) {
				self::$currentTheme = self::getTheme(self::$currentThemeName);
			}
			return self::$currentTheme;
		}

		public static function setCurrentThemeName($currentThemeName) {
			self::$currentThemeName = $currentThemeName;
		}

		public static function getTheme($themeName) {
			switch($themeName) {
				case "generator":
					$theme = new \Skeet\Theme\GeneratorTheme();
					break;
			}
			return $theme;
		}
	}
?>
