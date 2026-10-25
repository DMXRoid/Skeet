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
	 * The validation vocabulary — distinct from {@see DataType} (which is about
	 * storage). A field's validation defaults to the mapping from its DataType
	 * ({@see fromDataType()}), but a model can override per field with a
	 * semantic type like Email or Exists.
	 *
	 * @package Skeet
	 */
	enum ValidationType {
		case String;
		case Integer;
		case Float;
		case Boolean;
		case DateTime;
		case Array;
		case Email;
		case Exists;

		/** The default validation for a storage {@see DataType}. */
		public static function fromDataType(DataType $dataType): self {
			return match ($dataType) {
				DataType::Integer, DataType::TinyInt => self::Integer,
				DataType::Float                      => self::Float,
				DataType::DateTime, DataType::Time   => self::DateTime,
				DataType::Array                      => self::Array,
				default                              => self::String, // String, Text
			};
		}
	}
