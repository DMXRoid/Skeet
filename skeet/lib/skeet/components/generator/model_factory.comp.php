<?php $namespace = $this->getSetting('namespace'); $modelNamespace = $this->getSetting('modelNamespace'); $specs = $this->getSetting('specs'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class ModelFactory {

		public static function getModel(string $tableName, int|string|null $id = null, ?\Skeet\Database\AbstractDatabase $db = null): \Skeet\Model\AbstractModel {
			return match ($tableName) {
<?php foreach ($specs as $table => $spec): ?>
				<?= var_export($table, true) ?> => new \<?= $modelNamespace ?>\<?= $spec['className'] ?>($id, $db),
<?php endforeach; ?>
				default => throw new \Skeet\Exception\UnknownModelException("Unknown model table: {$tableName}"),
			};
		}
	}
