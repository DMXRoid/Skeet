<?
	namespace Skeet;
	class LinkFactory {
		public static function getLink($linkName) {
			switch($linkName) {
				default:
					$linkObject = new \Skeet\Link\GenericLink($linkName);
					break;
			}

			return $linkObject;
		}
	}
?>
