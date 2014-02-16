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
		const COLLECTION_TYPE_ONE_TO_MANY = 1;
		const COLLECTION_TYPE_MANY_TO_MANY = 2;
		const DATA_TYPE_STRING = 1;
		const DATA_TYPE_INTEGER = 2;
		const DATA_TYPE_ARRAY = 3;
		const DATA_TYPE_DATETIME = 4;
		const DATA_TYPE_FLOAT = 5;
		const DATA_TYPE_TINYINT = 6;
		const DATA_TYPE_TEXT = 7;
		
		const WORMHOLE_UNKNOWN = 128;

		public static $config = array();
		
		public static function init($configName="default",$configFile = "skeet.ini") {
			
			/**
			 * Read out the config files.  Merge the config values
			 * for the default and the passed config so that you can just
			 * override the default settings that you need to in each application
			 */
			$configPath = __DIR__ . "/../../etc/" . $configFile;
			$configArray = parse_ini_file($configPath,true,INI_SCANNER_RAW);
			$config = $configArray["default"];
			
			if($configName != "default" && isset($configArray[$configName])) {
				$config = array_merge($config,$configArray[$configName]);
			}
			
			$config["database"] = array();
			
			foreach($config["database_configs"] as $dbConfigName) {
				$config["database"][$dbConfigName] = array();
				foreach($config as $key => $value) {
					if(substr($key,0,strlen("database." . $dbConfigName)) == "database." . $dbConfigName) {
						$config["database"][$dbConfigName][str_replace("database." . $dbConfigName . ".","",$key)] = $value;
						unset($config[$key]);
					}
				}
			}
			self::$config = $config;
			self::setConfig("lib_path",self::getConfig("application_path") . 'lib/');
			self::setConfig("application_lib_path",self::getConfig("lib_path") . strtolower(self::getConfig("application_name")) . "/");
			self::setConfig("application_model_path",self::getConfig("application_lib_path") . "model/");
			self::setConfig("application_page_path",self::getConfig("application_lib_path") . "pages/");
			self::setConfig("application_namespace",(self::getConfig("namespace")) ?: str_replace(" ","",ucwords(self::getConfig("application_name"))));
			self::setConfig("skeet_lib_path",self::getConfig("lib_path") . "skeet/");
			spl_autoload_register("\Skeet\Skeet::autoload");
			
			if(file_exists(self::getConfig("application_lib_path") . "load.php")) {
				require_once(self::getConfig("application_lib_path"). "load.php");
			}
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
		
		public static function getDatabaseConfig($database) {
			if(isset(self::$config["database"][$database])) {
				return self::$config["database"][$database];
			}
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
			if(count($nameSpaceArray) == 1 && !strstr($className,"Factory") && $nameSpaceArray[0] != "Skeet") {
				array_push($nameSpaceArray,"classes");
			}
			foreach($nameSpaceArray as $namespace) {
				$includePath .= strtolower($namespace) . '/';
			}
			$fileName = substr_replace(preg_replace_callback("/([A-Z])/",
							function ($matches) {
								return strtolower("_" . $matches[0]);
							},
					  $className),'',0,1);
			$fileNameArray = explode("_",$fileName);
			if(!stristr($includePath,"generated")) {
				switch($fileNameArray[count($fileNameArray)-1]) {
					case "factory":
						$includePath .= $fileNameArray[count($fileNameArray)-1] . "/";
						break;
				}
			}
			$fileName .= ".class.php";
			
			if(file_exists($includePath . $fileName)) {
				require_once($includePath . $fileName);
			}
		}

		public static function Skeet() {
			die("YEAHHHHHH");
		}
	}

?>