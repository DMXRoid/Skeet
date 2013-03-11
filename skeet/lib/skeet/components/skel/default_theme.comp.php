<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Theme;
	
	class DefaultTheme extends \Skeet\Theme\AbstractTheme {
		protected $themeName = "Default";
		
		public function __construct() {
			$this->themeDirectory = \Skeet\Skeet::getConfig("application_lib_path") . "components/";
		}
	}
	
?>