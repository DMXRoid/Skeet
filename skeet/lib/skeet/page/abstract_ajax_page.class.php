<?
	namespace Skeet\Page;
	class AbstractAjaxPage extends AbstractPage {
		protected $fileName = "ajax.page.php";
		protected $ajaxReturn;

		public function getAjaxReturn() {
			return $this->ajaxReturn;
		}
	}
?>