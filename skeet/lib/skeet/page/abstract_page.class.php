<?php
	/**
	 * @package Skeet
	 * @subpackage Page
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	declare(strict_types=1);

	namespace Skeet\Page;

	/**
	 * AbstractPage is the core class for page-level logic — the first thing that
	 * runs for a request.
	 */
	class AbstractPage {
		protected string $fileName = "home.page.php";
		protected string $filePath = "";
		protected string $pageName = "";
		protected string $pageTitle = "";
		protected string $subTitle = "";

		/** @var string[] */
		protected array $keywords = [];

		protected string $metaDescription = "";

		/** @var string[] CSS files, assumed to live under CSS_DIRECTORY */
		protected array $css = [];

		/** @var string[] JS files, assumed to live under JS_DIRECTORY */
		protected array $javaScriptToLoad = [];

		protected bool $requiresAdmin = false;

		/** @var string[] spidering rules */
		protected array $robotsRules = ["INDEX", "FOLLOW"];

		protected bool $requireLogin = false;
		protected ?string $activeMenu = null;
		protected string $className = "";

		/* request error checking */
		protected string $badClass = "error";

		/** @var array<string,mixed> */
		protected array $errorMessages = [];

		/** @var array<string,int> request key => REQUEST_CHECK_TYPE_* */
		protected array $requestVarsToCheck = [];

		protected string $checkTrigger = "do_submit";

		public function __construct() {
			$this->filePath = (string) \Skeet\Skeet::getConfig("application_page_path");
			if ($this->requireLogin) {
				$this->checkLogin();
			}
			if ($this->checkTrigger && \Skeet\Util::getRequestValue($this->checkTrigger)) {
				$this->checkInput();
			}
		}

		protected function checkLogin(): void {
			$user = \Skeet\UserFactory::getCurrentUser();
			if (!$user->getID()) {
				\Skeet\LinkFactory::getLink("Login")->doRedirect();
			}
		}

		/*
			After checkInput() runs, $this->errorMessages is keyed by request key,
			which these helpers use for dynamic error-message reporting.
		*/

		public function getBadClass(): string {
			return $this->badClass;
		}

		/** @return array<string,mixed> */
		public function getErrorMessages(): array {
			return $this->errorMessages;
		}

		/**
		 * @param string|array<int,string> $key
		 */
		public function getErrorClass(string|array $key): string {
			if (!is_array($key)) {
				return !empty($this->errorMessages[$key]) ? $this->badClass : '';
			}
			$tempErrors = $this->errorMessages;
			foreach ($key as $arrayKey) {
				if (!empty($tempErrors[$arrayKey])) {
					$tempErrors = $tempErrors[$arrayKey];
				}
				else {
					return '';
				}
			}
			return $this->badClass;
		}

		/*
			A simple example. Usually you'll implement a local checkInput() in your
			page subclass and let the $checkTrigger in __construct() fire it.
		*/
		protected function checkInput(): void {
			$errorMessages = [];
			foreach ($this->requestVarsToCheck as $requestKey => $checkType) {
				switch ($checkType) {
					case REQUEST_CHECK_TYPE_EXISTS:
						if (!\Skeet\Util::requestExists($requestKey)) {
							$errorMessages[$requestKey] = "You must enter something for this field.";
						}
						break;

					case REQUEST_CHECK_TYPE_IS_NUMERIC:
						if (!\Skeet\Util::requestExists($requestKey) || !is_numeric(\Skeet\Util::getRequestValue($requestKey))) {
							$errorMessages[$requestKey] = "This must be a number";
						}
						break;

					case REQUEST_CHECK_TYPE_MIN_6_CHARACTERS:
						if (!\Skeet\Util::requestExists($requestKey) || strlen((string) \Skeet\Util::getRequestValue($requestKey)) < 6) {
							$errorMessages[$requestKey] = "This must be at least 6 characters long";
						}
						break;
				}
			}
			$this->errorMessages = $errorMessages;
		}

		public function getActiveMenu(): ?string {
			return $this->activeMenu;
		}

		public function getPageName(): string {
			return $this->pageName;
		}

		public function getRequiresAdmin(): bool {
			return $this->requiresAdmin;
		}

		public function getFileName(): string {
			return $this->fileName;
		}

		public function getFilePath(): string {
			return $this->filePath;
		}

		public function getClassName(): string {
			return $this->className;
		}

		public function getPage(): static {
			return $this;
		}

		public function getSubTitle(): string {
			return $this->subTitle;
		}

		public function getPageTitle(): string {
			return $this->subTitle . $this->pageTitle;
		}

		public function setPageTitle(string $pageTitle): void {
			$this->pageTitle = $pageTitle;
		}

		public function getMetaDescription(): string {
			return $this->metaDescription;
		}

		public function getJavaScript(): string {
			$output = "";
			$theme = \Skeet\ThemeFactory::getCurrentTheme();
			foreach ($theme->getJavascriptToLoad() as $jsFile) {
				$output .= '<script src="' . $theme->getJavascriptURL() . $jsFile . '"></script>' . "\n";
			}
			return $output;
		}

		public function getCSS(): string {
			$output = "";
			$theme = \Skeet\ThemeFactory::getCurrentTheme();
			foreach ($theme->getCSSToLoad() as $cssFile) {
				$output .= '<link rel="stylesheet" href="' . $theme->getCSSURL() . $cssFile . '">' . "\n";
			}
			return $output;
		}

		/** @return string[] */
		public function getKeywords(): array {
			return $this->keywords;
		}

		/** @return string[] */
		public function getRobotsRules(): array {
			return $this->robotsRules;
		}

		public function render(): void {
			include($this->filePath . $this->fileName);
		}

		public function getPageLink(): mixed {
			$pageLink = \Skeet\LinkFactory::getLink($this->getPageName());
			foreach ($_GET as $key => $value) {
				$pageLink->addLinkArg($key, $value);
			}
			return $pageLink;
		}
	}
