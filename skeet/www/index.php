<?
	/*
		index.php
		
		This page does everything.  It's not terribly complicated, you load in application.inc.php,
		you set up a couple of try/catch blocks, and you render the page.
	
	*/
	namespace Skeet;
	try {
		require_once("../application.inc.php");
		try {
			
			if(!getRequestValue(PAGE_REQUEST_KEY)) {                                                                                                                                                                                                                                                      
            if($_SERVER["REDIRECT_URL"] != "/") {                                                                                                                                                                                                                                            
               $pageString = strtolower(trim(str_replace("/"," ",$_SERVER["REDIRECT_URL"])));
               $_REQUEST[PAGE_REQUEST_KEY] = $pageString;                                                                                                                                                                                                                                              
            }                                                                                                                                                                                                                                                                                
            else {                                                                                                                                                                                                                                                                           
               $_REQUEST[PAGE_REQUEST_KEY] = "Home";                                                                                                                                                                                                                                                   
            }                                                                                                                                                                                                                                                                                
         }                     
			if(!getRequestValue(PAGE_REQUEST_KEY)) {
				$_REQUEST[PAGE_REQUEST_KEY] = "Home";
			}
			PageFactory::getPage(getRequestValue(PAGE_REQUEST_KEY))->render();
		}
		catch(\Exception $e) {
			$e->processException();
		}
/*		if(DEBUG) {
			Debug::endAllPerformanceLogs();
			Debug::displayDebugData();
		}*/
	}
	catch(\Exception $e) {
		echo "We're sorry, but an error has occured.";
	}
?>