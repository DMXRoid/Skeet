<?php
	/**
	 * @package Skeet
	 * @subpackage Cli
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Cli\Command;

	use Skeet\Cli\Args;
	use Skeet\Cli\Console;
	use Skeet\Migration\Migrator;

	/**
	 * `migrate` / `migrate:rollback` / `migrate:status` — drives the Migrator.
	 */
	class MigrateCommand {

		public function __construct(private Migrator $migrator) {}

		public function up(Args $args, Console $console): int {
			$applied = $this->migrator->migrate($this->steps($args, null));
			$console->writeln($applied === [] ? "Nothing to migrate." : "Migrated: " . implode(', ', $applied));
			return 0;
		}

		public function down(Args $args, Console $console): int {
			$reverted = $this->migrator->rollback($this->steps($args, 1) ?? 1);
			$console->writeln($reverted === [] ? "Nothing to roll back." : "Rolled back: " . implode(', ', $reverted));
			return 0;
		}

		public function status(Args $args, Console $console): int {
			$status = $this->migrator->status();
			if ($status === []) {
				$console->writeln("No migrations found.");
				return 0;
			}
			foreach ($status as $version => $applied) {
				$console->writeln(($applied ? "[x] " : "[ ] ") . $version);
			}
			return 0;
		}

		private function steps(Args $args, ?int $default): ?int {
			$steps = $args->option('steps');
			return ($steps === null || $steps === true) ? $default : (int) $steps;
		}
	}
