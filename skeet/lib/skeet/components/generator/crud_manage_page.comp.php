<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class <?= $spec['className'] ?>ManagePage extends \Skeet\Page\AbstractCrudManagePage {
		protected string $tableName = <?= var_export($spec['table'], true) ?>;
		protected string $primaryKey = <?= var_export($spec['primaryKey'], true) ?>;
		protected string $pageName = <?= var_export($spec['className'] . 'Manage', true) ?>;
	}
