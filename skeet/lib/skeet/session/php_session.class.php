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
	}
?>