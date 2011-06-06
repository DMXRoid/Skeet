<?
	namespace Skeet;
	try {
		require_once(__DIR__ . "/lib/skeet/skeet.class.php");
		Skeet::init();
		print_r(Skeet::$config);
	}
	catch (\Skeet\Exception\AbstractException $e) {
		$e->processException();
	}
?>

