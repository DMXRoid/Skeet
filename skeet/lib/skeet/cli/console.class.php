<?php
	/**
	 * @package Skeet
	 * @subpackage Cli
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Cli;

	/**
	 * Thin output helper. Streams are injectable so output can be captured in
	 * tests.
	 */
	class Console {

		/** @var resource */
		private $out;

		/** @var resource */
		private $err;

		/**
		 * @param resource|null $out
		 * @param resource|null $err
		 */
		public function __construct($out = null, $err = null) {
			$this->out = $out ?? (defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w'));
			$this->err = $err ?? (defined('STDERR') ? STDERR : fopen('php://stderr', 'w'));
		}

		public function writeln(string $line = ''): void {
			fwrite($this->out, $line . "\n");
		}

		public function error(string $line): void {
			fwrite($this->err, $line . "\n");
		}
	}
