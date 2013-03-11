<?= '<?' ?>
	namespace <?= \Skeet\Skeet::getConfig("application_namespace") ?>;
	
	class ThemeFactory extends \Skeet\ThemeFactory {
		public static function getTheme($themeName) {
			$theme = null;
			switch($themeName) {
				default:
					$theme = new \<?= \Skeet\Skeet::getConfig("application_namespace") ?>\Theme\DefaultTheme();
					break;
			}
			
			if(!is_object($theme)) {
				parent::getTheme($themeName);
			}
			
			return $theme;
		}
	}
?>