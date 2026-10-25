<?php
	/**
	* @package Skeet
	* @version 1.0
	* @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	declare(strict_types=1);

	namespace Skeet;

	class Util {

		public static function jamvar(mixed $toJam): void {
			echo "<pre>";
			print_r($toJam);
			echo "</pre>";
		}

		/**
		 * @param string|array<int,string> $key
		 */
		public static function requestExists(string|array $key): bool {
			if (is_array($key)) {
				$tempRequest = $_REQUEST;
				foreach ($key as $arrayKey) {
					if (isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
						$tempRequest = $tempRequest[$arrayKey];
					}
					else {
						return false;
					}
				}
				return true;
			}
			else {
				if (isset($_REQUEST[$key]) && $_REQUEST[$key]) {
					return true;
				}
			}
			return false;
		}

		/**
		 * @param string|array<int,string> $key
		 * @return string|array<mixed>
		 */
		public static function getRequestValue(string|array $key): string|array {
			if (!is_array($key)) {
				if (self::requestExists($key)) {
					if (is_array($_REQUEST[$key])) {
						return $_REQUEST[$key];
					}
					else {
						return trim($_REQUEST[$key]);
					}
				}
			}
			else {
				if (self::requestExists($key)) {
					$tempRequest = $_REQUEST;
					foreach ($key as $arrayKey) {
						if (isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
							$tempRequest = $tempRequest[$arrayKey];
						}
					}
					return trim($tempRequest);
				}
			}

			return '';
		}

		/**
		 * Map a SQL column type to the framework {@see DataType}.
		 *
		 * The mapping now lives on the enum ({@see DataType::fromSql()}); this
		 * remains as a convenience wrapper. See docs/findings.md F-004.
		 */
		public static function getDatatypeFromSQL(string $sqlDatatype): DataType {
			return DataType::fromSql($sqlDatatype);
		}
	}
