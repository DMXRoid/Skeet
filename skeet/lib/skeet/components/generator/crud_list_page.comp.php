<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class <?= $spec['className'] ?>ListPage extends \Skeet\Page\AbstractCrudCollectionPage {
		protected string $tableName = <?= var_export($spec['table'], true) ?>;
		protected string $primaryKey = <?= var_export($spec['primaryKey'], true) ?>;
		protected string $pageName = <?= var_export($spec['className'] . 'List', true) ?>;
	}
