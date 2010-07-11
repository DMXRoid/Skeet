<?
	namespace Skeet\Component;
	class GenericComponent extends AbstractComponent {
		protected $fileName;
		
		public function __construct($componentLabel=NULL) {
			if(!is_null($componentLabel)) {
				$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$componentLabel),'',0,1);
				$fileName .= '.comp.php';
				$this->fileName = $fileName;
			}
		}
	}
?>