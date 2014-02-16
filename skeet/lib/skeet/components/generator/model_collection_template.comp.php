<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Model;
	class <?= $tableDescription->getClassName() ?>Collection extends \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Model\AbstractModelCollection {
		protected $databaseName = '<?= $tableDescription->getDatabaseName() ?>';
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
	}
?>