<?

?><?= '<?php' ?>
	namespace Skeet\Generated\Factory;
	
	class ModelCollectionFactoryGenerated {
		public static function getModelCollection($tableName,$definitionArray=array()) {
			$modelCollectionObject = "";
			switch($tableName) {
				<?
					foreach($classArray as $tableName => $classSetupArray) {
				?>case '<?= $tableName ?>':
					$modelCollectionObject = new \Skeet\Model\<?= $classSetupArray["class_name"] ?>Collection($definitionArray);
					break;
				<?
					}
				?>
			}
			return $modelCollectionObject;
		}
	}
?>