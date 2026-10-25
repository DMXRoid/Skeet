<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * The place to get the session object. The handler is chosen by the
	 * `session_handler` config directive; the default is PHP's built-in sessions.
	 * Apps add their own handlers by subclassing \Skeet\Session\AbstractSession
	 * and extending this factory (or providing their own).
	 *
	 * @package Skeet
	 * @subpackage Factory
	 */
	class SessionFactory {

		protected static ?\Skeet\Session\AbstractSession $session = null;

		public static function getSession(): \Skeet\Session\AbstractSession {
			if (self::$session === null) {
				$handler = (string) \Skeet\Skeet::getConfig("session_handler");
				self::$session = match ($handler) {
					default => new \Skeet\Session\PhpSession(),
				};
			}
			return self::$session;
		}
	}
