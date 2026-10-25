<?php
	/**
	 * @package Skeet
	 * @subpackage Theme
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	declare(strict_types=1);

	namespace Skeet\Theme;

	class GeneratorTheme extends AbstractTheme {
		protected string $themeName = "Code Generator";

		public function __construct() {
			$this->themeDirectory = (string) \Skeet\Skeet::getConfig("application_path") . "lib/skeet/components/generator/";
		}
	}
