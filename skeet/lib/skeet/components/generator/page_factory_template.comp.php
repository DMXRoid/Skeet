<?
	$tableDescriptions = $this->getSetting("table_descriptions");
?><?= '<?php' ?>

	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Factory;

	class PageFactoryGenerated {
		public static function getPage($pageName) {
			$pageObject = "";
			switch($pageName) {
				<?
					foreach($tableDescriptions as $tableDescription) {
				?>case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> details':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>DetailsGeneratedPage();
					break;
					
					case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> manage':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>ManageGeneratedPage();
					break;
					
					case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> list':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>ListGeneratedPage();
					break;
				<?
					}
				?>
			}
			return $pageObject;
		}
	}
?>