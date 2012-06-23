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
		 *	Temp storage for a DB object
		 * 
		 * @var \Skeet\Database\AbstractDatabase
		 */
		
		protected $db;
		
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
									"file_suffix" => "_collection.class.php",
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
									"file_suffix" => "_collection.class.php",
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
			),
			"page_factory" => array(
									"template_type" => AbstractCodeGenerator::GENERATED_CODE_TEMPLATE_TYPE_ALL,
									"component_name" => "page factory template",
									"path" => "generated/factory/",
									"file_suffix" => ".class.php",
									"overwrite_existing_file" => true
			)
		);


		abstract protected function loadTables();
		
		
		public function __construct() {
			$this->db = \Skeet\DatabaseFactory::getDatabase();
		}
		
		/**
		 * Returns (@see $db)
		 * @return \Skeet\Database\AbstractDatabase
		 */
		
		public function getDB() {
			return $this->db;
		}
		
		/**
		 * Calls the various steps of code generation in order.
		 * First, get the list of tables via (@see loadTables()),
		 * then process those tables into TableDescription objects,
		 * then generate the code.
		 */

		public function doGenerate() {
			\Skeet\ThemeFactory::setCurrentThemeName("generator");
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

						$generatedCode = \Skeet\ComponentFactory::getComponent($generatedCodeTemplateSettings["component_name"])->addSetting("table_description",$tableDescription);

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
					$generatedCode = \Skeet\ComponentFactory::getComponent($generatedCodeTemplateSettings["component_name"])->addSetting("table_descriptions",$this->getTableDescriptionArray());

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
		
		/**
		 *
		 * @param type $tableName 
		 */
		
		protected function processTable($tableName) {
			$columnNameLabel = $this->getDB()->getColumnNameLabel();
			$sql =  $this->getDB()->getDescribeKeyword() . " " . $tableName;
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			$manyToManyMatches = array();

			/**
			 * First, we want to check and see if this is a many to many
			 * linking table, or a table that contains actual data, using the
			 * regular expression pattern defined in AbstractCodeGenerator.  The
			 * default pattern is <table_1>2<table_2>, but you can change that to whatever
			 * if you want to sub-class the generator.
			 */


			if(preg_match($this->getManyToManyPattern(),$tableName,$manyToManyMatches)) {
				$tempSourceTableName = $manyToManyMatches[1];
				$tempDestinationTableName = $manyToManyMatches[2];

				/**
				 * Similar to what we do below with target objects, we allow for prefixing
				 * in table names, for example, user2child_user, with both pointing at
				 * the user table.  See below for details on how it checks.  Here we check
				 * and make sure that both tables the linking table describes exist.
				 */


				$tempSourceTableArray = explode("_",$tempSourceTableName);
				$tempDestinationTableArray = explode("_",$tempDestinationTableName);
				$hasFoundSourceTable = false;
				$hasFoundDestinationTable = false;

				while(count($tempSourceTableArray) && !$hasFoundSourceTable) {
					$tempTableName = implode("_",$tempSourceTableArray);
					if($this->hasTable($tempTableName)) {
						$hasFoundSourceTable = true;
						$sourceTableName = $tempTableName;
					}
					else {
						array_shift($tempSourceTableArray);
					}
				}

				while(count($tempDestinationTableArray) && !$hasFoundDestinationTable) {
					$tempTableName = implode("_",$tempDestinationTableArray);
					if($this->hasTable($tempTableName)) {
						$hasFoundDestinationTable = true;
						$destinationTableName = $tempTableName;
					}
					else {
						array_shift($tempDestinationTableArray);
					}
				}
				/**
				 * If they exist, we start processing.
				 */
				if($hasFoundDestinationTable && $hasFoundSourceTable) {
					/**
					 * In the same way that primary keys have to be in the format
					 * <table_name>_id, foreign keys in join tables have to be in the format
					 * <prefix_plus_table_name>_id.
					 */

					$sourceTableJoinKeyName = $tempSourceTableName . "_id";
					$destinationTableJoinKeyName = $tempDestinationTableName . "_id";

					/**
					 * We want to see if there are addition columns in the join table,
					 * for example, a sort order, but we want to exclude columns from the
					 * check.  With creation_datetime, last_modified_date, and is_retired
					 * as required fields on all tables, we add the join keys that we already
					 * know about.  Anything left over is an additional column that we want
					 * to give objects access to.
					 */

					$extraColumnExcludeList = array(
						"creation_datetime",
						"last_modified_date",
						"is_retired",
						$tableName . "_id",
						$sourceTableJoinKeyName,
						$destinationTableJoinKeyName
					);


					$extraColumnArray = array();

					while($row = $result->getRow()) {
						if(!in_array($row[$columnNameLabel],$extraColumnExcludeList)) {
							$extraColumnArray[$row[$columnNameLabel]] = $row[$columnNameLabel];
						}
					}
					
					$sourceTableDescription = $this->getTableDescription($sourceTableName);
					$destinationTableDescription = $this->getTableDescription($destinationTableName);

					$sourceTableDescription->addManyToManyCollection($destinationTableName, $tableName, $tempDestinationTableName, $destinationTableDescription->getPrimaryKeyFieldName(), $destinationTableJoinKeyName, $sourceTableJoinKeyName, $extraColumnArray);
					$destinationTableDescription->addManyToManyCollection($sourceTableName, $tableName, $tempSourceTableName, $sourceTableDescription->getPrimaryKeyFieldName(), $sourceTableJoinKeyName, $destinationTableJoinKeyName, $extraColumnArray);
				}

			}
			else {
				$tableDescription = $this->getTableDescription($tableName);
				while($row = $result->getRow()) {
					$tableDescription->addField($row[$columnNameLabel],\Skeet\Util::getDatatypeFromSQL($row[$this->getDB()->getDataTypeLabel()]),$row[$this->getDB()->getDefaultValueLabel()]);
					if($row[$columnNameLabel] == $tableName . "_name") {
						$tableDescription->setDisplayNameField($row[$columnNameLabel]);
					}

					/**
					 * Check to see if the field is the primary key for the table.
					 * If it is, just set that.  If it's not, continue to process
					 * the field and figure out what relationships it has with other
					 * tables and such.
					 */

					if($this->getDB()->isColumnPK($row)) {
						$tableDescription->setPrimaryKeyFieldName($row[$columnNameLabel]);
					}
					else {
						/**
						 * Right now, the only thing we care about is if this field
						 * ends with _id, which means that it points to the primary
						 * key of another table.  We could figure it out via foreign
						 * key relationships, but a.) I don't want to require that
						 * people use FK's, because not everyone is great at them, and
						 * b.) because this forces a naming convention that makes a
						 * table structure easy to interpret by looking at it.
						 *
						 * What we're looking for is $tableName_id, ie: user_id.
						 */

						if(substr(strtolower($row[$columnNameLabel]),-3) == "_id") {
							/**
							 * I've found that, in using Skeet and it's predecessors,
							 * it's necessary to allow for prefixing column names,
							 * either to avoid conflicts with other relationships or
							 * to link tables back onto themselves.  So, what we do is
							 * check to see if the full field matches the primary key of
							 * another table, and if it doesn't, knock off chunks of the
							 * field name delimited by _'s.
							 *
							 * Example:
							 * You have a table called user with a primary key of (of course),
							 * user_id.  You have a field on another table called
							 * baby_eating_user_id.  Here's how it'd check:
							 *
							 * baby_eating_user => fail
							 * eating_user => fail
							 * user => success!
							 *
							 * The prefixes are retained in the collection/target definitions,
							 * so in the example above, it'd be something like:
							 * $otherObject->getTargetObject("baby_eating_user")
							 */

							$tempFieldArray = explode("_",$row[$columnNameLabel]);
							$tempFieldName = substr_replace($row[$columnNameLabel],'',-3);
							/* we know that the last element is going to be _id, and we don't want it */
							array_pop($tempFieldArray);
							$hasFoundTable = false;
							while(count($tempFieldArray) && !$hasFoundTable) {
								$tempTableName = implode("_",$tempFieldArray);

								/**
								 * If the table exists in the database...
								 */

								if($this->hasTable($tempTableName)) {
									$hasFoundTable = true;

									/**
									 * Add a target object to this table.
									 */

									$tableDescription->addTargetObject($tempTableName,$tempFieldName,$row[$columnNameLabel]);

									/**
									 * We want to maintain prefixing in both directions, so we replace instances
									 * of the target table's name with this table's name when describing
									 * the one to many collection.  So, continuing the example above, it'd be:
									 *
									 * $user->getCollection("baby_eating_other_object")
									 */

									$targetName = str_replace($tempTableName,$tableName,$tempFieldName);
									$targetTableDescription = $this->getTableDescription($tempTableName);
									$targetTableDescription->addOneToManyCollection($tableName, $targetName, $row[$columnNameLabel]);
								}
								/**
								 * Otherwise pop the first element off of the array, and do it again.
								 */
								else {
									array_shift($tempFieldArray);
								}
							}
						}
					}
				}
			}
		}
	}

?>
