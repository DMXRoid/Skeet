<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/

	namespace Skeet;
	
	/**
	 * The place to get database objects
	 * @package Skeet
	 * @subpackage Factory
	 */
	
	class DatabaseFactory {
		/**
		 * Caches database connections for future calls
		 * @access public
		 * @var \Skeet\Database\AbstractDatabase[]
		 */
		public static $dbList = array();		
		
		/**
		 * Get a database object based on the config settings
		 * @access public
		 * @static
		 * @param string $dbConfigName Config name to use when pulling info from \Skeet\Skeet::getDatabaseConfig
		 * @return \Skeet\Database\AbstractDatabase
		 */

		public static function getDatabase($dbConfigName="default") {
			$configArray = \Skeet\Skeet::getDatabaseConfig($dbConfigName);
			if(!isset(self::$dbList[$dbConfigName]) || !is_object(self::$dbList[$dbConfigName])) {
				switch($configArray["database_type"]) {
					case "mysql":
						$db = new \Skeet\Database\Mysql\MysqlDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);
						break;
					
					case "mysqli":
						$db = new \Skeet\Database\Mysql\Mysqli\MysqliDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);
						break;
					
					case "sqlsrv":
						$db = new \Skeet\Database\Sqlsrv\SqlsrvDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);
						break;
					
					case "mssql":
						$db = new \Skeet\Database\Mssql\MssqlDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);
						break;
				}
				self::$dbList[$dbConfigName] = $db;
			}
			return self::$dbList[$dbConfigName];
		}
		
		public static function addDatabase($dbName,$dbObject) {
			self::$dbList[$dbName] = $dbObject;	
		}
	}
?>