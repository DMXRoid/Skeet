<?
/**
 * @package Skeet
 * @subpackage Component
 * @version 1.0
 * @author Matthew Schiros <schiros@invisihosting.com>
 * @copyright Copyright (c) 2011, Matthew Schiros
 */

	namespace Skeet\Component;

	/**
	 * AbstractComponent is the core clas for code that gets displayed
	 * (either to the browser or the CLI, whatever).  Doesn't really do much,
	 * except give you a way to pass things to the display code.  The only real
	 * tricky bit is including the component file within the {@link render()}
	 * method, which gives the code inside the file access to the component object,
	 * and therefore the page, etc...
	 *
	 * @abstract
	 */

	abstract class AbstractComponent {
		
		/**
		 * The name of the file to render.
		 * 
		 * @access protected
		 * @var string
		 * @see getFileName()
		 * @see setFileName()
		 */
		
		protected $fileName;

		/**
		 * The path of the file to render, ends in / .
		 *
		 * @access protected
		 * @var string
		 * @see getFilePath()
		 * @see setFilePath()
		 */
		
		protected $filePath = COMPONENT_INCLUDE_PATH;

		/**
		 * The {@link AbstractPage} that the component is being rendered
		 * on.  Useful b/c it lets you access page level things easily from
		 * the component.  Should probably be replaced by calls to {@link PageFactory::getCurrentPage()},
		 * but here for now b/c it always has been.
		 *
		 * @var AbstractPage
		 * @access protected
		 * @see getPage()
		 * @see setPageObject()
		 */

		protected $pageObject;

		/**
		 *	Stores values passed to the component for use in the display code.
		 * No restrictions on what it can store, just put anything you need to
		 * access within the scope of the display code here, and then get it back
		 * out with {@link getSetting()}.
		 *
		 * @var array
		 * @access protected
		 * @see getSetting()
		 * @see addSetting()
		 */

		protected $settings = array();


		/**
		 *	Returns {@link $filePath}, the directory to look for {@link $fileName}
		 * in.
		 *
		 * @access public
		 * @return string
		 * @see $filePath
		 * @see setFilePath()
		 */

		public function getFilePath() {
			return $this->filePath;
		}

		/**
		 * Returns {@link $fileName}, the file name to include in {@link render()}
		 *
		 * @access public
		 * @return string
		 * @see $fileName
		 * @see setFileName()
		 */

		public function getFileName() {
			return $this->fileName;
		}

		/**
		 * Returns the {@link AbstractPage} the component is being rendered within.
		 *
		 * @access public
		 * @return AbstractPage
		 * @see $pageObject
		 * @see setPageObject()
		 */

		public function getPage() {
			return $this->pageObject;
		}

		/**
		 * Get a setting passed to the component before {@link render()}.  Returns
		 * false if the setting doesn't exist.
		 *
		 * @access public
		 * @param mixed $key
		 * @return mixed
		 * @see addSetting()
		 * @see $settings
		 */

		public function getSetting($key) {
			if(isset($this->settings[$key])) {
				return $this->settings[$key];
			}
			return false;
		}

		/**
		 * Set the {@link $fileName} to render
		 *
		 * @access public
		 * @param string $fileName
		 * @see $fileName
		 * @see getFileName()
		 */

		public function setFileName($fileName) {
			$this->fileName = $fileName;
		}

		/**
		 * Set the {@link $filePath} to look for {@link $fileName} in.
		 *
		 * @access public
		 * @param string $filePath
		 * @see $filePath
		 * @see getFilePath()
		 */

		public function setFilePath($filePath) {
			$this->filePath = $filePath;
		}


		/**
		 * Set the {@link $pageObject}
		 *
		 * @access public
		 * @param AbstractPage $pageObject
		 * @see getPage()
		 * @see $pageObject
		 */

		public function setPageObject($pageObject) {
			$this->pageObject = $pageObject;
		}

		/**
		 * Add an arbitrary setting to the component for access
		 * by the display code.  Returns $this, so you can chain
		 * setting calls before {@link render() rendering}, eg:
		 * {@link ComponentFactory::getComponent()}->addSetting("foo",1)->addSetting("bar",2)->render();
		 *
		 * @access public
		 * @param mixed $key
		 * @param mixed $setting
		 * @return AbstractComponent
		 * @see $settings
		 * @see getSetting()
		 */

		public function addSetting($key,$setting) {
			$this->settings[$key] = $setting;
			return $this;
		}

		/**
		 * Actually render the display code.  Includes {@link $fileName}
		 * within the function, so it has access to the component and all
		 * its methods.  Uses output buffering to do some post-include
		 * processing, like cleaning up whitespace.  Throws a
		 * {@link ComponentFileNotFoundException} if the file doesn't exist.
		 *
		 * @access public
		 * @see getFileName()
		 * @see getFilePath()
		 * @see ComponentFileNotFoundException
		 */

		public function render() {
			if(file_exists($this->getFilePath() . $this->getFileName())) {
				ob_start();
				require($this->filePath . $this->fileName);
				$data = ob_get_contents();
				if (isset($this->stripSpaces)) {
					$data = preg_replace('/\s\s+/', ' ', $data);
					$data = str_replace('<!-- ', "<!--\n", $data);
				}
				$this->output = $data;
				ob_end_clean();
			}
			else {
				throw new ComponentFileNotFoundException($this->getFilePath() . $this->getFileName());
			}
		}
	}
?>