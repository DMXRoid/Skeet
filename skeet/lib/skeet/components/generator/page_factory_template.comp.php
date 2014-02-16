<?
	$tableDescriptions = $this->getSetting("table_descriptions");
?><?= '<?php' ?>

	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Factory;

	class PageFactory extends \Skeet\PageFactory {
		public static function getPage($pageName) {
			$pageObject = null;
			switch($pageName) {
				<?
					foreach($tableDescriptions as $tableDescription) {
				?>case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> details':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>DetailsPage();
					break;
					
					case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> manage':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>ManagePage();
					break;
					
					case '<?= str_replace("_"," ",$tableDescription->getTableName()) ?> list':
					$pageObject = new \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableDescription->getTableName()))) ?>ListPage();
					break;
				<?
					}
				?>
			}
			if(!is_object($pageObject)) {
				$pageObject = parent::getPage($pageName);
			}
			return $pageObject;
		}
	}
?>