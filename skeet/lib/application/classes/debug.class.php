<?
	/*
		debug.class.php
		
		Debuging framework.  Pretty basic tests, load time, where the test started,
		memory usage, __autoload calls, database statistics.
	*/

	class Debug {
		public static $performanceLogs = array();
		public static $logStarted = false;
		
		public static $activeLogLabel;
		
		public static $autoLoads = array();
		
		
		
		public static function startPerformanceLog($label="") {
			$backTraceArray = debug_backtrace();
			$fileName = $backTraceArray[0]["file"];
			$lineNumber = $backTraceArray[0]["line"];
			$logArray = array();
			$logArray["start_time"] = microtime(true);
			$logArray["start_file_name"] = $fileName;
			$logArray["start_line_number"] = $lineNumber;
			$logArray["start_memory_usage"] = memory_get_usage(true);
			$logArray["auto_load_calls"] = array();
			$logArray["is_active"] = true;
			if($label) {
				$label = $label;
			}
			else {
				/*
					This could cause a collision if you start up two logs on the exact
					same line of code.  So, don't do that.  You should be using line breaks
					after every ; anyway.  Bad coder, BAD CODER!!!!
				*/
				$label = sha1($fileName . $lineNumber);
			}
			self::$performanceLogs[$label] = $logArray;
			self::$activeLogLabel = $label;
		}
		
		public static function jam($toJam,$doDie=true) {
			echo '<pre>';
			print_r($toJam);
			echo '</pre>';
			if($doDie) {
				die();
			}
		}
		
		public static function endPerformanceLog($label="") { 
			if(!$label) {
				$label = self::$activeLogLabel;
			}
			
			$logArray = self::$performanceLogs[$label];
			
			if($logArray["is_active"]) {
			
				$backTraceArray = debug_backtrace();
				$fileName = $backTraceArray[0]["file"];
				$lineNumber = $backTraceArray[0]["line"];
				$logArray["end_time"] = microtime(true);
				$logArray["final_time"] = $logArray["end_time"] - $logArray["start_time"];
				$logArray["peak_memory"] = memory_get_peak_usage(true);
				$logArray["end_file_name"] = $fileName;
				$logArray["end_line_number"] = $lineNumber;
				$logArray["end_memory_usage"] = memory_get_usage(true);
				$logArray["is_active"] = false;
				
				self::$performanceLogs[$label] = $logArray;
			}
			
			
		}
		
		/*
			Log a call to __autoload in both the active log and the overall log.  Faster than 
			foreaching.
		*/
		
		public static function addAutoLoadCall($className) {
			self::$performanceLogs[self::$activeLogLabel]["auto_load_calls"][] = $className;
			self::$autoLoads[] = $className;
		}
		
		/*
			
		*/
		
		public static function endAllPerformanceLogs() {
			foreach(self::$performanceLogs as $label => $logArray) {
				if($logArray["is_active"]) {
					self::endPerformanceLog($label);
				}
			}
		}
		
		public static function displayDebugData() {
			self::jam(self::$performanceLogs);
		}
	}
?>