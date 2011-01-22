<?
	class ImageFactory {
		public static function getImage($imagePath,$extras="",$altText="",$title="") {
			if(!$altText) {
				$altText = DOMAIN . " - " . PageFactory::getCurrentPage()->getSubTitle();
			}
			
			if(!$title) {
				$title = $altText;
			}
			
			$output = '<img src="' . $imagePath . '" ' . $extras . ' alt="' . $altText . '" title="' . $title . '">';
			return $output;
		}
	}
?>