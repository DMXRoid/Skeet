<?
	$tableDescriptions = $this->getSetting("table_descriptions");
?><?= '<?php' ?>

	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Factory;
	
	class ModelCollectionFactory {
		public static function getModelCollection($tableName,$definitionArray=array()) {
			$modelCollectionObject = "";
			switch($tableName) {
				<?
					foreach($tableDescriptions as $tableDescription) {
				?>case '<?= $tableDescription->getTableName() ?>':
					$modelCollectionObject = new \<?= \Skeet\Skeet::getConfig("application_name") ?>\Model\<?= $tableDescription->getClassName() ?>Collection($definitionArray);
					break;
				<?
					}
				?>
			}
			return $modelCollectionObject;
		}
	}
?>