<?
	/**
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	* @license FreeBSD
	*/

	namespace Skeet\Database\Mysql\Mysqli;
	
	/**
	 * MySQL Database Object, using the MySQLi driver.
	 * 
	 * @package Skeet
	 * @subpackage Database
	 */
	
	class MysqliDatabase extends \Skeet\Database\Mysql\MysqlDatabase {
		protected $mysqliObject;
		
		public function setMysqliObject($mysqliObject) {
			$this->mysqliObject = $mysqliObject;
		}
		
		public function getMysqliObject() {
			return $this->mysqliObject;
		}
		
		public function initConnection() {
			$mysqliObject = new \mysqli($this->getDBHost(),$this->getDBUser(),$this->getDBPassword(),$this->getDBName());
			$this->setMysqliObject($mysqliObject);
			if($mysqliObject->connect_error) {
				$this->generateError(self::ERROR_CONNECT);
			}
			
		}
		
		public function quote($value) {
			// Handle special PHP values
			if ($value === null) {
				return 'NULL';
			} else if ($value === false) {
				return '0';
			} else if ($value === true) {
				return '1';
			}

			// Handle all other cases
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}

			$value = '"' . $this->getMysqliObject()->real_escape_string($value) . '"';

			return $value;
		}
		
		public function doQuery($sql) {
			$this->query = $sql;
			$result = $this->getMysqliObject()->query($sql);
			if($result) {
				
			}
			else {
				$this->generateError(self::ERROR_QUERY);
			}
			
			$resultObject = new MysqliDatabaseResult();
			$resultObject->setResult($result);
			
			return $resultObject;
		}
		
		public function getInsertID() {
			return $this->getMysqliObject()->insert_id;
		}
		
		public function getMySQLError() {
			return $this->getMysqliObject()->error;
		}
	}
?>