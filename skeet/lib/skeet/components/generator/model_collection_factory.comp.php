<?php $namespace = $this->getSetting('namespace'); $modelNamespace = $this->getSetting('modelNamespace'); $specs = $this->getSetting('specs'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class ModelCollectionFactory {

		public static function getModelCollection(string $tableName, array $options = [], ?\Skeet\Database\AbstractDatabase $db = null): \Skeet\Model\AbstractModelCollection {
			return match ($tableName) {
<?php foreach ($specs as $table => $spec): ?>
				<?= var_export($table, true) ?> => new \<?= $modelNamespace ?>\<?= $spec['className'] ?>Collection($options, $db),
<?php endforeach; ?>
				default => throw new \Skeet\Exception\UnknownModelException("Unknown collection table: {$tableName}"),
			};
		}
	}
