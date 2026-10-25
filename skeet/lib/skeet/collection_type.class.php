<?php
	/**
	 * @package Skeet
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * Relationship cardinality for model collections. Backed by the original
	 * integer values so stored/generated collection definitions still line up.
	 *
	 * @package Skeet
	 */
	enum CollectionType: int {
		case OneToMany = 1;
		case ManyToMany = 2;
	}
