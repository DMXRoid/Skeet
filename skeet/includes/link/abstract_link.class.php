<?
	namespace Skeet\Link;
	abstract class AbstractLink {
		protected $pageName;
		protected $linkArgs = array();
		protected $fileName;
		protected $formID;
		protected $seedLink = NULL;
		protected $forceSecure = FALSE;
		protected $dir;

		
		
		public function getPageName() {
			return $this->pageName;
		}

		public function getLinkName() {
			return $this->linkName;
		}

		public function getLinkArgs() {
			return $this->linkArgs;
		}

		public function doRedirect() {
			$url = $this->getLink();

			header("Location: " . $url);
			die();
		}

		public function setFileName($fileName) {
			$this->fileName = $fileName;
		}

		public function getFileName() {
			return $this->fileName;
		}

		public function getLink() {
			if(strstr($this->getPageName(),"Admin")) {
				$this->dir = '/admin/';
			}
			$page = $this->getPageName();
			if(!$this->getFileName() && !($this instanceof HomeLink)) {
				$pageString = $this->dir . INDEX_PAGE . "?page=" . $page;
			}
			else {
				$pageString = $this->dir  . '/' .  $this->getFileName();
			}

			$argString = $this->processLinkArgsIntoURL();

			$url = $pageString . $argString;

			return $url;

		}

		public function getLinkAsForm($overrideURL=NULL,$forUpload=NULL,$method="post") {

			if($overrideURL) {
				$page = $overrideURL;
			}
			else {
				$page = $this->getPageName();
			}

			if($this->fileName) {
				$actionPage = '/' . $this->dir . $this->fileName;
			}
			else {
				$actionPage = '/' . $this->dir . INDEX_PAGE . "?page=" . $page;
			}

			if(!$this->formID) {
				$formID = 1;
				while(is_numeric(substr($formID,0,1))) {
					$formID = md5(strtotime("now") . mt_rand(1,10000000));
					$formID = substr_replace($formID,'',6);
				}
				$this->formID = $formID;
			}

			if($forUpload) {
				$encType = ' enctype="multipart/form-data" ';
			}
			else {
				$encType = "";
			}

			$linkArgs = $this->getLinkArgs();
			$formOutput = '<form id="' . $this->formID . '" action="' . $actionPage . '" method="' . $method . '" style="display: inline;" ' . $encType . '>';



			foreach($linkArgs as $key => $value) {
				if($value) {
					$formOutput .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
				}
			}
			return $formOutput;
		}

		public function setFormID($formID) {
			$this->formID = $formID;
		}

		public function getFormId() {
			return $this->formID;
		}

		public function addLinkArg($key,$value) {
			$this->linkArgs[$key] = $value;
			return $this;
		}

		public function processLinkArgsIntoURL() {

			$linkArgs = $this->getLinkArgs();
			$argString = "";
			$x=0;
			foreach($linkArgs as $key =>$value) {

				if($value && $value != "") {
					if($this->fileName && $x == 0 || ($this instanceof HomeLink && $x == 0)) {
						$urlChar = "?";
					}
					else {
						$urlChar = "&";
					}
 $argString .= $urlChar . $key . "=" . $value;
					$x++;
				}
			}

			return $argString;

		}
	}
?>