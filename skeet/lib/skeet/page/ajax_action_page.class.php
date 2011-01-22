<?
	class AjaxActionPage extends AbstractAjaxPage {
		protected $pageName = "AjaxAction";
		protected $doReturnJavascript = false;
		protected $requireLogin = false;
		
		public function getDoReturnJavascript() {
			return $this->doReturnJavascript;
		}
		public function __construct() {
			parent::__construct();
			
			switch($_REQUEST["action"]) {
								
			}
			if(!$this->ajaxReturn && getRequestValue("value")) {
				$this->ajaxReturn = getRequestValue("value");
			}
			elseif(!$this->ajaxReturn && isset($_REQUEST["value"])) {
				$this->ajaxReturn = "click to edit";
			}
			$this->ajaxReturn = trim(stripslashes($this->ajaxReturn));
		}
	}
?>