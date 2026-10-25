<?php
	/**
	 * @package Skeet
	 * @subpackage Session
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	declare(strict_types=1);

	namespace Skeet\Session;

	/**
	 * Session handling over PHP's built-in $_SESSION. Subclass it in your app
	 * and register in your SessionFactory if you need different behavior.
	 */
	class PhpSession extends AbstractSession {

		protected function open(): void {
			if (session_status() !== PHP_SESSION_ACTIVE) {
				session_start();
			}
		}

		public function set(string $key, mixed $value): void {
			parent::set($key, $value);
			$_SESSION[$key] = $value;
		}

		public function get(string $key): mixed {
			return parent::get($key) ?? ($_SESSION[$key] ?? null);
		}

		public function delete(string $key): void {
			parent::delete($key);
			unset($_SESSION[$key]);
		}

		public function destroy(): void {
			$_SESSION = [];
			$cookieParams = session_get_cookie_params();
			setcookie(session_name(), '', time() - 36000, $cookieParams["path"], $cookieParams["domain"], $cookieParams["secure"], $cookieParams["httponly"]);
			session_destroy();
		}
	}
