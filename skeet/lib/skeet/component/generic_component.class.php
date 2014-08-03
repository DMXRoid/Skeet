<?
	/**
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	namespace Skeet\Component;

	/**
	 * GenericComponent
	 * 
	 * A generic component fill set its file name and path
	 * based on both the current theme, and the label passed
	 * into the constructor.
	 * 
	 * @package Skeet
	 * @subpackage Component
	 */

	class GenericComponent extends AbstractComponent {

		public function __construct($componentLabel=NULL) {
			if(!is_null($componentLabel)) {
				$theme = \Skeet\ThemeFactory::getCurrentTheme();
				$this->filePath = $theme->getThemeDirectory();
				$fileName = substr_replace(preg_replace_callback("/([A-Z])/",
							function ($matches) {
								return strtolower("_" . $matches[0]);
							},
					  $componentLabel),'',0,1);
				$fileName .= '.comp.php';
				$this->fileName = $fileName;
			}
		}
	}
?>