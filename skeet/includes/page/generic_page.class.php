<?
	namespace Skeet\Page;
	class GenericPage extends AbstractPage {
		protected $pageName;
		
		public function __construct($pageName) {
			$this->pageName = $pageName;
			parent::__construct();
		}
	}
?>