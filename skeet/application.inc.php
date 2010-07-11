<?
	/*
		application.inc.php
		
		Does all the necessary setup work to provide access to all classes and such
		that are part of the application.  This file is mostly a set of require() calls.  
		require() is preferred to include()
		
		
		One definition is done in this file, the constant INCLUDE_PATH.  This allows you to move
		the default includes directory (/includes/) to somewhere else on the server for sharing of code,
		or just for making it non-accessible from the web if you'd rather not use Allow/Deny rules
		in Apache.
	*/
	
	/*
		Define INCLUDE_PATH:
			1.) All path definitions should end in a / .
			2.) The example below uses $_SERVER["DOCUMENT_ROOT"] to result in full pathing for includes.  
				 It's not added into php's ini value "include_path", although if you wanted to, you could simply do:
				 ini_set("include_path",".:..:" . INCLUDE_PATH), and you'd be able to do includes that way.  However,
				 include(INCLUDE_PATH . "file.php") is the preferred method.
	*/
	

	/*
		autoload.  whew!
	*/
	
	namespace Skeet;
	
	function skeetAutoload($className) {
		
		/**
		 * break out shit by namespace
		 */
		$nsArray = explode('\\',$className);
		$realClassName = $nsArray[count($nsArray)-1];
		unset($nsArray[count($nsArray)-1]); // get rid of the last array, that's the class name
		unset($nsArray[0]); // we know we're in skeet
		
		$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$realClassName),'',0,1);
		$fileArray = explode("_",$fileName);
		
		$filePath = INCLUDE_PATH;
		$fileType = ".class";
		if(count($nsArray)) {
			$nsString = strtolower(implode("/",$nsArray));
			$nsArray = explode("/",$nsString);
			$filePath .= $nsString . '/';
		//	$fileType = "." . strtolower($nsArray[count($nsArray)-1]);
			
			
		}
		
		
	switch($fileArray[count($fileArray)-1]) {
			case "factory":
			case "exception":
				$filePath = INCLUDE_PATH . $fileArray[count($fileArray)-1] . "/";
				break;
				
			case "result":
				$filePath = INCLUDE_PATH . "database/";
				break;
		}

		$fullPath = $filePath . $fileName . $fileType . ".php";
		
		require_once($fullPath);
		
		/*
			If we're debugging, log a call to __autoload()
		*/
		
		if(DEBUG && $className != "Debug") {
			//	Debug::addAutoLoadCall($className);
		}
	}
	
	spl_autoload_register(__NAMESPACE__ . "\skeetAutoload");
	
	require("environment.inc.php");
	define("INCLUDE_PATH",APPLICATION_ROOT . "includes/");
	if(DEBUG) {
//		Debug::startPerformanceLog("application.inc.php");
	}
	
	require(INCLUDE_PATH . "global_variables.inc.php");
	require(INCLUDE_PATH . "global_functions.inc.php");	
	ob_start();
?>
