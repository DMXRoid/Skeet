<?
	if($this->getAjaxReturn() !== '') {
		if($this->getDoReturnJavascript()) {
			header("Content-type: text/javascript");
		}
		echo $this->getAjaxReturn();
	}
?>