<?php
	/**
	 * @package Skeet
	 * @subpackage Cli
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Cli;

	/**
	 * Parsed command-line input: the command, positional args, and `--key=value`
	 * / `--flag` options.
	 */
	final class Args {

		/**
		 * @param string[]                   $positionals
		 * @param array<string,string|bool>  $options
		 */
		public function __construct(
			public readonly string $command,
			public readonly array $positionals,
			public readonly array $options
		) {}

		/**
		 * @param string[] $argv the raw $argv (including the script name)
		 */
		public static function fromArgv(array $argv): self {
			array_shift($argv); // script name
			$command = array_shift($argv) ?? 'help';

			$positionals = [];
			$options = [];
			foreach ($argv as $arg) {
				if (str_starts_with($arg, '--')) {
					$kv = explode('=', substr($arg, 2), 2);
					$options[$kv[0]] = $kv[1] ?? true;
				}
				else {
					$positionals[] = $arg;
				}
			}
			return new self($command, $positionals, $options);
		}

		public function option(string $key, string|bool|null $default = null): string|bool|null {
			return $this->options[$key] ?? $default;
		}

		public function positional(int $index, ?string $default = null): ?string {
			return $this->positionals[$index] ?? $default;
		}
	}
