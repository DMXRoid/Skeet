<?php
	declare(strict_types=1);

	namespace Skeet\Exception;

	/** The configured database_type has no matching dialect. */
	class UnknownDatabaseTypeException extends DatabaseException {
		protected bool $doEmail = false;
	}
