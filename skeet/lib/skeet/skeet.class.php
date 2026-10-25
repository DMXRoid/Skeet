<?php
	/**
	 * @version 1.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	* Global class for the Skeet Framework.  Contains configuration data.
	* Can be extended if you want to run your own set of init things.
	*
	* Note: the internal data-type and collection-type constants that used to
	* live here are now the {@see DataType} and {@see CollectionType} enums.
	*
	* @package Skeet
	*/
	class Skeet {

		/**
		 * version number
		 */
		const VERSION = "1.0";

		/**
		 * Legacy flat config array. Retained only for derived path keys set
		 * during bootstrap; the source of truth is {@see $configObject}. Prefer
		 * Skeet::getConfig().
		 *
		 * @var array<string,mixed>
		 */
		public static array $config = [];

		/**
		 * The configuration aggregator backing the getConfig()/setConfig()
		 * facade (layered INI + env sources, lazy secret resolution).
		 */
		public static ?Config\Config $configObject = null;

		/**
		 * Initializer for the application.  Loads in the configuration data from
		 * the config file, sets up the autoloader and registers it, and includes
		 * the calling application's load.php file.
		 *
		 * @param string $configName Name of the config in the config file to load up
		 * @param string $configFile Configuration file name
		 */
		public static function init(string $configName = "default", string $configFile = "skeet.ini"): void {

			$configPath = __DIR__ . "/../../etc/" . $configFile;

			// Environment selection: APP_ENV wins over the passed name, so CLI
			// workers (which have no SERVER_NAME) and containers can select a
			// section without the old $_SERVER switch.
			$appEnv = getenv("APP_ENV");
			$section = ($appEnv !== false && $appEnv !== "") ? $appEnv : $configName;

			// The config classes must load before the autoloader is registered
			// (the autoloader itself depends on config), so require them directly.
			self::requireConfigClasses();

			$config = new Config\Config();
			$config->addSource(new Config\IniConfigSource($configPath, $section));
			$config->addSource(new Config\EnvConfigSource());
			$config->addSecretProvider(new Config\EnvSecretProvider());
			self::$configObject = $config;
			self::setConfig("lib_path", self::getConfig("application_path") . 'lib/');
			self::setConfig("application_lib_path", self::getConfig("lib_path") . strtolower((string) self::getConfig("application_name")) . "/");
			self::setConfig("application_model_path", self::getConfig("application_lib_path") . "model/");
			self::setConfig("application_page_path", self::getConfig("application_lib_path") . "pages/");
			self::setConfig("application_namespace", (self::getConfig("namespace")) ?: str_replace(" ", "", ucwords((string) self::getConfig("application_name"))));
			self::setConfig("skeet_lib_path", self::getConfig("lib_path") . "skeet/");
			spl_autoload_register("\Skeet\Skeet::autoload");

			if (file_exists(self::getConfig("application_lib_path") . "load.php")) {
				require_once(self::getConfig("application_lib_path") . "load.php");
			}
		}

		/**
		 * Require the core config classes explicitly. Called during init()
		 * before the autoloader is registered (the autoloader depends on
		 * config, so config cannot depend on the autoloader).
		 */
		private static function requireConfigClasses(): void {
			$dir = __DIR__ . "/config/";
			foreach (["config_source", "secret_provider", "ini_config_source", "env_config_source", "env_secret_provider", "config"] as $file) {
				require_once($dir . $file . ".class.php");
			}
		}

		/**
		 * Set a config value (a runtime override, highest precedence).
		 */
		public static function setConfig(string $key, mixed $value): void {
			self::$config[$key] = $value; // legacy mirror
			self::$configObject?->set($key, $value);
		}

		/**
		 * Get a config value. Returns false when the key is not set (legacy
		 * sentinel; new code can use Skeet::$configObject->get($key, $default)).
		 */
		public static function getConfig(string $key): mixed {
			if (self::$configObject !== null) {
				return self::$configObject->get($key, false);
			}
			return self::$config[$key] ?? false;
		}

		/**
		 * Get settings for a named database config, assembled from the
		 * `database.<name>.*` keys (secret references resolved lazily here).
		 *
		 * @return array<string,mixed>|null
		 */
		public static function getDatabaseConfig(string $database): ?array {
			if (self::$configObject === null) {
				return null;
			}
			$prefix = "database.{$database}.";
			$out = [];
			foreach (self::$configObject->getKeys() as $key) {
				if (str_starts_with($key, $prefix)) {
					$out[substr($key, strlen($prefix))] = self::$configObject->get($key);
				}
			}
			return $out !== [] ? $out : null;
		}

		/**
		 *	The Skeet autoloader.
		 *
		 * The autoloader uses namespaces to determine file paths,
		 * with a couple of usability exceptions for Factory classes
		 * (which are in the root Skeet namespace, but live in their
		 * own directory), and the Skeet base class.
		 *
		 * @param string $classAndNameSpace The instantiated class with full namespace
		 */
		public static function autoload(string $classAndNameSpace): void {
			$nameSpaceArray = explode('\\', $classAndNameSpace);
			$className = array_pop($nameSpaceArray);
			$includePath = self::getConfig("application_path") . "lib/";
			if (count($nameSpaceArray) == 1 && !strstr($className, "Factory") && $nameSpaceArray[0] != "Skeet") {
				array_push($nameSpaceArray, "classes");
			}
			foreach ($nameSpaceArray as $namespace) {
				$includePath .= strtolower($namespace) . '/';
			}
			$fileName = substr_replace(preg_replace_callback(
				"/([A-Z])/",
				function ($matches) {
					return strtolower("_" . $matches[0]);
				},
				$className
			), '', 0, 1);
			$fileNameArray = explode("_", $fileName);
			if (!stristr($includePath, "generated")) {
				switch ($fileNameArray[count($fileNameArray) - 1]) {
					case "factory":
						$includePath .= $fileNameArray[count($fileNameArray) - 1] . "/";
						break;
				}
			}
			$fileName .= ".class.php";

			if (file_exists($includePath . $fileName)) {
				require_once($includePath . $fileName);
			}
		}
	}
