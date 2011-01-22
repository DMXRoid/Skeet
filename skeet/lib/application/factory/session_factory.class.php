<?
/*
	SessionFactory does _all_ the session work.  No direct instantiation of the 
	Session or SessionVariable objects should _ever_ be done.
*/
	namespace Skeet;
	class SessionFactory {
		public static $currentSession;
	
		/*

		*/
		public static function getCurrentSession() {
			// have we already found out what the correct session object is?  if so, don't re-do the work.
			if(!is_object(self::$currentSession)) {
				// no?  OK.  check and see if there's a cookie set first
				if(isset($_COOKIE["session_hash"]) && $_COOKIE["session_hash"]) {
					// there is!  hooray!  check the database for a session that has that hash 
					$db = DatabaseFactory::getDatabase(FRAMEWORK_DB);
					$sql = "SELECT session_id FROM session WHERE session_hash = " . $db->quote($_COOKIE["session_hash"]);
					$result = $db->doQuery($sql);
					if($row = $result->getRow()) {
						// found one!  set the current session to that one
						self::$currentSession = ModelFactory::getModel('session',$row["session_id"]);
					}
					else {
						// gah, suck.  unset the $_COOKIE["session_hash"], and call yourself, because you'll generate a new session that way.
						unset($_COOKIE["session_hash"]);
						self::$currentSession = self::getCurrentSession();
					}
				}
				else {
					// no cookie?  generate a new session
					$sessionHash = self::generateSessionHash(); // gotta have a hash
					$session = ModelFactory::getModel('session');
					$session->set('session_hash',$sessionHash);
					$session->save();
					// set the cookie
					setcookie("session_hash",$sessionHash,SESSION_TIMEOUT,'/',DOMAIN);
					self::$currentSession = $session;
				}
			}
			return self::$currentSession;
		}

		// get the value of a session variable

		public static function getSessionVariable($key) {
			$session = self::getCurrentSession();
			// re-init the session variable object.  
			$session->getCollection('session_variable')->reInit();
			// now, loop through until you find the one with that key
			while($sessionVariable = $session->getCollection('session_variable')->getNext()) {
				if($sessionVariable->get('session_variable_name') == $key) {
					// found it?  return the value
					return $sessionVariable->get('session_variable_value');
				}
			}

			// didn't find one?  return bool false
			return false;
		}

		public function deleteSessionVariable($key) {
			$session = self::getCurrentSession();
			// re-init the session variable object.  
			$session->getCollection('session_variable')->reInit();
			// now, loop through until you find the one with that key, so we're not creating duplicate objects
			while($sessionVariable = $session->getCollection('session_variable')->getNext()) {
				if($sessionVariable->get('session_variable_name') == $key) {
					// found one?  set it, son!
					$sessionVariable->set('is_retired',1);
					$sessionVariable->save();
					$session->getCollection('session_variable')->remove($sessionVariable);
					return true;
				}
			}
		}
		
		public static function setSessionVariable($key,$value) {
			$session = self::getCurrentSession();
			// re-init the session variable object.  
			$session->getCollection('session_variable')->reInit();
			// now, loop through until you find the one with that key, so we're not creating duplicate objects
			while($sessionVariable = $session->getCollection('session_variable')->getNext()) {
				if($sessionVariable->get('session_variable_name') == $key) {
					// found one?  set it, son!
					$sessionVariable->set('session_variable_value',$value);
					$sessionVariable->save();
					return true;
				}
			}
			$sessionVariable = ModelFactory::getModel("session_variable");
			$sessionVariable->set('session_id',$session->getID());
			$sessionVariable->set('session_variable_name',$key);
			$sessionVariable->set('session_variable_value',$value);
			$sessionVariable->save();
			return true;
		}

		public static function generateSessionHash() {
			$hashString = $_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] . strtotime("now") . $_SERVER["REMOTE_PORT"];
			$hash = sha1($hashString);
			return $hash;
		}
	}
?>