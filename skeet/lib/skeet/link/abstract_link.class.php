<?
	/**
	* @package Skeet
	* @subpackage Link
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Link;

	/**
	 * AbstractLink.
	 *
	 * @abstract
	 */
	abstract class AbstractLink {

		/**
		 * The value of {@link PageFactory::$pageKey} to pass if there's
		 * no redirect pattern or file name set.
		 *
		 *	@access protected
		 * @var string
		 * @see __construct()
		 */

		protected $pageName;

		/**
		 *	A set of arbitrary key/value pairs to
		 * pass to the get string in a link, or as hidden
		 * inputs in a form.
		 *
		 * @access protected
		 * @var array
		 * @see addLinkArg()
		 * @see getLink()
		 * @see getLinkAsForm()
		 */

		
		protected $linkArgs = array();

		/**
		 *	The file name the link should point to, 
		 * relative to your webroot.
		 *
		 * @access protected
		 * @var string
		 * @see getLink()
		 * @see getLinkAsForm()
		 */


		protected $fileName;

		/**
		 *	The HTML id of the most recent form created
		 * by {@link getLinkAsForm()}, or the ID manually
		 * set.
		 *
		 * @access protected
		 * @var string
		 * @see getLinkAsForm()
		 * @see getFormID()
		 * @see setFormID()
		 */

		protected $formID;

		/**
		 *	Whether or not to force links to point to 
		 * https instead of http, for example a checkout page
		 * or account info or what have you.
		 *
		 * @access protected
		 * @var boolean
		 * @see getLink()
		 * @see getLinkAsForm()
		 */

		protected $forceSecure = false;

		public function __construct($pageName) {
			$this->pageName = $pageName;

			if(preg_match("/[A-Z]/",$pageName)) {
         	$fileName = substr_replace(preg_replace("/([A-Z])/e",'strtolower("_\\1")',$pageName),'',0,1);
         	$fileArray = explode("_",$fileName);
			}
			else {
				$fileArray = explode(" ", $pageName);
			}

         if(file_exists(strtolower($pageName) . ".html")) {
            $this->fileName = strtolower($pageName) . ".html";
         }
         else {
            $this->fileName = implode("/",$fileArray);
         }
			$this->pageName = $pageName;

		}

		/**
		 *	Get the page name for this link
		 * 
		 * @access public
		 * @return string
		 * @see $pageName
		 */

		public function getPageName() {
			return $this->pageName;
		}

		/**
		 *	Return the file name for this link
		 *
		 * @access public
		 * @return string
		 * @see $fileName
		 */

		public function getFileName() {
			return $this->fileName;
		}

		/**
		 * Automatically send a Location: header to the browser,
		 * redirecting the client to the location produced by
		 * {@link getLink()}.  Useful after a form submission.
		 *
		 * @access public
		 * @see getLink()
		 */
		
		public function doRedirect() {
			$url = $this->getLink();
			header("Location: " . $url);
			die();
		}

		public function setFileName($fileName) {
			$this->fileName = $fileName;
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

		public function getLinkArgs() {
			return $this->linkArgs;
		}
		
		public function addLinkArg($key,$value) {
			$this->linkArgs[$key] = $value;
			return $this;
		}

		public function processLinkArgsIntoURL() {
			return (($this->getFileName()) ? '?' : '&') . http_build_query($this->getLinkArgs());
		}
	}
?>