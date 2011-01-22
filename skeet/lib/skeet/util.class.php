<?
	/**
	 * @package Skeet
	 * @subpackage Component
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	 namespace Skeet;

	 class Util {
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
	 }
?>