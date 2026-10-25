<?php
	/**
	 * @package Skeet
	 * @subpackage Cli
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Cli;

	/**
	 * A tiny command dispatcher. Commands are registered as
	 * `callable(Args, Console): int` and keyed by name; `run()` dispatches,
	 * prints help for unknown/`help`, and turns thrown exceptions into a clean
	 * error + non-zero exit.
	 */
	class Cli {

		/** @var array<string,array{0:callable,1:string}> name => [handler, description] */
		private array $commands = [];

		public function __construct(private Console $console) {}

		public function register(string $name, callable $handler, string $description = ''): void {
			$this->commands[$name] = [$handler, $description];
		}

		public function run(Args $args): int {
			$command = $args->command;

			if ($command === 'help' || $command === '') {
				$this->printHelp();
				return 0;
			}
			if (!isset($this->commands[$command])) {
				$this->console->error("Unknown command: {$command}");
				$this->printHelp();
				return 1;
			}

			try {
				return (int) ($this->commands[$command][0])($args, $this->console);
			}
			catch (\Throwable $e) {
				$this->console->error("Error: " . $e->getMessage());
				return 1;
			}
		}

		private function printHelp(): void {
			$this->console->writeln("Skeet CLI");
			$this->console->writeln("Usage: skeet <command> [options]");
			$this->console->writeln("");
			$this->console->writeln("Commands:");
			foreach ($this->commands as $name => [$handler, $description]) {
				$this->console->writeln("  " . str_pad($name, 22) . $description);
			}
		}
	}
