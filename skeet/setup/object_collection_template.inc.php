<?

?><?= '<?php' ?>
	namespace Skeet\Generated\Model;
	class <?= $classSetupArray["class_name"] ?>CollectionGenerated extends \Skeet\Model\AbstractModelCollection {
		protected $tableName = '<?= $classSetupArray["table_name"] ?>';
<?
	if(isset($classSetupArray["data_structure"][$classSetupArray["table_name"] . '_name'])) {
?>
		protected $nameField = '<?= $tableName . '_name' ?>';
		public function get<?= $className ?>DropDown($name,$selected=NULL,$extras='') {
			$output = '<select name="' . $name . '" ' . $extras . '>' . "\n";
			$output .= '<option value="">-- Select One --</option>' . "\n";
			$this->reInit();
			while($modelObject = $this->getNext()) {
				if($modelObject->getID() == $selected) {
					$checked = "selected";
				}
				else {
					$checked = "";
				}
				$output .= '<option value="' . $modelObject->getID() . '" ' . $checked . '>' . $modelObject->get($this->getTableName() . '_name') . '</option>' . "\n";
			}
			$output .= "</select>";
			return $output;
		}
	<?
	}
?>}
?>