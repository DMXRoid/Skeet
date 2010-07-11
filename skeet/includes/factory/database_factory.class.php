<?
	/*
		database_factory.class.php
		
		Contains the DatabaseFactory, your one and only method of instantiating and retreiving 
		already existing database objects.  At NO POINT should you ever instantiate a database object
		directly.  DB connections are fucking expensive, minimize them.
	*/

	namespace Skeet;
	
	class DatabaseFactory {
		public static $dbList = array();
		public static $databaseType = DATABASE_TYPE_MYSQL;
		
		
		public static function getDatabase($dbName=DB_NAME,$dbHost=DB_HOST,$dbUsername=DB_USERNAME,$dbPassword=DB_PASSWORD,$databaseType='') {	
			if(!$databaseType) {
				$databaseType = self::$databaseType;
			}
			
			if(!isset(self::$dbList[$dbName]) || !is_object(self::$dbList[$dbName])) {	
				switch($databaseType) {
					case DATABASE_TYPE_MYSQL:
						$db = new \Skeet\Database\MysqlDatabase($dbName,$dbHost,$dbUsername,$dbPassword);
						self::$dbList[$dbName] = $db;		
						break;
						
					default:
						throw new InstantiateDatabaseException();
						break;
				}
			}
			return self::$dbList[$dbName];
		}
		
		public static function addDatabase($dbName,$dbObject) {
			self::$dbList[$dbName] = $dbObject;	
		}
	}
?>