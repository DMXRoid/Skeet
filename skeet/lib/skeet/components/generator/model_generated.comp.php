<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); $phpTarget = $this->getSetting('phpTarget'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	class <?= $spec['className'] ?>Generated extends \Skeet\Model\AbstractModel {

		<?= \Skeet\Generator\Generator::columnsConstDecl($phpTarget) ?> = <?= var_export(array_keys($spec['columns']), true) ?>;

		protected string $tableName = <?= var_export($spec['table'], true) ?>;
		protected string $primaryKeyField = <?= var_export($spec['primaryKey'], true) ?>;
		protected ?string $displayNameField = <?= $spec['displayNameField'] === null ? 'null' : var_export($spec['displayNameField'], true) ?>;

		protected array $columnTypes = <?= var_export($spec['columns'], true) ?>;

		protected array $targetObjectDefinitions = <?= var_export($spec['targetObjects'], true) ?>;

		protected array $collectionDefinitions = <?= var_export($spec['collections'], true) ?>;
<?php foreach ($spec['columns'] as $col => $dataType): ?>
<?php $type = \Skeet\Generator\Generator::phpType($dataType); $studly = \Skeet\Generator\Generator::classify($col); $cast = \Skeet\Generator\Generator::castExpr($dataType, '$value'); $key = var_export($col, true); ?>
<?php if ($phpTarget >= 80400): ?>

		public <?= $type ?> $<?= $col ?> {
			get {
				$value = $this->get(<?= $key ?>);
				return $value === null ? null : <?= $cast ?>;
			}
			set (<?= $type ?> $value) {
				$this->set(<?= $key ?>, $value);
			}
		}
<?php else: ?>

		public function get<?= $studly ?>(): <?= $type ?> {
			$value = $this->get(<?= $key ?>);
			return $value === null ? null : <?= $cast ?>;
		}

		public function set<?= $studly ?>(<?= $type ?> $value): void {
			$this->set(<?= $key ?>, $value);
		}
<?php endif; ?>
<?php endforeach; ?>
<?php foreach ($spec['targetObjects'] as $relKey => $def): ?>

		public function get<?= \Skeet\Generator\Generator::classify($relKey) ?>(): mixed {
			return $this->getTargetObject(<?= var_export($relKey, true) ?>);
		}
<?php endforeach; ?>
<?php foreach ($spec['collections'] as $relKey => $def): ?>

		public function get<?= \Skeet\Generator\Generator::classify($relKey) ?>Collection(): mixed {
			return $this->getCollection(<?= var_export($relKey, true) ?>);
		}
<?php endforeach; ?>
	}
