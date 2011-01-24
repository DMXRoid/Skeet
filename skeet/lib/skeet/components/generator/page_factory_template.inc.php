<?

?><?= '<?php' ?>

	namespace Skeet\Generated\Factory;

	class PageFactoryGenerated {
		public static function getPage($pageName) {
			$pageObject = "";
			switch($pageName) {
				<?
					foreach($classArray as $tableName => $classSetupArray) {
				?>case '<?= str_replace("_"," ",$tableName) ?> details':
					$pageObject = new \Skeet\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableName))) ?>DetailsGeneratedPage();
					break;
					
					case '<?= str_replace("_"," ",$tableName) ?> manage':
					$pageObject = new \Skeet\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableName))) ?>ManageGeneratedPage();
					break;
					
					case '<?= str_replace("_"," ",$tableName) ?> list':
					$pageObject = new \Skeet\Generated\Page\<?= str_replace(" ","",ucwords(str_replace("_"," ",$tableName))) ?>ListGeneratedPage();
					break;
				<?
					}
				?>
			}
			return $pageObject;
		}
	}
?>