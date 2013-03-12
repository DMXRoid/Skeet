<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	
	class DatabaseFactory extends \Skeet\DatabaseFactory {
		public static $dbList = array();		
		
		public static function getDatabase($dbConfigName="default") {
			$configArray = \Skeet\Skeet::getDatabaseConfig($dbConfigName);
			if(!isset(self::$dbList[$dbConfigName]) || !is_object(self::$dbList[$dbConfigName])) {
				$db = null;
				switch($configArray["database_type"]) {
					
				}
				if(is_null($db)) {
					$db = parent::getDatabase($dbConfigName);
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