<?
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet;
	
	class DatabaseFactory {
		public static $dbList = array();		
		
		public static function getDatabase($dbConfigName="default") {
			$configArray = \Skeet\Skeet::getDatabaseConfig($dbConfigName);
			if(!isset(self::$dbList[$dbConfigName]) || !is_object(self::$dbList[$dbConfigName])) {
				switch($configArray["database_type"]) {
					case "mysql":
						$db = new \Skeet\Database\Mysql\MysqlDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);
						break;
					
					case "sqlsrv":
						$db = new \Skeet\Database\Sqlsrv\SqlsrvDatabase($configArray["database_name"],$configArray["database_host"],$configArray["database_username"],$configArray["database_password"]);

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