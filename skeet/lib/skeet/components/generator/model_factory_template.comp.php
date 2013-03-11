<?
	$tableDescriptions = $this->getSetting("table_descriptions");
?><?= '<?php' ?>

	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Factory;


	class ModelFactory {
		public static function getModel($tableName,$primaryKeyID,$customSQL=NULL,$dataRow=NULL) {
			$modelObject = "";
			switch($tableName) {
				<?
					foreach($tableDescriptions as $tableDescription) {
				?>case '<?= $tableDescription->getTableName() ?>':
					$modelObject = new \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Model\<?= $tableDescription->getClassName() ?>($primaryKeyID,$customSQL,$dataRow);
					break;
				<?
					}
				?>
			}
			return $modelObject;
		}
	}
?>