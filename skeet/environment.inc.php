<?php
	/**
	 * @package Skeet
	 * @version 1.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 */

	declare(strict_types=1);

	/*
		environment.inc.php

		Selects the active configuration environment and exposes it as the
		CONFIG_NAME constant (consumed by Skeet::init()).

		The modern default is the APP_ENV environment variable, which works
		uniformly for web requests, CLI scripts and background workers (none of
		which can rely on $_SERVER["SERVER_NAME"]). Falls back to "default".

		If you need host-based selection you can still branch on $_SERVER here
		and define CONFIG_NAME accordingly before it is defined below.
	*/

	if (!defined("CONFIG_NAME")) {
		$appEnv = getenv("APP_ENV");
		define("CONFIG_NAME", ($appEnv !== false && $appEnv !== "") ? $appEnv : "default");
	}
