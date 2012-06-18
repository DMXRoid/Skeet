<?
	namespace Skeet\Theme;
	
	class GenericTheme extends AbstractTheme {
		protected $themeName = "Generic";
		protected $cssURL = "/css/";
		protected $cssToLoad = array("style.css","jquery-ui-1.8.6.custom.css");
		protected $javascriptURL = "/js/";
		protected $javascriptToLoad = array(
														"wireit/src/loader.js",
														"jquery-1.7.2.min.js",
														"jquery-ui-1.8.6.custom.min.js",
														"jquery.jeditable.mini.js",
														"site_scripts.js"
											 );
		
		public function __construct() {
			$this->themeDirectory = \Skeet\Skeet::getConfig("application_lib_path") . "components/";
		}
		
	}
	
?>