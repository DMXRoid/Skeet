<?
/**
 * @package Skeet
 * @subpackage Component
 * @version 1.0
 * @author Matthew Schiros <schiros@invisihosting.com>
 * @copyright Copyright (c) 2011, Matthew Schiros
 */

	namespace Skeet;

	/*
	 * Global class for the Skeet Framework.  Contains configuration data.
	 * Can be extended if you want to run your own set of init things.
	 *
	 */


	class Skeet {
		private static $config = array();
		
		public static function init($configName="default",$configPath="../../../etc/skeet.ini") {
			$config = parse_ini_file($configPath,true);
			self::$config = $config[$configName];
			spl_autoload_register("\Skeet\Skeet::autoload");
		}

		public static function getConfig($key) {
			if(isset(self::$config[$key])) {
				return self::$config[$key];
			}
			return false;
		}
		/**
		 * \Skeet\Generated
		 * \Skeet
		 * \Skeet\User\
		 * @param <type> $classAndNameSpace
		 */
		public static function autoload($classAndNameSpace) {
			$nameSpaceArray = explode('\\',$classAndNameSpace);
			$className = array_pop($nameSpaceArray);
		}
	}

?>