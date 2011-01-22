<?
	class ExceptionComponent extends AbstractComponent {
		protected $fileName = "exception.comp.php";
		protected $exception;
		
		public function setException($exception) {
			$this->exception = $exception;
		}
		
		public function getException() {
			return $this->exception;
		}
	}
?>