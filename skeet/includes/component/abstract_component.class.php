<?
	namespace Skeet\Component;
	
	abstract class AbstractComponent {
		protected $fileName;
		protected $filePath = COMPONENT_INCLUDE_PATH;
		protected $pageObject;
		protected $settings = array();
		
		public function render() {
			$this->doRenderDynamic();
			echo $this->output;
		}

		public function getFilePath() {
			return $this->filePath;
		}

		public function getFileName() {
			return $this->fileName;
		}

		public function doRenderDynamic() {
			ob_start();
			include ($this->filePath . $this->fileName);

			$data = ob_get_contents();
			if (isset($this->stripSpaces)) {
				$data = preg_replace('/\s\s+/', ' ', $data);
				$data = str_replace('<!-- ', "<!--\n", $data);
			}
			$this->output = $data;
			ob_end_clean();
		}
		
		public function setFileName($fileName) {
			$this->fileName = $fileName;
		}
		
		public function setFilePath($filePath) {
			$this->filePath = $filePath;
		}

		public function getComponent() {
			return $this;
		}

		public function setPageObject($pageObject) {
			$this->pageObject = $pageObject;
		}

		public function getPage() {
			return $this->pageObject;
		}
		
		public function addSetting($key,$setting) {
			$this->settings[$key] = $setting;
			return $this;
		}
		
		public function getSetting($key) {
			if(isset($this->settings[$key])) {
				return $this->settings[$key];
			}
		}

	}
?>