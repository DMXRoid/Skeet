<?

	/*
		database_exception.class.php
		
		All-purpose database exception.  You can break it out into exceptions for 
		each $errorType, but this is less code :)
		
		Emailing of this exception is turned on.  You really do want to know when 
		a database error occurs, because they aren't a result of simple user error,
		they're a fundamental break in the application.
		
	*/
	
	namespace Skeet\Exception;
	
	class DatabaseException extends \Skeet\Exception\AbstractException {
		protected $doEmail = true;
		
		public function __construct($databaseObject,$errorType) {
			switch($errorType) {
				case \Skeet\Database\Mysql\MySqlDatabase::ERROR_CONNECT:
					$message = "There was an error connecting to the host " . $databaseObject->getDBHost();
					break;
				
				case \Skeet\Database\Mysql\MySqlDatabase::ERROR_DB_SELECT:
					$message = "There was an error connecting to the database " . $databaseObject->getDBName();
					break;
				
				case \Skeet\Database\Mysql\MysqlDatabase::ERROR_INSERT:
					$message = "There was an error on insert.  
The query was: " . $databaseObject->getQuery();
					break;
					
				case \Skeet\Database\Mysql\MysqlDatabase::ERROR_UPDATE:
					$message = "There was an error on update.  
The query was: " . $databaseObject->getQuery();
					break;
					
				case \Skeet\Database\Mysql\MysqlDatabase::ERROR_QUERY:
					$message = "There was a query error.  
The query was: " . $databaseObject->getQuery();
					break;
			}
			
			$message .= "
			
The error is: " . $databaseObject->getMySQLError();
			parent::__construct($message,$errorType);
		}
	}
?>