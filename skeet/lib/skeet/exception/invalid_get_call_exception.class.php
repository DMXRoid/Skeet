<?php
	namespace Skeet\Exception;
	
	class InvalidGetCallException extends AbstractException {
		protected bool $doEmail = true;
		
		public function __construct($message) {
			parent::__construct($message,1);
		}
	}
?>