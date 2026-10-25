<?php
	declare(strict_types=1);

	namespace Skeet\Exception;

	/** A factory was asked for a model/collection table it doesn't know. */
	class UnknownModelException extends AbstractException {
	}
