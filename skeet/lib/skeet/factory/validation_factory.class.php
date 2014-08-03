<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/
	
	namespace Skeet;
	
	/**
	 * A set of methods for validating input
	 * @package Skeet
	 * @subpackage Factory
	 */

	class ValidationFactory {
		const VALIDATION_TYPE_STRING = 1;
		const VALIDATION_TYPE_INT = 2;
		const VALIDATION_TYPE_ARRAY = 3;
		const VALIDATION_TYPE_DATETIME = 4;
		const VALIDATION_TYPE_FLOAT = 5;
		const VALIDATION_TYPE_TINYINT = 6;
		const VALIDATION_TYPE_TEXT = 6;
		const VALIDATION_TYPE_EXISTS = 8;
		const VALIDATION_TYPE_EMAIL = 9;
		
		
		public static function validate($input,$validationType=NULL) {
			return true;
			$isValid = false;
			switch($validationType) {
				case DATA_TYPE_TEXT:
				case DATA_TYPE_STRING:
					if(is_string($input)) {
						$isValid = true;
					}
					break;
				case DATA_TYPE_TINYINT:
				case DATA_TYPE_INTEGER:
					if(is_int($input)) {
						$isValid = true;
					}
					break;
				
				case DATA_TYPE_ARRAY:
					if(is_array($input)) {
						$isValid = true;
					}
					break;
					
				case DATA_TYPE_DATETIME:
					if(strtotime($input)) {
						$isValid = true;
					}
					break;
				
				case DATA_TYPE_FLOAT:
					if(is_float($input) || is_double($input) || is_numeric($input)) {
						$isValid = true;
					}
					break;
					
				//case DATA_TYPE_EXISTS:
				default:
					if($input) {
						$isValid = true;
					}
					break;
					
				case self::VALIDATION_TYPE_EMAIL:
					$pattern = "/[[:print:]^@^ ]+(\+[[:print:]^@^ ]){0,1}+@+(([[:alnum:]\._\-])+\.){1,5}+[a-zA-Z]{2,6}/";
					if(preg_match($pattern,$input)) { 
						$isValid = true;	
					}
					break;
			}
			return $isValid;
		}
	}
?>