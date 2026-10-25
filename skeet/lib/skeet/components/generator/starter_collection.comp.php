<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	/**
	 * Hand-editable collection. Not overwritten by the generator.
	 */
	class <?= $spec['className'] ?>Collection extends <?= $spec['className'] ?>CollectionGenerated {
	}
