<?
	namespace Skeet\Exception;
	
	class PageNotFoundException extends \Skeet\Exception\AbstractException {
		public function __construct() {
			parent::__construct('Page Not Found','404');
		}
		
		
		public function processException() {
			header("Location: /404.html");
		}
	}
?>