<?
	/**
	 * @package Skeet
	 * @subpackage Page
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Page;

	/**
	 * AbstractPage is the core class for page-level logic, and is pretty much the first
	 * thing that gets done
	 *
	 * @abstract
	 */
	class AbstractPage {
		protected $fileName = "home.page.php";
		protected $filePath;
		protected $pageName;
		protected $pageTitle = "Apptive.com - Build an App, Connect With Your Customers";
		protected $subTitle = "";
		protected $keywords = array("meta","keywords","go","here");
		protected $metaDescription = "meta description goes here";
		protected $css = array(); // an array of CSS files, assumed to be in CSS_DIRECTORY
		protected $javaScriptToLoad = array(); // an array of javascript files, assumed to be in JS_DIRECTORY
		protected $requiresAdmin = FALSE;
		protected $robotsRules = array("INDEX","FOLLOW");  // spidering rules 
		protected $requireLogin = false; // should I require logins?
		
		/*
		
			request error checking 
		*/
		
		protected $badClass = "error";
		protected $errorMessages = array();
		protected $requestVarsToCheck = array();
		protected $checkTrigger = "do_submit";

		public function __construct() {
			$this->filePath = \Skeet\Skeet::getConfig("application_page_path");
			if($this->requireLogin) {
				$this->checkLogin();
			}
			
			if($this->checkTrigger && \Skeet\Util::getRequestValue($this->checkTrigger)) {
				$this->checkInput();
			}

			
		}
		
		protected function checkLogin() {
			$user = \Skeet\UserFactory::getCurrentUser();
			if(!$user->getID()) {
				\Skeet\LinkFactory::getLink("Login")->doRedirect();
			}
		}
			
		/*
			These functions provide for pretty easy dynamic error-message reporting.
			After checkInput() is called, $this->errorMessages will be an array of 
			error messages with the request key as the key.
		
		*/
		
		public function getBadClass() {
			return $this->badClass;
		}
		
		public function getErrorMessages() {
			return $this->errorMessages;
		}
		
		public function getErrorClass($key) {
			if(!is_array($key)) {
				if(isset($this->errorMessages[$key]) && $this->errorMessages[$key]) {
					return $this->badClass;
				}
			}
			else {
				$tempErrors = $this->errorMessages;
				foreach($key as $arrayKey) {
					if(isset($tempErrors[$arrayKey]) && $tempErrors[$arrayKey]) {
						$tempErrors = $tempErrors[$arrayKey];
					}
					else {
						return '';
					}
				}
				return $this->badClass;
			}
			return '';
		}

		/*
			this is really only here as an example.  Most of the time, the kind of checks
			you'll want to do on user input are more complex than simple type matching.  However,
			when you extend AbstractPage, you should implement a local checkInput(), do your custom
			error checking there, and just use the trigger in AbstractPage::__construct() to auto-fire off 
			your shit.			
		*/
		
		protected function checkInput() {
			$errorMessages = array();
			foreach($this->requestVarsToCheck as $requestKey => $checkType)   {
				switch($checkType) {
					case REQUEST_CHECK_TYPE_EXISTS:
						if(!requestExists($requestKey)) {
							$errorMessages[$requestKey] = "You must enter something for this field.";
						}
						break;
					
					case REQUEST_CHECK_TYPE_IS_NUMERIC:
						if(!requestExists($requestKey) || !is_numeric(getRequestValue($requestKey))) {
							$errorMessages[$requestKey] = "This must be a number";
						}
						
						break;
					
					case REQUEST_CHECK_TYPE_MIN_6_CHARACTERS:
						if(!requestExists($requestKey) || strlen(getRequestValue($requestkey)) < 6) {
							$errorMessages[$requestkey] = "This must be at least 6 characters long";
						}
						break;
				}
			}
			$this->errorMessages = $errorMessages;
		}
		

		public function getActiveMenu() {
			return $this->activeMenu;
		}
		
		public function getPageName() {

			return $this->pageName;
		}

		public function getRequiresAdmin() {
			return $this->requiresAdmin;
		}

		public function getFileName() {
			return $this->fileName;
		}

		public function getFilePath() {
			return $this->filePath;
		}

		public function getClassName() {
			return $this->className;
		}

		public function getPage() {
			return $this;
		}

		public function getSubTitle() {
			return $this->subTitle;
		}

		public function getPageTitle() {
			return $this->subTitle .  $this->pageTitle;
		}

		public function setPageTitle($pageTitle) {
			$this->pageTitle = $pageTitle;
		}

		public function getMetaDescription() {
			return $this->metaDescription;
		}

		public function getJavaScript() {
			$output = "";
			foreach(\Canvasser\ThemeFactory::getCurrentTheme()->getJavascriptToLoad() as $jsFile) {
				$output .= '<script language="javascript" src="' .  \Canvasser\ThemeFactory::getCurrentTheme()->getJavascriptURL() .  $jsFile . '" type="text/javascript"></script>' . "\n";
			}
			return $output;
		}

		public function getCSS() {
			$output = "";
			foreach(\Canvasser\ThemeFactory::getCurrentTheme()->getCSSToLoad() as $css) {
				$output .= '<link rel="stylesheet" type="text/css" href="' . \Canvasser\ThemeFactory::getCurrentTheme()->getCSSURL()  . $css . '">' . "\n";
			}
			return $output;
		}
		public function getKeywords() {
				return $this->keywords;
		}
		
		public function getRobotsRules() {
			return $this->robotsRules;
		}
		public function render() {
			include ($this->filePath . $this->fileName);
		}
		
		public function getPageLink() {
			$pageLink = \Skeet\LinkFactory::getLink($this->getPageName());
			foreach($_GET as $key => $value) {
				$pageLink->addLinkArg($key,$value);
			}
			return $pageLink;
		}
	}
?>