<?
	/* 
		global_variables.inc.php
		
		Slightly misleading name.  This file actually contains globally defined constants, not
		variables.  Still, if you had to do a non-superglobal global variable, this would be 
		the place to do it.  I'd avoid that though, it's shitty coding.
	*/
	
	/* 
		session related definitions
	*/

	define("SESSION_TIMEOUT",0);
	
	
	define("INDEX_PAGE","index.php");
	
	/*
		various include paths && directories
	*/
	
	define("PAGE_INCLUDE_PATH",INCLUDE_PATH . "pages/");
	define("COMPONENT_INCLUDE_PATH",INCLUDE_PATH . "components/");
	define("JS_DIRECTORY","js/");
	define("CSS_DIRECTORY","css/");
	
	define("PAGE_REQUEST_KEY","page");
	
	define("GENERATED_PATH",INCLUDE_PATH . "generated/");
	define("GENERATED_MODEL_PATH",GENERATED_PATH . "model/");
	define("GENERATED_FACTORY_PATH",GENERATED_PATH . "factory/");
	define("GENERATED_CRUD_PATH",GENERATED_PATH . "crud/");
	define("GENERATED_PAGE_PATH",GENERATED_PATH . "page/");
	
	define("MODEL_DATABASE_NAME","framework");
	
	define("MODEL_PATH",INCLUDE_PATH . "model/");
	define("CRUD_PATH",INCLUDE_PATH . "crud/");
	
	
	/*
		Database Types for use by DatabaseFactory
	*/
	
	define("DATABASE_TYPE_MYSQL","mysql");
	define("DATABASE_TYPE_POSTGRES","postgres");
	
	define("FRAMEWORK_DB","framework");
	
	/*
		Email Types for Emailer
	*/
	
	define("EMAIL_TYPE_TEXT_ONLY",1);
	define("EMAIL_TYPE_HTML_ONLY",2);
	define("EMAIL_TYPE_MULTIPART",3);
	
	/*
		automatic request checking constants
	*/
	
	define("REQUEST_CHECK_TYPE_EXISTS",1);
	define("REQUEST_CHECK_TYPE_MIN_6_CHARACTERS",2);
	define("REQUEST_CHECK_TYPE_IS_NUMERIC",3);
	define("REQUEST_CHECK_TYPE_VALID_PASSWORD",4);
	
	/*
		User email types
	*/
	
	define("USER_EMAIL_TYPE_PRIMARY",1);
	
	/*
		default email values
	*/
	
	define("DEFAULT_FROM_ADDRESS","info@" . DOMAIN);
	define("DEFAULT_FROM_NAME",DOMAIN . " Information");
	
	/* data types */
	
	define("DATA_TYPE_STRING",1);
	define("DATA_TYPE_INTEGER",2);
	define("DATA_TYPE_ARRAY",3);
	define("DATA_TYPE_DATETIME",4);
	define("DATA_TYPE_FLOAT",5);
	define("DATA_TYPE_TINYINT",6);
	define("DATA_TYPE_TEXT",7);
	
	/* collection types  */
	define("COLLECTION_TYPE_ONE_TO_MANY",1);
	define("COLLECTION_TYPE_MANY_TO_MANY",2);
	
	/* validation error types */
?>