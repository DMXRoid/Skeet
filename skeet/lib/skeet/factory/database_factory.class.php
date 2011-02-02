<?
	/**
	* @package Skeet
	* @subpackage Factory
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Factory;
	
	class DatabaseFactory {
		public static $dbList = array();		
		
		public static function getDatabase($dbConfigName="default") {
			if(!isset(self::$dbList[$dbConfigName]) || !is_object(self::$dbList[$dbConfigName])) {
					$db = new \Skeet\Database\Mysql\MysqlDatabase(
								\Skeet\Skeet::getConfig("database." . $dbConfigName . ".database_name"),
								\Skeet\Skeet::getConfig("database." . $dbConfigName . ".database_host"),
								\Skeet\Skeet::getConfig("database." . $dbConfigName . ".database_username"),
								\Skeet\Skeet::getConfig("database." . $dbConfigName . ".database_password")
					);
					self::$dbList[$dbConfigName] = $db;
			}
			return self::$dbList[$dbConfigName];
		}
		
		public static function addDatabase($dbName,$dbObject) {
			self::$dbList[$dbName] = $dbObject;	
		}
	}
?>