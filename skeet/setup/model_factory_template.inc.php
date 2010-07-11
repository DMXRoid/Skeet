<?

?><?= '<?php' ?>

	namespace Skeet\Generated\Factory;

	class ModelFactoryGenerated {
		public static function getModel($tableName,$primaryKeyID,$customSQL=NULL,$dataRow=NULL) {
			$modelObject = "";
			switch($tableName) {
				<?
					foreach($classArray as $tableName => $classSetupArray) {
				?>case '<?= $tableName ?>':
					$modelObject = new \Skeet\Model\<?= $classSetupArray["class_name"] ?>($primaryKeyID,$customSQL,$dataRow);
					break;
				<?
					}
				?>
			}
			return $modelObject;
		}
	}
?>