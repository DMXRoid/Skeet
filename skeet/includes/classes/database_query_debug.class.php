<?
	/*
		database_query_debug.class.php
		
		Wrapper for useful data about a database query.
	*/
	class DatabaseQueryDebug {
		protected $databaseName;
		protected $query;
		protected $queryType;
		protected $startTime;
		protected $endTime;
		protected $numRows;
		
		public function setDatabaseName($databaseName) {
			$this->databaseName = $databaseName;
		}
		
		public function setQuery($query) {
			$this->query = $query;
		}
		
		public function setQueryType($queryType) {
			$this->queryType = $queryType;
		}
		
		public function setStartTime($startTime) {
			$this->startTime = $startTime;
		}
		
		public function setEndTime($endTime) {
			$this->endTime = $endTime;
		}
		
		public function setNumRows($numRows) {
			$this->numRows = $numRows;
		}
		
		public function getDatabaseName() {
			return $this->databaseName;
		}
		
		public function getQuery() {
			return $this->query;
		}
		
		public function getQueryType() {
			return $this->queryType;
		}
		
		public function getStartTime() {
			return $this->startTime;
		}
		
		public function getEndTime() {
			return $this->endTime;
		}
		
		public function getTotalTime() {
			return $this->getEndTime() - $this->getStartTime();
		}
		
	}
?>