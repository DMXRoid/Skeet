<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * A database result — a thin wrapper over a PDOStatement.
	 *
	 * With PDO the result handling is uniform across drivers, so this is now a
	 * single concrete class rather than one subclass per driver.
	 *
	 * @package Skeet
	 * @subpackage Database
	 */
	class AbstractDatabaseResult {

		public function __construct(protected \PDOStatement $statement) {}

		/**
		 * Fetch the next row as an associative array, or false when exhausted.
		 *
		 * @return array<string,mixed>|false
		 */
		public function getRow(): array|false {
			return $this->statement->fetch(\PDO::FETCH_ASSOC);
		}

		/**
		 * All remaining rows as associative arrays.
		 *
		 * @return array<int,array<string,mixed>>
		 */
		public function getResultAsArray(): array {
			return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
		}

		/**
		 * Alias of {@see getResultAsArray()}.
		 *
		 * @return array<int,array<string,mixed>>
		 */
		public function getRows(): array {
			return $this->getResultAsArray();
		}

		/**
		 * Number of rows affected by the statement.
		 *
		 * NOTE: reliable for INSERT/UPDATE/DELETE. For SELECT, rowCount() is
		 * driver-dependent (fine on sqlite/mysql, unreliable on pgsql); prefer
		 * fetching rows and counting, or getRow() for existence checks.
		 */
		public function getNumRows(): int {
			return $this->statement->rowCount();
		}

		public function numRows(): int {
			return $this->getNumRows();
		}

		public function rowCount(): int {
			return $this->statement->rowCount();
		}

		public function getStatement(): \PDOStatement {
			return $this->statement;
		}
	}
