<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	
	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Model;

	class <?= $tableDescription->getClassName() ?>Collection extends \<?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Model\<?= $tableCollection->getClassName() ?>Collection {
	
	}
?>