<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Model;
	class <?= $tableDescription->getClassName() ?>Collection extends \Skeet\Model\AbstractModelCollection {
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
	}
?>