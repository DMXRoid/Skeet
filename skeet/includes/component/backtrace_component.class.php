<?
	class BacktraceComponent extends AbstractComponent {
		protected $fileName = "backtrace.comp.php";
		protected $backtrace;
		
		public function setBacktrace($backtrace) {
			$this->backtrace = $backtrace;
		}
		
		public function getBacktrace() {
			return $this->backtrace;
		}
	}
?>