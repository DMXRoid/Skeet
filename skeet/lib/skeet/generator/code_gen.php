<?
	require("../application.inc.php");
	set_time_limit(10);
	
	
	function classifyTableName($tableName) {
		$tableName = str_replace("_"," ",$tableName);
		$tableName = ucwords($tableName);
		$tableName = str_replace(" ","",$tableName);
		return $tableName;	
	}
	
	function variablizeTableName($tableName) {
		$tableArray = explode("_",$tableName);
		$tableName = strtolower($tableArray[0]);
		unset($tableArray[0]);
		foreach($tableArray as $piece) {
			$tableName .= ucfirst($piece);	
		}
		return $tableName;
	}
	
	if(count($argv) < 2 || count($argv) > 5) {
		echo '-----
usage:

code_gen.php <database_name> [options]

Options:
	-c, --generate-crud			generates (C)reate,(R)etreive,(U)pdate,(D)elete templates and logic
	--generated_path=PATH			sets the output directory for generated classes.
						defaults to ' . APPLICATION_ROOT . 'includes/generated
	-s, --generate-starter-models		generate starter model classes for their Generated counterparts.
						Will not overwrite existing files
	--starter-model-path=PATH		sets the output directory for starter models.
						defaults to ' . APPLICATION_ROOT . 'includes/model
';
		die();
	}
	$databaseName = $argv[1];
	$db = \Skeet\DatabaseFactory::getDatabase($databaseName);
	$db->selectDB($databaseName);
	
	$generatedPath = INCLUDE_PATH . "generated";
	$includeStarterClasses = false;
	$starterClassPath = INCLUDE_PATH . "model";
	
	if(count($argv) > 2) {
		foreach($argv as $key => $value) {
			if(substr($value,0,2) == "--") {
				$settingArray = explode("=",substr_replace($value,'',0,2));
				switch($settingArray[0]) {
					case "generated_path":
						$generatedPath = $settingArray[1];
						
						
						break;
						
					case "include_starter_classes":
						$includeStarterClasses = true;
						break;
						
					case "starter_class_path":
						$starterClassPath = $settingArray[1];
						$includeStarterClasses = true;
						break;
				}
			}
		}
	}
	if(file_exists($generatedPath) && is_writeable($generatedPath)) {
		if($includeStarterClasses) {
			if(file_exists($starterClassPath) && is_writeable($starterClassPath)) {
				
			}
			else {
				echo "the starter_class_path (" . $starterClassPath . ") either does not exist or is not writeable.\n";
				die();
			}
		}
	}
	else {
		echo "the generated_path (" . $generatedPath . ") either does not exist or is not writeable.\n";
		die();
	}
	
	if($databaseName) {
		
		$dbName = $databaseName;
		error_reporting(E_ALL);
		$db = \Skeet\DatabaseFactory::getDatabase($dbName);
		$dbNameConstant = strtoupper($dbName) . "_DB";
		$sql = "SHOW TABLES";
		$result = $db->doQuery($sql);
		while($row = $result->getRow()) {
			$classArray[$row["Tables_in_" . $dbName]] = array();
		}
		$classDBName = $dbName . "_db";
		
		
		foreach($classArray as $tableName => $classSetupArray) {
			if(strstr($tableName,"2")) {
				
				$tableList = explode("2",$tableName);
				
				$firstTableSetup = array();
				$secondTableSetup = array();
				
				$classVariable0 = variablizeTableName($tableList[0]);
				$classVariable1 = variablizeTableName($tableList[1]);
				
				$joinTableKey0 = $tableList[0] . "_id";
				$joinTableKey1 = $tableList[1] . "_id";

				if(!isset($classArray[$tableList[0]])) {
					$classVariable0 = variablizeTableName($tableList[0]);
					$tempTableArray = explode("_",$tableList[0]);
					unset($tempTableArray[0]);
					$tempTable = implode("_",$tempTableArray);
					if(isset($classArray[$tempTable])) {
						$tableList[0] = $tempTable;
					}
					else {
						continue;
					}
				}

				if(!isset($classArray[$tableList[1]])) {
					$classVariable1 = variablizeTableName($tableList[1]);
					$tempTableArray = explode("_",$tableList[1]);
					$classVariable0 = variablizeTableName($tempTableArray[0] . "_" . $classVariable0);
					unset($tempTableArray[0]);
					$tempTable = implode("_",$tempTableArray);

					if(isset($classArray[$tempTable])) {
						$tableList[1] = $tempTable;
					}
					else {
						continue;
					}
				}
	
			
				if(!isset($classArray[$tableList[0]]["many2many"])) {
					$classArray[$tableList[0]]["many2many"] = array();
				}
				if(!isset($classArray[$tableList[1]]["many2many"])) {
					$classArray[$tableList[1]]["many2many"] = array();
				}
				
				$fieldSkipArray = array($joinTableKey1,$joinTableKey0,"creation_datetime","last_modified_date","is_retired");
				$extraFieldsArray = array();
				
				$sql = "DESCRIBE " . $tableName;
				$result = $db->doQuery($sql);
				$firstTableExtraFieldsArray = array();
				$secondTableExtraFieldsArray = array();
				while($row = $result->getRow()) {
					if($row["Key"] != "PRI" && !in_array($row["Field"],$fieldSkipArray)) {
						$firstTableExtraFieldsArray[$row["Field"]] = $row["Field"];
						$secondTableExtraFieldsArray[$row["Field"]] = $row["Field"];
					}
				}
;
				
				
				$sql = "DESCRIBE " . $tableList[0];
				$result = $db->doQuery($sql);
				while($row = $result->getRow()) {
					if($row["Key"] == "PRI") {	
						$primaryKey0 = $row["Field"];
						continue;	
					}
				}
				
				$sql = "DESCRIBE " . $tableList[1];
				$result = $db->doQuery($sql);
				
				while($row = $result->getRow()) {
					if($row["Key"] == "PRI") {	
						$primaryKey1 = $row["Field"];
						continue;	
					}
				}
				
				if(isset($classArray[$tableList[0]])) {
					$tempCollectionObjectArray = array();
					$tempCollectionObjectArray["join_table"] = $tableName;
					$tempCollectionObjectArray["table_name"] = $tableList[1];
					$tempCollectionObjectArray["target_join_key"] = $primaryKey1;
					$tempCollectionObjectArray["collection_join_key"] = $joinTableKey1;
					$tempCollectionObjectArray["local_join_key"] = $joinTableKey0;
					$tempCollectionObjectArray["collection_type"] = COLLECTION_TYPE_MANY_TO_MANY;
					if(isset($firstTableExtraFieldsArray) && count($firstTableExtraFieldsArray)) {
						$tempCollectionObjectArray["extra_fields"] = $firstTableExtraFieldsArray;
					}
					//$classArray[$tableList[0]]["many2many"][$tableName] = $firstTableSetup;
					$classArray[$tableList[0]]["collection_definitions"][$tableList[1]] = $tempCollectionObjectArray;
				}
				
				if(isset($classArray[$tableList[1]]) && $tableList[1] != $tableList[0]) {
					
					$tempCollectionObjectArray = array();
					$tempCollectionObjectArray["join_table"] = $tableName;
					$tempCollectionObjectArray["table_name"] = $tableList[0];
					$tempCollectionObjectArray["target_join_key"] = $primaryKey0;
					$tempCollectionObjectArray["collection_join_key"] = $joinTableKey0;
					$tempCollectionObjectArray["local_join_key"] = $joinTableKey1;
					$tempCollectionObjectArray["collection_type"] = COLLECTION_TYPE_MANY_TO_MANY;
					if(isset($secondTableExtraFieldsArray) && count($secondTableExtraFieldsArray)) {
						$tempCollectionObjectArray["extra_fields"] = $secondTableExtraFieldsArray;
					}
					//$classArray[$tableList[1]]["many2many"][$tableName] = $secondTableSetup;
					$classArray[$tableList[1]]["collection_definitions"][$tableList[0]] = $tempCollectionObjectArray;
				}
				unset($classArray[$tableName]);	
			}
			else {
				$tableNameArray = explode("_",$tableName);
				
				$classSetupArray["table_name"] = $tableName;
				$classSetupArray["class_name"] = classifyTableName($tableName);
				$classSetupArray["collection_name"] = classifyTableName($tableName) . "Collection";
				
				$classSetupArray["data_structure"] = array();
				$classSetupArray["target_object_definitions"] = array();
				$classSetupArray["target_object_fields"] = array();
				
				$classSetupArray["fields"] = array();
				
				$sql = "DESCRIBE " . $tableName;
				$result2 = $db->doQuery($sql);
				
				
				
				while($row2 = $result2->getRow()) {
					
					$fieldName = $row2["Field"];
					$dataType = $row2["Type"];
					$defaultValue = $row2["Default"];
					
					$tempDataStructureArray = array();
					$tempDataStructureArray["field_name"] = $fieldName;
					$tempDataStructureArray["data_type"] = getDatatypeFromSQL($row2["Type"]);
					$tempDataStructureArray["value"] = $defaultValue;
					$tempDataStructureArray["default_value"] = NULL;
					
					$classSetupArray["data_structure"][$fieldName] = $tempDataStructureArray;
					$fieldArray = explode('_',$fieldName);
					
					if($fieldName == $tableName . '_name') {
						$classSetupArray["display_name"] = $fieldName;
					}
					
					if($row2["Key"] == "PRI") {
						$classSetupArray["primary_key"]["field_name"] = $fieldName;
					}
					else {
						if(substr(strtolower($fieldName),-2) == "id") {
							$counter = 0;
							$tempFieldName = substr_replace($fieldName,'',-3);
							
							while($tempFieldName && count(explode("_",$tempFieldName))) {
								if($counter) {
									$tempFieldArray = explode("_",$tempFieldName);
									unset($tempFieldArray[0]);
									$tempFieldName = implode("_",$tempFieldArray);
								}
								$tempTargetObjectArray = array();
								$tempCollectionObjectArray = array();
								$tempTableName = strtolower(substr_replace($fieldName,'',-3));
								$targetTableName = '';
								if(isset($classArray[$tempFieldName])) {
									$className = classifyTableName($tempTableName);
									$classVariableName = variablizeTableName(substr_replace($fieldName,'',-3));
									$targetTable = $tempFieldName;
									
									$tempTargetObjectArray["table_name"] = $targetTable;
									$tempTargetObjectArray["foreign_key_name"] = $fieldName;
									
									$classSetupArray["target_object_definitions"][$tempTableName] = $tempTargetObjectArray;
									$classSetupArray["target_object_fields"][$fieldName] = $targetTable;
									
									$tempCollectionObjectArray["collection_type"] = COLLECTION_TYPE_ONE_TO_MANY;
									$tempCollectionObjectArray["table_name"] = $tableName;
									$tempCollectionObjectArray["primary_key"] = $fieldName;
									echo $targetTable . " : " . $tableName . "\n";
									
									$classArray[$targetTable]["collection_definitions"][$tableName] = $tempCollectionObjectArray;
									$tempFieldName = "";	
									
									
								}
								else {
									$counter++;
								}
							}
						}
					}
				}
				foreach($classSetupArray as $key => $value) {
					$classArray[$tableName][$key] = $value;
				}
			}
		}	
		ksort($classArray);
	}
	
	foreach($classArray as $tableName => $classSetupArray) {
		ob_start();
		include("object_collection_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_MODEL_PATH . $tableName . '_collection_generated.class.php',$generatedClass);
		
		ob_start();
		include("object_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_MODEL_PATH . $tableName . '_generated.class.php',$generatedClass);
		
		if(!file_exists(MODEL_PATH . $tableName . '.class.php')) {
			ob_start();
			include("starter_model_template.inc.php");
			$generatedClass = ob_get_clean();
			file_put_contents(MODEL_PATH . $tableName . '.class.php',$generatedClass);
			
			
			ob_start();
			include("starter_model_collection_template.inc.php");
			$generatedClass = ob_get_clean();
			file_put_contents(MODEL_PATH . $tableName . '_collection.class.php',$generatedClass);
		}
		ob_start();
		include("crud_manage_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_CRUD_PATH . $tableName . '_manage_generated.comp.php',$generatedClass);
		
		ob_start();
		include("crud_collection_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_CRUD_PATH . $tableName . '_list_generated.comp.php',$generatedClass);
		
		ob_start();
		include("crud_manage_page_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_PAGE_PATH . $tableName . '_manage_generated_page.class.php',$generatedClass);
		
		ob_start();
		include("crud_list_page_template.inc.php");
		$generatedClass = ob_get_clean();
		file_put_contents(GENERATED_PAGE_PATH . $tableName . '_list_generated_page.class.php',$generatedClass);
	}
	
	ob_start();
	include("model_factory_template.inc.php");
	$generatedClass = ob_get_clean();
	file_put_contents(GENERATED_FACTORY_PATH . 'model_factory_generated.class.php',$generatedClass);
	
	ob_start();
	include("model_collection_factory_template.inc.php");
	$generatedClass = ob_get_clean();
	file_put_contents(GENERATED_FACTORY_PATH . 'model_collection_factory_generated.class.php',$generatedClass);
	
	ob_start();
	include("page_factory_template.inc.php");
	$generatedClass = ob_get_clean();
	file_put_contents(GENERATED_FACTORY_PATH . 'page_factory_generated.class.php',$generatedClass);
	
	ob_start();
	include("list_link_template.inc.php");
	$generatedClass = ob_get_clean();
	file_put_contents(COMPONENT_INCLUDE_PATH . 'manage_link_list.comp.php',$generatedClass);
?>
