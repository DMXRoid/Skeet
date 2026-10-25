<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); $modelNamespace = $this->getSetting('modelNamespace'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class <?= $spec['className'] ?>CollectionGenerated extends \Skeet\Model\AbstractModelCollection {

		protected string $tableName = <?= var_export($spec['table'], true) ?>;
		protected ?string $modelClass = \<?= $modelNamespace ?>\<?= $spec['className'] ?>::class;
		protected ?string $nameField = <?= $spec['displayNameField'] === null ? 'null' : var_export($spec['displayNameField'], true) ?>;
	}
