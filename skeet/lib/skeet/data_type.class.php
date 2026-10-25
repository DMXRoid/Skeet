<?php
	/**
	 * @package Skeet
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * The framework's internal field data types.
	 *
	 * Backed by the original integer values (1-8) so any persisted data or
	 * generated code that stored the old `DATA_TYPE_*` constants continues to
	 * line up. Single source of truth: previously these lived (triplicated) as
	 * constants on Skeet, constants on Util, and global define()s.
	 *
	 * @package Skeet
	 */
	enum DataType: int {
		case String = 1;
		case Integer = 2;
		case Array = 3;
		case DateTime = 4;
		case Float = 5;
		case TinyInt = 6;
		case Text = 7;
		case Time = 8;

		/**
		 * Map a SQL column type (e.g. "varchar", "int identity", "datetime")
		 * to the framework data type. Non-alphabetic characters are stripped
		 * first, so "int identity" matches as "intidentity" (the legacy code
		 * had a dead "int identity" case because it matched after stripping).
		 */
		public static function fromSql(string $sqlType): self {
			$normalized = strtolower((string) preg_replace('/[^a-zA-Z]/', '', $sqlType));

			return match ($normalized) {
				'int', 'integer', 'smallint', 'mediumint', 'bigint', 'intidentity' => self::Integer,
				'tinyint', 'bool', 'boolean', 'bit'                                => self::TinyInt,
				'float', 'double', 'real', 'decimal', 'numeric'                    => self::Float,
				'date', 'datetime', 'timestamp'                                    => self::DateTime,
				'time'                                                             => self::Time,
				'text', 'tinytext', 'mediumtext', 'longtext', 'bigtext', 'clob'    => self::Text,
				'varchar', 'char', 'character', 'nvarchar', 'nchar', 'string'      => self::String,
				default                                                            => self::String,
			};
		}
	}
