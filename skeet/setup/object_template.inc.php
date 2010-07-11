<?
	$arrayToTextList = array("data_structure","collection_definitions","target_object_definitions");
	$arrayToTextArray = array();
	foreach($arrayToTextList as $listItem) {
		$arrayToTextArray[$listItem] = array();
		if(isset($classSetupArray[$listItem]) && count($classSetupArray[$listItem])) {
			foreach($classSetupArray[$listItem] as $keyName => $keyArray) {
				$tempKeyArray = array();
				$string = ' "' . $keyName . '"  => array(
													';
				foreach($keyArray as $key => $value) {
					if(is_null($value)) {
						$value = "NULL";
					}
					elseif(is_array($value)) {
						$tempValue = 'array(';
						$tmepValueArray = array();
						foreach($value as $key2 => $value2) {
							$tempValueArray[] = '"' . $key2 . '" => "' . $value2 . '"';
						}
						$value = $tempValue . implode(",",$tempValueArray) . ")";
					}
					else { 
						$value = '"' . $value . '"';	
					}
					$keyString = ' "' . $key . '" => ' . $value;
					$tempKeyArray[] = $keyString;
				}
				$string .= implode(",\n\t\t\t\t\t\t\t\t\t\t\t\t\t",$tempKeyArray) . "\n\t\t\t\t\t\t\t\t\t\t\t\t\t)";
				$arrayToTextArray[$listItem][] = $string;
			}
		}
	}
	
?><?= '<?php' ?>

	namespace Skeet\Generated\Model;

	class <?= $classSetupArray["class_name"] ?>Generated extends \Skeet\Model\AbstractModel {
		protected $tableName = '<?= $classSetupArray["table_name"] ?>';
		protected $primaryKey = array("primary_key_field_name" => "<?= $classSetupArray["primary_key"]["field_name"] ?>",
												"primary_key_value" => 0);
	
		protected $dataStructure = array(
													<?= implode(",\n\n\t\t\t\t\t\t\t\t\t\t\t\t\t",$arrayToTextArray["data_structure"]) ?>
													);
	
		protected $collectionDefinitions = array(
															<?= implode(",\n\n\t\t\t\t\t\t\t\t\t\t\t\t\t",$arrayToTextArray["collection_definitions"]) ?>
													);
		
		protected $targetObjectDefinitions = array(
																	<?= implode(",\n\n\t\t\t\t\t\t\t\t\t\t\t\t\t",$arrayToTextArray["target_object_definitions"]) ?>
														);
<?
	if(isset($classSetupArray["display_name"]) && $classSetupArray["display_name"]) {
?>
		public function getDisplayLabel() {
			return $this->get('<?= $classSetupArray["display_name"] ?>');
		}
<?
	}
?>
	}
?>