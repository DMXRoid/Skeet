<?
	class DatabaseDebug {
		public static $databaseQueryDebugs = array();		

		public static function addDatabaseQueryDebug($databaseQueryDebug) {
			self::$databaseQueryDebugs[] = $databaseQueryDebug;
		}
		
		public static function getTotalQueryTime() {
			$queryTime = 0;
			foreach(self::$databaseQueryDebugs as $queryDebug) {
				$queryTime += $queryDebug->getTotalTime();				
			}
		}
		
		public static function getNumQueries() {
			return count(self::$databaseQueryDebugs);
		}
	}
	
?>