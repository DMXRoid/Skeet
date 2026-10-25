<?php
	declare(strict_types=1);

	namespace Skeet\Exception;

	class NoRecipientsSetException extends AbstractException {
		public function __construct(
			string $message = "No recipients are set on the email you've tried to send.",
			int $code = 0,
			?\Throwable $previous = null
		) {
			parent::__construct($message, $code, $previous);
		}
	}
