<?
	namespace Skeet;
	try {
		require_once(__DIR__ . "/environment.inc.php");
		require_once(__DIR__ . "/lib/skeet/skeet.class.php");
		Skeet::init(CONFIG_NAME);
		print_r(Skeet::$config);
	}
	catch (\Skeet\Exception\AbstractException $e) {
		$e->processException();
	}
?>

