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
	 * AbstractSession is the core class for session handling.  You can
	 * implement any sort of session handling that you want to at the application 
	 * level by extending AbstractSession, and it'll be used consistently application
	 * and Skeet-wide without any additional configuration.  
	 * 
	 * You can set the session handling type you want to use in the skeet.ini file, with
	 * the "session_handler" directive.  That value will be passed first to the application's
	 * SessionFactory (if one exists), and then to Skeet's.  The default method is just to 
	 * use PHP's internal session handling
	 *
	 * Decided to go the route that I did instead of using PHP's session_set_save_handler
	 * because a.) this way provides more flexibility, b.) access to object methods are restricted
	 * to static calls, c.) consistency in terms of running everything through Skeet objects, and:
	 * d.) session_set_save_handler is a gigantic pain in the ass.
	 */
	
	abstract class AbstractSession {
		
		/**
		 *	If you want it to, Skeet will cache set and retreived 
		 * session values to its own session handler.  While this
		 * wouldn't make much of a difference if you're using the 
		 * internal PHP handler, it could make things faster if you're
		 * storing session data in a DB.
		 * 
		 * Key/value array
		 * 
		 * @see get(),set(),getSessionVariables()
		 * @access protected
		 * @var array
		 */
		
		protected $sessionVariables = array();
		
		/**
		 *	Whether or not to cache set/gotten session variables 
		 * local to the session object
		 * 
		 * @see getDoCacheSessionVariables()
		 * @access protected
		 * @var boolean
		 */
		protected $doCacheSessionVariables = false;
		
		public function __construct() { 
			$this->open();
		}
		
		/**
		 *	Get whether or not to cache session variables
		 * 
		 * @see $doCacheSessionVariables
		 * @access protected
		 * @return boolean
		 */
		
		protected function getDoCacheSessionVariables() {
			return $this->doCacheSessionVariables;
		}
		
		/**
		 * If you need to do anything special at the start of a session, 
		 * (ie, calling session_start(), priming some DB tables, whatever),
		 * do it here.  It's called during the constructor.
		 */
		
		protected function open() { }
		
		/**
		 * If you need to do anything special to end a session,
		 * do it here.  This is NOT for destroying a session, that's 
		 * {@link destroy()}.  This is called automatically by the 
		 * destructor
		 */
		
		protected function close() { }
		
		/**
		 * For doing stuff when the session is destroyed.  Not auto-called
		 */
		
		public function destroy() { }
		
		
		/**
		 *	Set a session variable.  This function must be 
		 * implemented by classes that extend AbstractSession,
		 * the one here just exists to handle caching for you.  Call
		 * parent::set() at the beginning/end of your specific instance.
		 * 
		 * Keys and values can be anything, depending on what your 
		 * subclassed version can handle
		 * 
		 * @param mixed $key
		 * @param mixed $value 
		 * @access public
		 */
		
		public function set($key,$value) {
			if($this->getDoCacheSessionVariables()) {
				$this->sessionVariables[$key] = $value;
			}
		}
		
		/**
		 *	Get a session variable.  This function must be implemented
		 * by classes that extend AbstractSession.  Call parent::get()
		 * at the beginning/end of your own implementation of get()
		 * if you want a locally cached value.  Returns null if there's 
		 * no cached value or if caching is disabled.
		 * 
		 * @access public
		 * @param mixed $key
		 * @param mixed $value 
		 * @return mixed|null
		 */
		
		public function get($key,$value) {
			if($this->getDoCacheSessionVariables() && isset($this->sessionVariables[$key])) {
				return $this->sessionVariables[$key];
			}
			return null;
		}
		
		public function delete($key) {
			if($this->getDoCacheSessionVariables() && isset($this->sessionVariables[$key])) {
				unset($this->sessionVariables[$key]);
			}
		}
		
		
		public function __destruct() { 
			$this->close();
		}
		
	}
?>