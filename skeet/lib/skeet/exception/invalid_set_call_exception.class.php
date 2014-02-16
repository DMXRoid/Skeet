<?
	namespace Skeet\Exception;
	
	class InvalidSetCallException extends AbstractException {
		protected $doEmail = true;
		
		public function __construct($message) {
			parent::__construct($message,1);
		}
	}
?>