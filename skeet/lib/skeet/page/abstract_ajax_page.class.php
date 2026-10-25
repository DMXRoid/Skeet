<?php
	declare(strict_types=1);

	namespace Skeet\Page;

	class AbstractAjaxPage extends AbstractPage {
		protected string $fileName = "ajax.page.php";
		protected mixed $ajaxReturn = null;

		public function getAjaxReturn(): mixed {
			return $this->ajaxReturn;
		}
	}
