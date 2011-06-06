<?
	/**
	* @package Skeet
	* @subpackage Generator
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/
	namespace Skeet\Generator;

	/**
	* AbstractCodeGenerator
	* @abstract
	*/

	abstract class AbstractCodeGenerator {

		const GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL = 1;
		const GENERATED_CODE_TEMPLATE_TYPE_ALL = 2;

		/**
		 * The list of tables found in the application database
		 *
		 *	@access protected
		 * @var array
		 * @see getTableArray()
		 * @see hasTable()
		 */

		protected $tableArray = array();

		/**
		 *	{@link TableDescription} objects
		 *
		 * @access protected
		 * @var array
		 * @see getTableDescriptionArray()
		 * @see getTableDescription()
		 */
		protected $tableDescriptionArray = array();

		/**
		 * The regular expression used to preg_match
		 * against a table name to detect if it's a join table
		 *
		 *	@access protected
		 * @var string
		 */

		protected $manyToManyPattern = "/([^2]*)2(.*)/";


		/**
		 *	A list of generated code templates, and their corresponding
		 * settings.  In the format:
		 *
		 * array(
		 *		"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_(INDIVIDUAL|ALL)
		 *		"component_name" => "", // the name the component, passed to {@link ComponentFactory::getComponent()}
		 *		"path" => "", // the path below Skeet::getConfig("application_lib_path") to store the generated code in
		 *		"file_suffix" => "", // the suffix for files written.
		 *		"overwrite_existing_file => true|false, // whether to overwrite an existing file on generation of code
		 *		"settings" => array() // an array of arbitrary settings to pass to the template component
		 * )
		 *
		 * @access protected
		 * @var array
		 * @see getGeneratedCodeTemplateArray()
		 * @see addGeneratedCodeTemplate()
		 */

		protected $generatedCodeTemplateArray = array(
			"model" => array(
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL,
									"component_name" => "model template",
									"path" => "generated/model/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => true
			),
			"model_collection" => array (
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL,
									"component_name" => "model collection template",
									"path" => "generated/model/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => true
			),
			"starter_model" => array(
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL,
									"component_name" => "starter model template",
									"path" => "model/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => false
			),
			"starter_model_collection" => array (
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL,
									"component_name" => "starter model collection template",
									"path" => "model/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => false
			),
			"model_factory" => array(
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_ALL,
									"component_name" => "model factory template",
									"path" => "generated/factory/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => true
			),
			"model_collection_factory" => array(
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_ALL,
									"component_name" => "model collection factory template",
									"path" => "generated/factory/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => true
			)
		);


		abstract protected function loadTables();
		abstract protected function processTable($tableName);

		public function doGenerate() {
			\Skeet\Factory\ThemeFactory::setCurrentThemeName("generator");
			$this->loadTables();
			$this->processTables();
			$this->generateCode();
		}

		protected function processTables() {
			foreach($this->getTableArray() as $tableName) {
				$this->processTable($tableName);
			}
		}

		/**
		 *	Actually generate and write the generated code to a file.
		 * The loop always passes the table description to the component,
		 *	as well as any additional settings set in {@link $generatedCodeTemplateArray}
		 *
		 * @access protected
		 * @see getTableDescriptionArray()
		 * @see getGeneratedCodeTemplateArray()
		 */

		protected function generateCode() {
			/**
			 * Start an output buffer, because we don't want rendered components
			 * outputting to the screen, that would be useless.
			 */

			ob_start();

			/**
			 * Loop through the table descriptions
			 */

			foreach($this->getTableDescriptionArray() as $tableDescription) {

				/**
				 * Loop through the generated code templates
				 */

				foreach($this->getGeneratedCodeTemplateArray() as $generatedCodeTemplateName => $generatedCodeTemplateSettings) {
					if($generatedCodeTemplateSettings["template_type"] == AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_INDIVIDUAL) {
						/**
						 * Load up the component that has the actual template code.
						 * Component name is set from the template config.  By default,
						 * the Generator theme is used, and these files are in skeet/components/generator.
						 * I'm not 100% sure that I've made it flexible enough to look for file in user directories,
						 * or switch themes.  Something to test.
						 */

						$generatedCode = \Skeet\Factory\ComponentFactory::getComponent($generatedCodeTemplateSettings["component_name"])->addSetting("table_description",$tableDescription);

						/**
						 * You can pass arbitrary settings to the component, if you want to.
						 */

						if(isset($generatedCodeTemplateSettings["settings"]) && is_array($generatedCodeTemplateSettings["settings"]) && count($generatedCodeTemplateSettings["settings"])) {
							foreach($generatedCodeTemplateSettings["settings"] as $key => $value) {
								$generatedCode->addSetting($key,$value);
							}
						}
						/**
						 * Render the code to the output buffer
						 */
						$generatedCode->render();

						/**
						 * If the file that would be written either a.) doesn't exist,
						 * or b.) is set as overwriteable in the template config, write the
						 * contents
						 *
						 */
						$targetFilePath = \Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"] . $tableDescription->getTableName() . $generatedCodeTemplateSettings["file_suffix"];
						if($generatedCodeTemplateSettings["overwrite_existing_file"] || !file_exists($targetFilePath)) {
							if(!file_exists(\Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"])) {
								mkdir(\Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"]);
							}
							file_put_contents($targetFilePath,ob_get_contents());
						}
						ob_clean();
					}
				}
			}

			foreach($this->getGeneratedCodeTemplateArray() as $generatedCodeTemplateName => $generatedCodeTemplateSettings) {
				if($generatedCodeTemplateSettings["template_type"] == AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_ALL) {
					$generatedCode = \Skeet\Factory\ComponentFactory::getComponent($generatedCodeTemplateSettings["component_name"])->addSetting("table_descriptions",$this->getTableDescriptionArray());

					/**
					 * You can pass arbitrary settings to the component, if you want to.
					 */

					if(isset($generatedCodeTemplateSettings["settings"]) && is_array($generatedCodeTemplateSettings["settings"]) && count($generatedCodeTemplateSettings["settings"])) {
						foreach($generatedCodeTemplateSettings["settings"] as $key => $value) {
							$generatedCode->addSetting($key,$value);
						}
					}
					/**
					 * Render the code to the output buffer
					 */

					$generatedCode->render();
					
					/**
					 * If the file that would be written either a.) doesn't exist,
					 * or b.) is set as overwriteable in the template config, write the
					 * contents
					 *
					 */

					$targetFilePath = \Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"] . str_replace(" ","_",strtolower($generatedCodeTemplateName)) . $generatedCodeTemplateSettings["file_suffix"];
					if($generatedCodeTemplateSettings["overwrite_existing_file"] || !file_exists($targetFilePath)) {
						if(!file_exists(\Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"])) {
							mkdir(\Skeet\Skeet::getConfig("application_lib_path") . $generatedCodeTemplateSettings["path"]);
						}
						file_put_contents($targetFilePath,ob_get_contents());
					}
					ob_clean();
				}
			}

		}

		/**
		 *	Get the list of tables
		 *
		 * @access protected
		 * @return array
		 * @see $tableArray
		 */

		protected function getTableArray() {
			return $this->tableArray;
		}

		/**
		 *	Get the list of table descriptions
		 *
		 * @access protected
		 * @return array
		 * @see $tableDescriptionArray
		 */

		protected function getTableDescriptionArray() {
			return $this->tableDescriptionArray;
		}

		/**
		 * Get the array of settings for code to generate
		 * after all the tables have been loaded
		 *
		 *	@access protected
		 * @return array
		 * @see $generatedCodeTemplateArray
		 */

		protected function getGeneratedCodeTemplateArray() {
			return $this->generatedCodeTemplateArray;
		}

		/**
		 *	Checks to see if a table definition has been loaded up.
		 *
		 * @access protected
		 * @param string $tableName
		 * @return boolean
		 * @see $tableArray
		 */

		protected function hasTable($tableName) {
			if(isset($this->tableArray[$tableName])) {
				return true;
			}
			return false;
		}

		/**
		 *	Get the regular expression to match many to many
		 * tables on.
		 * @access protected
		 * @return string
		 * @see $manyToManyPattern
		 */

		protected function getManyToManyPattern() {
			return $this->manyToManyPattern;
		}

		/**
		 * Add/replace a piece of code to be generated while iterating
		 * through each table description.  If you wanted to, you could
		 * override the existing default sets with your own stuff, or just
		 * add things on
		 *
		 * @access public
		 * @param string $label
		 * @param string $componentName
		 * @param string $outputPath
		 * @param string $fileSuffix
		 * @param boolean $overwriteExistingFile
		 * @param array $additionalSettings
		 * @see $generatedCodeTemplateArray
		 */

		public function addGeneratedCodeTemplate($generatedCodeTemplateType,$label,$componentName,$outputPath='classes/',$fileSuffix='.class.php',$overwriteExistingFile=false,$additionalSettings=array()) {
			$this->generatedCodeTemplateArray[$label] = array(
				"template_type" => $generatedCodeTemplateType,
				"component_name" => $componentName,
				"path" => $outputPath,
				"file_suffix" => $fileSuffix,
				"overwrite_existing_file" => $overwriteExistingFile,
				"settings" => $additionalSettings
			);
		}

		/**
		* @access protected
		* @param string $tableName
		* @return TableDescription
		* @see $tableDescriptionArray
		*/
		protected function getTableDescription($tableName) {
			if(!isset($this->tableDescriptionArray[$tableName])) {
				$this->tableDescriptionArray[$tableName] = new TableDescription($tableName);
			}
			return $this->tableDescriptionArray[$tableName];
		}
	}

?>
