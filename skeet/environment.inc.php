<?
	/**
	 * @package Skeet
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */


	/*
		environment.inc.php
		
		Dynamic (sort of) detection of the current environment, and assignment
		of relevant constants based on that.  You can pretty much choose any method
		to do the actual check, although some combination of $_SERVER["DOCUMENT_ROOT"] and 
		$_SERVER["SERVER_NAME"] will likely be the best way to go, and the examples here will
		use that method.
		
	*/	
	$location = (isset($_SERVER["SERVER_NAME"])) ? $_SERVER["SERVER_NAME"] : gethostname();

	switch(@$_SERVER["SERVER_NAME"]) {
		/*
			First, we check for the server name
		*/
		default:
			define("CONFIG_NAME","default");
			break;
	}
	
?>
