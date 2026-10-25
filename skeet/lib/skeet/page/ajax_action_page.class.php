<?php
	declare(strict_types=1);

	namespace Skeet\Page;

	class AjaxActionPage extends AbstractAjaxPage {
		protected string $pageName = "AjaxAction";
		protected bool $doReturnJavascript = false;
		protected bool $requireLogin = false;

		public function getDoReturnJavascript(): bool {
			return $this->doReturnJavascript;
		}

		public function __construct() {
			parent::__construct();

			switch ($_REQUEST["action"] ?? null) {
				// application AJAX actions are handled in app subclasses
			}

			if (!$this->ajaxReturn && \Skeet\Util::getRequestValue("value")) {
				$this->ajaxReturn = \Skeet\Util::getRequestValue("value");
			}
			elseif (!$this->ajaxReturn && isset($_REQUEST["value"])) {
				$this->ajaxReturn = "click to edit";
			}

			$this->ajaxReturn = trim(stripslashes((string) $this->ajaxReturn));
		}
	}
