<?php
	declare(strict_types=1);

	namespace Skeet\Theme;

	class GenericTheme extends AbstractTheme {
		protected string $themeName = "Generic";
		protected string $cssURL = "/css/";

		/** @var string[] */
		protected array $cssToLoad = ["style.css", "jquery-ui-1.8.6.custom.css"];

		protected string $javascriptURL = "/js/";

		/** @var string[] */
		protected array $javascriptToLoad = [
			"wireit/src/loader.js",
			"jquery-1.7.2.min.js",
			"jquery-ui-1.8.6.custom.min.js",
			"jquery.jeditable.mini.js",
			"site_scripts.js",
		];

		public function __construct() {
			$this->themeDirectory = (string) \Skeet\Skeet::getConfig("application_lib_path") . "components/";
		}
	}
