<?
	/**
	 * @package Skeet
	 * @subpackage Component
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Component;

	/**
	 * GenericComponent
	 */

	class GenericComponent extends AbstractComponent {
		public function __construct($componentLabel=NULL) {
			if(!is_null($componentLabel)) {
				$theme = \Skeet\Factory\ThemeFactory::getCurrentTheme();
				$this->filePath = \Skeet\Skeet::getConfig("application_path") . "lib/skeet/components/" . $theme->getThemeDirectory();
				$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$componentLabel),'',0,1);
				$fileName .= '.comp.php';
				$this->fileName = $fileName;
			}
		}
	}
?>