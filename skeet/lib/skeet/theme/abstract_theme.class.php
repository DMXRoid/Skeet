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

	/**
	 * Base theme: holds the CSS/JS a theme loads, their base URLs, and the
	 * directory its components live in.
	 *
	 * @abstract
	 */
	abstract class AbstractTheme {

		protected string $themeName = '';
		protected string $themeDirectory = '';
		protected string $cssURL = '';
		protected string $javascriptURL = '';

		/** @var string[] CSS files (filename, or path+filename under $cssURL) */
		protected array $cssToLoad = [];

		/** @var string[] JS files (filename, or path+filename under $javascriptURL) */
		protected array $javascriptToLoad = [];

		public function getThemeName(): string {
			return $this->themeName;
		}

		public function getThemeDirectory(): string {
			return $this->themeDirectory;
		}

		/** @return string[] */
		public function getCSSToLoad(): array {
			return $this->cssToLoad;
		}

		public function getCSSURL(): string {
			return $this->cssURL;
		}

		public function getJavascriptURL(): string {
			return $this->javascriptURL;
		}

		/** @return string[] */
		public function getJavascriptToLoad(): array {
			return $this->javascriptToLoad;
		}

		/**
		 * Render <link> tags for each CSS file. Override and call
		 * parent::renderCSS() to append custom tags.
		 */
		public function renderCSS(): string {
			$output = '';
			foreach ($this->getCSSToLoad() as $cssFile) {
				$output .= '<link rel="stylesheet" href="' . $this->getCSSURL() . $cssFile . '">' . "\n";
			}
			return $output;
		}

		/**
		 * Render <script> tags for each JS file. Override and call
		 * parent::renderJavascript() to append custom tags.
		 */
		public function renderJavascript(): string {
			$output = '';
			foreach ($this->getJavascriptToLoad() as $javascriptFile) {
				$output .= '<script src="' . $this->getJavascriptURL() . $javascriptFile . '"></script>' . "\n";
			}
			return $output;
		}
	}
