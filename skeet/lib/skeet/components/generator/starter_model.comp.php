<?php $spec = $this->getSetting('spec'); $namespace = $this->getSetting('namespace'); ?>
	declare(strict_types=1);

	namespace <?= $namespace ?>;

	/**
	 * Hand-editable model. Extend/override the generated base here; this file
	 * is not overwritten by the generator.
	 */
	class <?= $spec['className'] ?> extends <?= $spec['className'] ?>Generated {
	}
