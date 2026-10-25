<?php
	/**
	 * @package Skeet
	 * @subpackage Link
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	declare(strict_types=1);

	namespace Skeet\Link;

	/**
	 * Builds URLs (and link-as-form markup) for a page plus a set of args.
	 *
	 * @abstract
	 */
	abstract class AbstractLink {

		protected string $pageName = '';

		/** @var array<string,mixed> arbitrary key/value pairs for the query string / hidden inputs */
		protected array $linkArgs = [];

		protected string $fileName = '';
		protected string $formID = '';
		protected bool $forceSecure = false;
		protected string $dir = '';

		public function __construct(string $pageName) {
			$this->pageName = $pageName;

			if (preg_match("/[A-Z]/", $pageName)) {
				$fileName = substr_replace(preg_replace_callback(
					"/([A-Z])/",
					static fn($matches) => strtolower("_" . $matches[0]),
					$pageName
				), '', 0, 1);
				$fileArray = explode("_", $fileName);
			}
			else {
				$fileArray = explode(" ", $pageName);
			}

			$this->fileName = file_exists(strtolower($pageName) . ".html")
				? strtolower($pageName) . ".html"
				: implode("/", $fileArray);
		}

		public function getPageName(): string {
			return $this->pageName;
		}

		public function getFileName(): string {
			return $this->fileName;
		}

		/**
		 * Send a Location: header redirecting to getLink(). Useful after a form
		 * submission.
		 */
		public function doRedirect(): never {
			header("Location: " . $this->getLink());
			die();
		}

		public function setFileName(string $fileName): void {
			$this->fileName = $fileName;
		}

		public function getLink(): string {
			if (strstr($this->getPageName(), "Admin")) {
				$this->dir = '/admin/';
			}
			$page = $this->getPageName();
			if (!$this->getFileName() && !($this instanceof HomeLink)) {
				$pageString = $this->dir . INDEX_PAGE . "?page=" . $page;
			}
			else {
				$pageString = $this->dir . '/' . $this->getFileName();
			}
			return $pageString . $this->processLinkArgsIntoURL();
		}

		public function getLinkAsForm(?string $overrideURL = null, bool $forUpload = false, string $method = "post", ?string $extras = null): string {
			$page = $overrideURL ?: $this->getPageName();

			$actionPage = $this->fileName
				? '/' . $this->dir . $this->fileName
				: '/' . $this->dir . INDEX_PAGE . "?page=" . $page;

			if (!$this->formID) {
				$formID = '1';
				while (is_numeric(substr($formID, 0, 1))) {
					$formID = substr(md5(uniqid('', true)), 0, 6);
				}
				$this->formID = $formID;
			}

			$encType = $forUpload ? ' enctype="multipart/form-data" ' : '';

			$formOutput = '<form ' . $extras . ' id="' . $this->formID . '" action="' . $actionPage
				. '" method="' . $method . '" style="display: inline;" ' . $encType . '>';

			foreach ($this->getLinkArgs() as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $v2 => $k2) {
						if ($v2) {
							$formOutput .= '<input type="hidden" name="' . $k2 . '" value="' . $v2 . '">';
						}
					}
				}
				elseif ($value) {
					$formOutput .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
				}
			}
			return $formOutput;
		}

		public function setFormID(string $formID): void {
			$this->formID = $formID;
		}

		public function getFormId(): string {
			return $this->formID;
		}

		/** @return array<string,mixed> */
		public function getLinkArgs(): array {
			return $this->linkArgs;
		}

		public function addLinkArg(string $key, mixed $value): static {
			$this->linkArgs[$key] = $value;
			return $this;
		}

		public function processLinkArgsIntoURL(): string {
			return (($this->getFileName()) ? '?' : '&') . http_build_query($this->getLinkArgs());
		}

		public function removeLinkArg(string $key): static {
			unset($this->linkArgs[$key]);
			return $this;
		}
	}
