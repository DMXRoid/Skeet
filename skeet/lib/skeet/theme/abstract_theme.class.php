<?
	/**
	 * @package Skeet
	 * @subpackage Theme
	 * @version 1.0
	 * @author Matthew Schiros <schiros@invisihosting.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	namespace Skeet\Theme;

	/**
	 * AbstractTheme 
	 *
	 * @abstract
	 */

	abstract class AbstractTheme {
		/**
		 * The name of the theme
		 *
		 * @access protected
		 * @var string
		 * @see getThemeName()
		 */

		protected $themeName;

		/**
		 *	The sub-directory where theme components live
		 *
		 * @access protected
		 * @var string
		 * @see getThemeDirectory()
		 */

		protected $themeDirectory;

		/**
		 * The base URL for CSS files.
		 * 
		 *	@access protected
		 * @var string
		 * @see getCSSURL()
		 * @see renderCSS()
		 */

		protected $cssURL;

		/**
		 * The base URL for javascript files
		 *
		 *	@access protected
		 * @var string
		 * @see getJavascriptURL()
		 * @see renderJavascript()
		 */


		protected $javascriptURL;

		/**
		 * An array of css filenames to load for the theme.  Can
		 * just be a filename, or can be a path plus filename
		 * underneath {@link $cssURL}
		 *
		 *
		 * @access protected
		 * @var array
		 * @see getCSSToLoad()
		 * @see renderCSS()
		 */

		protected $cssToLoad = array();

		/**
		 * An array of javascript filenames to load for the theme.  Can
		 * be just a filename, or a path plus filename underneath
		 * {@link $javascriptURL}
		 *
		 * @access protected
		 * @var array
		 * @see getJavascriptToLoad()
		 * @see renderJavascript()
		 */

		protected $javascriptToLoad = array();


		/**
		 * Get the theme name
		 *
		 * @access public
		 * @return string
		 * @see $themeName
		 */

		public function getThemeName() {
			return $this->themeName;
		}

		/**
		 * Get the theme directory
		 *
		 * @access public
		 * @return string
		 * @see $themeDirectory
		 */

		public function getThemeDirectory() {
			return $this->themeDirectory;
		}

		/**
		 * Gets the list of CSS files to load
		 *
		 * @access public
		 * @return array
		 * @see $cssToLoad
		 * @see renderCSS()
		 */

		public function getCSSToLoad() {
			return $this->cssToLoad;
		}

		/**
		 * Gets the base CSS URL
		 *
		 * @access public
		 * @return string
		 * @see $cssURL
		 * @see renderCSS()
		 */

		public function getCSSURL() {
			return $this->cssURL;
		}

		/**
		 * Get the base javascript URL
		 *
		 * @access public
		 * @return string
		 * @see $javascriptURL
		 * @see renderJavascript
		 */

		public function getJavascriptURL() {
			return $this->javascriptURL;
		}

		/**
		 * Get the list of javascript files to load
		 * @access public
		 * @return array
		 * @see $javascriptToLoad
		 * @see renderJavascript()
		 */

		public function getJavascriptToLoad() {
			return $this->javascriptToLoad;
		}

		/**
		 *	Loop through the files in {@link $cssToLoad} and generate
		 * <link> tags for each one.  If you want to add some custom
		 * style tags, you can also do so here.  In your theme sub-class,
		 * implement this function and just add your custom stuff
		 * after calling parent::renderCSS()
		 *
		 * @access public
		 * @return string
		 * @see getCSSURL()
		 * @see getCSSToLoad()
		 */


		public function renderCSS() {
			$output = '';
			foreach($this->getCSSToLoad() as $cssFile) {
				$output .= '<link rel="stylesheet" type="text/css" href="' . $this->getCSSURL() . $cssFile . '">' . "\n";
			}
			return $output;
		}

		/**
		 *	Loop through the files in {@link $javascriptToLoad} and generate
		 * <script> tags for each one.  If you want to add some custom
		 * javascript tags (like including shit from the Google JS library),
		 * you can also do so here.  In your theme sub-class, implement this
		 * function and just add your custom stuff after calling parent::renderJavascript()
		 *
		 * @access public
		 * @return string
		 * @see getJavascriptURL()
		 * @see getJavascriptToLoad()
		 */

		public function renderJavascript() {
			$output = '';
			foreach($this->getJavascriptToLoad() as $javascriptFile) {
				$output .= '<script language="javascript" type="text/javascript" src="' . $this->getJavascriptURL() . $javascriptFile . '"></script>';
			}
			return $output;
		}
	}
?>