<?php
	/**
	 * @package Skeet
	 * @subpackage Theme
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Theme;

	class SkelTheme extends AbstractTheme {
		protected $themeName = "Application Skeleton Files";
		
		public function __construct() {
			$this->themeDirectory = \Skeet\Skeet::getConfig("application_path") . "lib/skeet/components/skel/";
		}
	}
?>
