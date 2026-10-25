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
	 * A factory for page objects. The routing switch is filled in per-app by the
	 * generated page factory (DD-018); this base resolves nothing on its own.
	 *
	 * @package Skeet
	 * @subpackage Factory
	 */
	class PageFactory {

		/** The current page, cached. */
		public static ?\Skeet\Page\AbstractPage $currentPage = null;

		public static function getCurrentPage(): ?\Skeet\Page\AbstractPage {
			return self::$currentPage;
		}

		public static function getPage(string $pageName): ?\Skeet\Page\AbstractPage {
			$pageObject = null;
			switch ($pageName) {
				// filled in by the application's generated page factory
			}
			self::$currentPage = $pageObject;
			return $pageObject;
		}
	}
