<?
	/**
	* @package Skeet
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet;

	class Util {

		const DATA_TYPE_STRING = 1;
		const DATA_TYPE_TEXT = 2;
		const DATA_TYPE_INTEGER = 3;
		const DATA_TYPE_DATETIME = 4;
		const DATA_TYPE_FLOAT = 5;
		const DATA_TYPE_TINYINT = 6;


		public static function jamvar($toJam) {
			echo "<pre>";
			print_r($toJam);
			echo "</pre>";
		}

		public static function requestExists($key) {
			if(is_array($key)) {
				$tempRequest = $_REQUEST;
				foreach($key as $arrayKey) {
					if(isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
						$tempRequest = $tempRequest[$arrayKey];
					}
					else {
					return false;
					}
				}
				return true;
			}
			else {
				if(isset($_REQUEST[$key]) && $_REQUEST[$key]) {
					return true;
				}
			}
			return false;
		}
		
		public static function getRequestValue($key) {
			if(!is_array($key)) {
				if(self::requestExists($key)) {
					if(is_array($_REQUEST[$key])) {
						return $_REQUEST[$key];	
					}
					else {
						return trim($_REQUEST[$key]);
					}
				}
			}
			else {
				if(self::requestExists($key)) {
					$tempRequest = $_REQUEST;
					foreach($key as $arrayKey) {
						if(isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
							$tempRequest = $tempRequest[$arrayKey];
						}
					}
					return trim($tempRequest);
				}
			}

			return '';
		}

		public static function getDatatypeFromSQL($sqlDatatype) {
			$sqlDatatype = preg_replace("/[^a-zA-Z]/",'',$sqlDatatype);
			$dataType = self::DATA_TYPE_STRING;
			switch($sqlDatatype) {
				case "varchar":
				$dataType = self::DATA_TYPE_STRING;
				break;

				case "text":
				case "bigtext":
				$dataType = self::DATA_TYPE_TEXT;
				break;

				case "int":
				case "bigint":
				$dataType = self::DATA_TYPE_INTEGER;
				break;

				case "datetime":
				case "timestamp":
				$dataType = self::DATA_TYPE_DATETIME;
				break;

				case "float":
				case "decimal":
				$dataType = self::DATA_TYPE_FLOAT;
				break;

				case "tinyint":
				$dataType = self::DATA_TYPE_TINYINT;
				break;
			}
			return $dataType;
		}
	}
?>