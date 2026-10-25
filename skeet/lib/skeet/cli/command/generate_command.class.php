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
	use Skeet\Generator\Generator;

	/**
	 * `generate:models` — runs the schema generator into an output directory.
	 */
	class GenerateCommand {

		public function __construct(private Generator $generator) {}

		public function models(Args $args, Console $console): int {
			$outputDir = (string) $args->option('out', 'generated');
			$files = $this->generator->generate($outputDir);
			$console->writeln("Generated " . count($files) . " file(s) into {$outputDir}:");
			foreach ($files as $file) {
				$console->writeln("  " . $file);
			}
			return 0;
		}

		/** `generate:crud <table…>` — opt-in CRUD page classes for named tables. */
		public function crud(Args $args, Console $console): int {
			$tables = $args->positionals;
			if ($tables === []) {
				$console->error("usage: skeet generate:crud <table> [<table>...] [--out=]");
				return 1;
			}
			$outputDir = (string) $args->option('out', 'generated');
			$files = $this->generator->generateCrud($tables, $outputDir);
			$console->writeln("Generated " . count($files) . " CRUD file(s) into {$outputDir}:");
			foreach ($files as $file) {
				$console->writeln("  " . $file);
			}
			return 0;
		}
	}
