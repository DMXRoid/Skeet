<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?php' ?>
	namespace  <?= \Skeet\Skeet::getConfig("application_name") ?>\Model;
	
	class <?= $tableDescription->getClassName() ?> extends \<?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Model\<?= $tableDescription->getClassName() ?> {
	
	}
?>