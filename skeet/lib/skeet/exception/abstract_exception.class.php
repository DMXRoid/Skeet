<?php
	/*
		abstract_exception.class.php

		Don't let the naming fool you.  Exception is the base PHP Exception class,
		AbstractException extends that to allow for all the methods and properties
		that it has while being able to provide a central point of customization for
		all the other exceptions in the framework.

		All Skeet exceptions should extend AbstractException.
	*/

	declare(strict_types=1);

	namespace Skeet\Exception;

	abstract class AbstractException extends \Exception {

		/**
		 * Whether to email this exception when processed. Off by default; turn
		 * on for exceptions worth paging a developer about (e.g. database).
		 */
		protected bool $doEmail = false;

		/** @var string[] recipients for $doEmail notifications (configure per app) */
		protected array $emailList = [];

		public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null) {
			parent::__construct($message, $code, $previous);
		}

		/**
		 * Central handling: optionally email, and render when debugging.
		 */
		public function processException(): void {
			if ($this->doEmail && $this->emailList !== []) {
				$subject = "Exception thrown on " . \Skeet\Skeet::getConfig("hostname");
				$to = implode(",", $this->emailList);
				$text = "An exception was thrown.\nException message: " . $this->getMessage()
					. "\nTrace below:\n" . $this->getTraceAsString();
				mail($to, $subject, $text);
			}

			if (\Skeet\Skeet::getConfig("debug")) {
				$this->renderException();
			}
		}

		/**
		 * Minimal, dependency-free rendering. Apps can override in a subclass or
		 * route to a dedicated error page/component.
		 */
		public function renderException(): void {
			echo "Exception: " . $this->getMessage() . "\n";
			echo $this->getTraceAsString() . "\n";
		}
	}
