<?
	$tableDescription = $this->getSetting("table_description");
	
?><?= '<?php' ?>

	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Model;

	class <?= $tableDescription->getClassName() ?> extends \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Model\AbstractModel {
		protected $databaseName = '<?= $tableDescription->getDatabaseName() ?>';
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
		protected $primaryKey = array(
						"primary_key_field_name" => "<?= $tableDescription->getPrimaryKeyFieldName() ?>",
						"primary_key_value" => 0
					);
	
		protected $dataStructure = <?= $tableDescription->arrayToGeneratorText($tableDescription->getFields()) ?>;
	
		protected $collectionDefinitions = <?= $tableDescription->arrayToGeneratorText($tableDescription->getCollections()) ?>;
		
		protected $targetObjectDefinitions = <?= $tableDescription->arrayToGeneratorText($tableDescription->getTargetObjects()) ?>;
<?
	if($tableDescription->getDisplayNameField()) {
?>
		protected $displayNameField = '<?= $tableDescription->getDisplayNameField() ?>';
		
<?
	}
?>
	}
?>