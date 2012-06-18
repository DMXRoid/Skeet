<?php
	/**
	 * @package Skeet
	 * @subpackage Theme
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Theme;

	class GeneratorTheme extends AbstractTheme {
		protected $themeName = "Code Generator";
		
		public function __construct() {
			$this->themeDirectory = \Skeet\Skeet::getConfig("application_path") . "lib/skeet/components/generator/";
		}
	}
?>
