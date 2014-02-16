<?
	$tableDescription = $this->getSetting("table_description");
?><?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Page;
	class <?= $tableDescription->getClassName() ?>ListPage extends \<?= \Skeet\Skeet::getConfig("application_name") ?>\Page\AbstractCrudCollectionPage {
		protected $pageName = '<?= $tableDescription->getClassName() ?>List';
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
		protected $primaryKey = '<?= $tableDescription->getPrimaryKeyFieldName() ?>';
	}

?>