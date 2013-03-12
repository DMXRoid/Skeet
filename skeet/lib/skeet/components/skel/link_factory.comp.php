<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	class LinkFactory {
		public static function getLink($linkName) {
			$linkObject = null;
			switch($linkName) {
			
			}
			
			if(is_null($linkObject)) {
				$linkObject = parent::getLink($linkName);
			}

			return $linkObject;
		}
	
?>