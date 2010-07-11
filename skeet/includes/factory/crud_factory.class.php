<?
	namespace Skeet;
	
	class CrudFactory  {
		public function getCrud($tableName,$type) {
			if(file_exists(CRUD_PATH . $tableName . '_' . $type . ".comp.php")) {
				$fileName = $tableName . '_' . $type . '.comp.php';
				$filePath = CRUD_PATH;
			}
			elseif(file_exists(GENERATED_CRUD_PATH . $tableName . '_' . $type . "_generated.comp.php")) {
				$fileName = $tableName . '_' . $type . '_generated.comp.php';
				$filePath = GENERATED_CRUD_PATH;
			}
			
			if(isset($fileName)) {
				$componentObject = new \Skeet\Component\GenericComponent();
				$componentObject->setFileName($fileName);
				$componentObject->setFilePath($filePath);
				$componentObject->setPageObject(PageFactory::getCurrentPage());

				return $componentObject;
			}
		}
		
	}
?>