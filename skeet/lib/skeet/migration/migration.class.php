<?php
	/**
	 * @package Skeet
	 * @subpackage Migration
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Migration;

	/**
	 * A single plain-SQL migration, parsed from a `.sql` file (DD-015).
	 *
	 * Filename: `<version>_<description>.sql`, where `<version>` is a sortable
	 * prefix (e.g. a timestamp). Contents use `-- @up` / `-- @down` delimiter
	 * lines to separate the forward and reverse SQL:
	 *
	 *     -- @up
	 *     CREATE TABLE widget (id INTEGER PRIMARY KEY, name TEXT);
	 *     -- @down
	 *     DROP TABLE widget;
	 */
	final class Migration {

		public function __construct(
			public readonly string $version,
			public readonly string $name,
			public readonly string $upSql,
			public readonly string $downSql
		) {}

		public static function fromFile(string $path): self {
			$base = basename($path, '.sql');
			$parts = explode('_', $base, 2);
			$version = $parts[0];
			$name = $parts[1] ?? $base;

			$content = (string) file_get_contents($path);
			$halves = preg_split('/^\s*--\s*@down\s*$/im', $content, 2);
			$up = preg_replace('/^\s*--\s*@up\s*$/im', '', $halves[0] ?? '', 1);
			$down = $halves[1] ?? '';

			return new self($version, $name, trim((string) $up), trim((string) $down));
		}
	}
