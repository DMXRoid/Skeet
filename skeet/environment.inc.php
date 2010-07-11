<?
	/*
		environment.inc.php
		
		Dynamic (sort of) detection of the current environment, and assignment
		of relevant constants based on that.  You can pretty much choose any method
		to do the actual check, although some combination of $_SERVER["DOCUMENT_ROOT"] and 
		$_SERVER["SERVER_NAME"] will likely be the best way to go, and the examples here will
		use that method.
		
	*/	

	switch(@$_SERVER["SERVER_NAME"]) {
		/*
			First, we check for the server name
		*/
		default:
			/*
				Now, if you want to, you can do further matching inside this block.  For example:
				
				if(stristr($_SERVER["DOCUMENT_ROOT"],'developer_name')) {
					// do some stuff
				}
				
				would be useful if you have multiple developers working off the same domain, but 
				on different servers (local dev servers that both resolve to "testing.foo.com" within
				their respective networks
			*/
			
			
			/* 	
				MySQL database constants
				
				If you're going to be using the MySQL Database object provided, these NEED to be set
				accordingly.
				
			*/
			
			define("DB_HOST","127.0.0.1");
			define("DB_NAME","test");
			define("DB_USERNAME","");
			define("DB_PASSWORD","");
			define("APPLICATION_ROOT","");  // with trailing slash!				
			
			/*
				Is this a development environment?  It's often useful to be able to perform different behaviors 
				for things like error logging.  Optional, though.
			*/
			
			define("IS_DEV",true);
			define("DEBUG",true);
			define("DOMAIN",@$_SERVER["SERVER_NAME"]);
			
			break;
	}
	
?>
