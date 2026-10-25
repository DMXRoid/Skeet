<?php
	namespace Skeet;
	try {
		// Composer's autoloader for third-party libraries and dev tooling, if
		// installed. Skeet's own classes load via Skeet's bespoke autoloader
		// (registered in Skeet::init). The two coexist (see docs/design-decisions.md DD-006).
		$skeetComposerAutoload = __DIR__ . "/../vendor/autoload.php";
		if (is_file($skeetComposerAutoload)) {
			require_once($skeetComposerAutoload);
		}

		require_once(__DIR__ . "/environment.inc.php");
		require_once(__DIR__ . "/lib/skeet/global_functions.inc.php");
		require_once(__DIR__ . "/lib/skeet/skeet.class.php");
		Skeet::init(CONFIG_NAME);
	}
	catch (\Skeet\Exception\AbstractException $e) {
		$e->processException();
	}
?>

