<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Model;

	class <?= $tableDescription->getClassName() ?>Collection extends \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Model\<?= $tableDescription->getClassName() ?>Collection {
	
	}
?>