<?
/**
	* @package Skeet
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet;
	
	/*
	 * Generator class, handles setting up new applications, 
	 * model generation, etc
	 */
	
	class Generator {
		const GENERATE_TYPE_NEW_APPLICATION = 1;
		const GENERATE_TYPE_MODEL = 2;
		
		public static function doGenerate($generateType) {
			switch($generateType) {
				case self::GENERATE_TYPE_NEW_APPLICATION:
					self::setupNewApplication();
					break;
				
				case self::GENERATE_TYPE_MODEL:
					$codeGenerator = new Generator\MysqlCodeGenerator();
					$codeGenerator->doGenerate();
					break;
			}
		}
		
		private static function setupNewApplication() {
			
			if(!file_exists(\Skeet\Skeet::getConfig("application_lib_path"))) {
				mkdir(\Skeet\Skeet::getConfig("application_lib_path"));
			}
			
			$subDirectoryArray = array();
			$subDirectoryArray["classes"] = array();
			$subDirectoryArray["components"] = array();
			$subDirectoryArray["page"] = array(
				"abstract page"  => array("file_name" => "abstract_page.class.php")
			);
			$subDirectoryArray["factory"] = array(
				"page factory" => array("file_name" => "page_factory.class.php"),
				"component factory" => array("file_name" => "component_factory.class.php"),
				"theme factory" => array("file_name" => "theme_factory.class.php"),
				"link factory" => array("file_name" => "link_factory.class.php"),
				"database factory" => array("file_name" => "database_factory.class.php"),
				"model factory" => array("file_name" => "model_factory.class.php"),
				"model collection factory" => array("file_name" => "model_collection_factory.class.php")
			);
			
			$subDirectoryArray["factory"] = array(
				"default theme"  => array("file_name" => "default_theme.class.php")
			);
			
			\Skeet\ThemeFactory::setCurrentThemeName("skel");
			foreach($subDirectoryArray as $subDirectory => $generatedFileArray) {
				if(!file_exists(\Skeet\Skeet::getConfig("application_lib_path") . $subDirectory)) {
					mkdir(\Skeet\Skeet::getConfig("application_lib_path") . $subDirectory);
				}
				if(count($generatedFileArray)) {
					foreach($generatedFileArray as $componentName => $fileInfoArray) {
						ob_start();
						\Skeet\ComponentFactory::getComponent($componentName)->render();
						$fileOutput = ob_get_clean();
						file_put_contents(\Skeet\Skeet::getConfig("application_lib_path") . $subDirectory . "/" . $fileInfoArray["file_name"],$fileOutput);
					}
				}
			}
			
			touch(\Skeet\Skeet::getConfig("application_lib_path") . "load.php");
		}
	}
?>