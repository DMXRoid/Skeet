<?php
	declare(strict_types=1);

	namespace Skeet\Exception;

	/** A ${secret:...} reference could not be resolved by any secret provider. */
	class UnresolvedSecretException extends ConfigException {
	}
