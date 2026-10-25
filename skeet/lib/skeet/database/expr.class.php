<?php
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * A raw SQL expression, inlined verbatim into a statement instead of being
	 * bound as a parameter — the safe, explicit replacement for the legacy
	 * "$quotes = false" convention (e.g. new Expr('NOW()'), new Expr('DEFAULT')).
	 *
	 * Only ever construct these from trusted, developer-authored SQL. Never pass
	 * user input to Expr; use normal bound values for that.
	 */
	final class Expr {

		public function __construct(private string $sql) {}

		public function getSql(): string {
			return $this->sql;
		}

		public function __toString(): string {
			return $this->sql;
		}
	}
