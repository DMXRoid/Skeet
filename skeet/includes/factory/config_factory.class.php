<?	
	class ConfigFactory {
		public static $configs = array();
		
		public static function getConfig($key) {
			if(!isset(self::$configs[$key])) {
				$db = DatabaseFactory::getDatabase(FRAMEWORK_DB);
				$sql = "SELECT config FROM config WHERE config_key = " . $db->quote($key);
				$result = $db->doQuery($sql);
				if($row = $result->getRow()) {
					$config = new Config($row["utrib_config_id"]);
				}
				else {
					$config = new UtribConfig();
				}
				self::$configs[$key] = $config;
			}
			return self::$configs[$key];
		}
		
		public static function getConfigValue($key) {
			return self::getConfig($key)->getConfigValue();
		}
		
		public function setConfigValue($key,$value) {
			self::getConfig($key)->setConfigValue($value);
			self::getConfig($key)->save();
		}
	}
?>