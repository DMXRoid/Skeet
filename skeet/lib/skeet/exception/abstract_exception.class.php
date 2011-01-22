<?
	/*
		abstract_exception.class.php
		
		Don't let the naming fool you.  Exception is the base PHP Exception class,
		AbstractException extends that to allow for all the methods and properties
		that it has while being able to provide a central point of customization for 
		all the other exceptions in the framework.
		
		All exceptions should extend AbstractException.
	*/

	namespace Skeet\Exception;
	
	abstract class AbstractException extends \Exception {
		/*
			normally, we don't care about being emailed exceptions,
			we'll just deal with them in the code.  However, for things 
			like Database exceptions, where there may be debugging information
			that we want to see, it can be useful to email the developer(s) when
			some shit goes down.  
		*/
		
		protected $doEmail = false;
		protected $emailList = array("schiros@invisihosting.com"); 
		
		public function __construct($message,$code) {
			parent::__construct($message,$code);
		}
		
		public function processException() {
			if($this->doEmail) {
				$subject = "Exception thrown on " . DOMAIN;
				$to = implode(",",$this->emailList);
				
				$text = "An exception was thrown.  
Exception message: " . $this->message . "
Trace below:\n" . $this->getTraceAsString();
				mail($to,$subject,$text);
			}
			
			if(DEBUG) {
				$this->renderException();
			}
		}
		
		public function renderException() {
			$exceptionComponent = ComponentFactory::getComponent("exception",PageFactory::getCurrentPage());
			$exceptionComponent->setException($this);
			$exceptionComponent->render();
		}
	}
?>