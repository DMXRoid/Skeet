<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Model;
	class <?= $tableDescription->getClassName() ?>Collection extends \Skeet\Model\AbstractModelCollection {
		protected $databaseName = '<?= $tableDescription->getDatabaseName() ?>';
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
	}
?>