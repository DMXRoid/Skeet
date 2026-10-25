<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * The place to get link objects.
	 * @package Skeet
	 * @subpackage Factory
	 */
	class LinkFactory {

		public static function getLink(string $linkName): \Skeet\Link\AbstractLink {
			return match ($linkName) {
				default => new \Skeet\Link\GenericLink($linkName),
			};
		}
	}
