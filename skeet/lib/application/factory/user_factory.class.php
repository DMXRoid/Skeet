<?
	namespace Skeet;
	class UserFactory {
		public static $currentUser;
		
		public static function getCurrentUser() {
			if(!is_object(self::$currentUser)) {
				$userID = SessionFactory::getSessionVariable("user_id");
				self::$currentUser = ModelFactory::getModel('user',$userID);
			}
			return self::$currentUser;
		}
		
		public static function doLogin($username,$password) {
			$db = DatabaseFactory::getDatabase(FRAMEWORK_DB);
			$sql = "SELECT * FROM user WHERE username = " . $db->quote($username);
			$result = $db->doQuery($sql);
			if($row = $result->getRow()) {
				if(sha1($password . $row["salt"]) == $row["password"]) {
					SessionFactory::setSessionVariable("user_id",$row["user_id"]);
					self::$currentUser = ModelFactory::getModel('user',$row["user_id"]);
					return true;
				}
			}
			return false;
		}
		
		public static function getUserFromUsername($username) {
			$db = DatabaseFactory::getDatabase(FRAMEWORK_DB);
			$sql = "SELECT user_id FROM user WHERE username = " . $db->quote($username);
			$result = $db->doQuery($sql);
			$userID = false;
			if($row = $result->getRow()) {
				$userID = $row["user_id"];
			}
			$user = ModelFactory::getModel('user',$userID);
			return $user;
		}
		
		public static function doesUserExist($username) {
			$db = DatabaseFactory::getDatabase(FRAMEWORK_DB);
			$sql = "SELECT user_id FROM user WHERE username = " . $db->quote($username);
			$result = $db->doQuery($sql);
			return $result->getNumRows();
		}
	}
?>
