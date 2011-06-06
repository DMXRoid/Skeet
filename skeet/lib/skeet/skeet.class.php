<?
/**
 * @package Skeet
 * @version 1.0
 * @author Matthew Schiros <schiros@invisihosting.com>
 * @copyright Copyright (c) 2011, Matthew Schiros
 */

	namespace Skeet;

	/*
	 * Global class for the Skeet Framework.  Contains configuration data.
	 * Can be extended if you want to run your own set of init things.
	 */

	class Skeet {

		const VERSION = "1.0";

		public static $config = array();
		
		public static function init($configName="default",$configFile = "skeet.ini") {
			$configPath = __DIR__ . "/../../etc/" . $configFile;
			$config = parse_ini_file($configPath,true,INI_SCANNER_RAW);
			self::$config = $config[$configName];
			self::setConfig("lib_path",self::getConfig("application_path") . 'lib/');
			self::setConfig("application_lib_path",self::getConfig("lib_path") . strtolower(self::getConfig("application_name")) . "/");
			self::setConfig("application_model_path",self::getConfig("application_lib_path") . "model/");
			self::setConfig("skeet_lib_path",self::getConfig("lib_path") . "skeet/");
			spl_autoload_register("\Skeet\Skeet::autoload");
		}

		public static function setConfig($key,$value) {
			self::$config[$key] = $value;
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
			$includePath = self::getConfig("application_path") . "lib/";
			foreach($nameSpaceArray as $namespace) {
				$includePath .= strtolower($namespace) . '/';
			}
			$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$className),'',0,1) . '.class.php';
			if(file_exists($includePath . $fileName)) {
				require_once($includePath . $fileName);
			}
		}

		public static function Skeet() {
			die("YEAHHHHHH");
		}
	}

?>