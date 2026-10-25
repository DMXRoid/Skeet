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

	/**
	 * `new <path>` — scaffold a starter application skeleton (directories +
	 * a starter skeet.ini). A starting point, not a full app.
	 */
	class NewCommand {

		/** @var string[] */
		private const DIRECTORIES = ['etc', 'lib', 'www', 'migrations', 'generated'];

		public function run(Args $args, Console $console): int {
			$base = $args->positional(0);
			if ($base === null) {
				$console->error("usage: skeet new <app-path>");
				return 1;
			}
			$base = rtrim($base, '/');

			foreach (self::DIRECTORIES as $dir) {
				if (!is_dir("{$base}/{$dir}")) {
					mkdir("{$base}/{$dir}", 0777, true);
				}
			}

			$iniPath = "{$base}/etc/skeet.ini";
			if (!is_file($iniPath)) {
				file_put_contents($iniPath, $this->starterIni($base));
			}

			$console->writeln("Scaffolded Skeet app at {$base}");
			$console->writeln("Next: edit {$iniPath}, then `skeet migrate` / `skeet generate:models`.");
			return 0;
		}

		private function starterIni(string $base): string {
			return "[default]\n"
				. "application_name = \"App\"\n"
				. "application_path = \"{$base}/\"\n"
				. "database.default.database_type = \"sqlite\"\n"
				. "database.default.database_name = \"{$base}/app.db\"\n";
		}
	}
