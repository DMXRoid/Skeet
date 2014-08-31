<?
	/**
	 * @package Skeet
	 * @subpackage Session
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Session;
	
	/**
	 * PhpSession is a Skeet-compatible implementation of PHP's 
	 * internal session functions.  It's just a straight up default
	 * setup, so if this doesn't work for you, you can sub-class it 
	 * in your application and make whatever changes you need, just add
	 * it to your application's SessionFactory switch.
	 */
	
	class PhpSession extends AbstractSession {


		protected function open() {
			session_start();
		}

		public function set($key,$value) {
			parent::set($key,$value);
			$_SESSION[$key] = $value;
		}

		public function get($key) {
			return (parent::get($key)) ?: $_SESSION[$key];
		}

		public function destroy() {
			$_SESSION = array();
			$cookieParams = session_get_cookie_params();
			setcookie(session_name(),'',time() - 36000,$cookieParams["path"],$cookieParams["domain"],$cookieParams["secure"],$cookieParams["httponly"]);
			session_destroy();
		}
	}
?>