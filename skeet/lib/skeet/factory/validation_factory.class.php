<?php
	/**
	* @version 2.0
	* @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * Validates input against a {@see ValidationType}.
	 *
	 * (Historically this began with an unconditional `return true;` — a no-op —
	 * and conflated validation with the storage DataType. It now validates for
	 * real against the dedicated ValidationType vocabulary. See docs/findings.md
	 * F-002/F-003.)
	 *
	 * @package Skeet
	 * @subpackage Factory
	 */
	class ValidationFactory {

		public static function validate(mixed $input, ValidationType $type): bool {
			return match ($type) {
				ValidationType::String   => is_string($input),
				ValidationType::Integer  => is_int($input) || (is_string($input) && ctype_digit($input)),
				ValidationType::Float    => is_numeric($input),
				ValidationType::Array    => is_array($input),
				ValidationType::Boolean  => is_bool($input)
					|| in_array(strtolower(trim((string) $input)), ['0', '1', 'true', 'false', 'on', 'off', 'yes', 'no'], true),
				ValidationType::DateTime => $input !== null && $input !== '' && strtotime((string) $input) !== false,
				ValidationType::Email    => is_string($input) && filter_var($input, FILTER_VALIDATE_EMAIL) !== false,
				ValidationType::Exists   => $input !== null && $input !== '',
			};
		}
	}
