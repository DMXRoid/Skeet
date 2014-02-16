<?
	$tableDescription = $this->getSetting("table_description");

?><?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_name") ?>\Generated\Page;

	class <?= $tableDescription->getClassName() ?>ManagePage extends \<?= \Skeet\Skeet::getConfig("application_name") ?>\Page\AbstractCrudManagePage {
		protected $pageName = '<?= $tableDescription->getClassName() ?>Manage';
		protected $tableName = '<?= $tableDescription->getTableName() ?>';
		protected $primaryKey = '<?= $tableDescription->getPrimaryKeyFieldName() ?>';
	}

?>