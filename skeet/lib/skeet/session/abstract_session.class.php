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
	 * Core session handling. Extend for a specific backend (PHP sessions, a DB,
	 * etc.) and register it in a SessionFactory. Optionally caches values on the
	 * object (handy when the backend is remote/DB).
	 */
	abstract class AbstractSession {

		/** @var array<string,mixed> local cache of session values */
		protected array $sessionVariables = [];

		/** Whether to cache set/gotten values on the object. */
		protected bool $doCacheSessionVariables = false;

		public function __construct() {
			$this->open();
		}

		protected function getDoCacheSessionVariables(): bool {
			return $this->doCacheSessionVariables;
		}

		/** Called from the constructor; do session_start() or backend priming here. */
		protected function open(): void {}

		/** Called from the destructor; NOT for destroying a session (see destroy()). */
		protected function close(): void {}

		/** Tear down the session. Not auto-called. */
		public function destroy(): void {}

		/**
		 * Set a session value. Subclasses implement the real storage and call
		 * parent::set() to keep the local cache in sync.
		 */
		public function set(string $key, mixed $value): void {
			if ($this->getDoCacheSessionVariables()) {
				$this->sessionVariables[$key] = $value;
			}
		}

		/**
		 * Get a session value. Subclasses implement the real lookup and may call
		 * parent::get() first for a cached value. Returns null when uncached /
		 * caching disabled.
		 */
		public function get(string $key): mixed {
			if ($this->getDoCacheSessionVariables() && isset($this->sessionVariables[$key])) {
				return $this->sessionVariables[$key];
			}
			return null;
		}

		public function delete(string $key): void {
			unset($this->sessionVariables[$key]);
		}

		public function __destruct() {
			$this->close();
		}
	}
