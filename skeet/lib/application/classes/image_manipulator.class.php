<?
  	class ImageManipulator {
		private $sourceImage;
		private $sourceImagePath;
		private $destinationFileName;
		private $destinationFilePath;
		private $destinationWidth;
		private $destinationHeight;
		private $imageInfo = array();
	
		public function __construct($sourceImagePath) {
			$this->sourceImagePath = $sourceImagePath;
			if(file_exists($sourceImagePath)) {
				$this->imageInfo = getimagesize($sourceImagePath);
				$this->createSourceImage();
			}
		}
	
		public function getImageInfo() {
			return $this->imageInfo;
		}
	
		public function getSourceImage() {
			return $this->sourceImage;
		}
	
		public function setOriginalSize() {
			$this->setDestinationWidth($this->imageInfo[0]);
			$this->setDestinationHeight($this->imageInfo[1]);
		}
	
		public function setDestinationFileName($destinationFileName) {
			$this->destinationFileName = $destinationFileName;
		}
	
		public function setDestinationFilePath($destinationFilePath) {
			$this->destinationFilePath = $destinationFilePath;
		}
	
		public function setDestinationWidth($destinationWidth) {
			$this->destinationWidth = $destinationWidth;
		}
	
		public function setDestinationHeight($destinationHeight) {
			$this->destinationHeight = $destinationHeight;
		}
	
		public function createSourceImage() {
			$sip = $this->sourceImagePath;
			switch($this->imageInfo[2]) {
				case 1: // GIF
					$image = imagecreatefromgif($sip);
					break;
	
					case 2: // jpg
					$image = imagecreatefromjpeg($sip);
					break;
	
				case 3:  // png
					$image = imagecreatefrompng($sip);
					break;
	
				default:
					$image = FALSE;
			}
			$this->sourceImage = $image;
		}
	
		public function setDestinationHeightFromWidth() {
			if ($this->sourceImage) {
				$origWidth = $this->imageInfo[0];
				$widthRatio = round(($this->destinationWidth / $origWidth), 2);
				$newHeight = intval($this->imageInfo[1] * $widthRatio);
				$this->setDestinationHeight($newHeight);
			}
		}
	
		public function setDestinationWidthFromHeight() {
			if ($this->sourceImage) {
				$origHeight = $this->imageInfo[1];
				$heightRatio = round(($this->destinationHeight / $origHeight), 2);
				$newWidth = intval($this->imageInfo[0] * $heightRatio);
				$this->setDestinationWidth($newWidth);
			}
		}
	
		public function setMaxDimension($maxDimension) {
			if ($this->sourceImage) {
				$origWidth = $this->imageInfo[0];
				$origHeight = $this->imageInfo[1];
	
				if ($origWidth >= $origHeight) {
					// Set width to $maxDimension, scale height
					$this->setDestinationWidth($maxDimension);
					$this->setDestinationHeightFromWidth();
				} else {
					// Set height to $maxDimension, scale width
	
						$this->setDestinationHeight($maxDimension);
					$this->setDestinationWidthFromHeight();
				}
			}
		}
	
		function centeredCrop() {
			$img = $this->sourceImage;
			$newImage = imagecreatetruecolor($this->imageInfo[0],$this->imageInfo[1]);
			$origWidth = $this->imageInfo[0];
			$origHeight = $this->imageInfo[1];
			if ($origWidth >= $origHeight) {
				$widthDifference = $origWidth - $origHeight;
				$startingX = $widthDifference / 2;
				$xLength = $origWidth - ($widthDifference);
				$startingY = 0;
				$yLength = $origHeight;
				$dimension = $origHeight;
				$this->imageInfo[0] = $origWidth - $widthDifference;
			} 
			else {
				$heightDifference = $origHeight - $origWidth;
				$startingY = $heightDifference / 2;
				$yLength = $origHeight - ($heightDifference);
				$startingX = 0;
				$xLength = $origWidth;
				$dimension = $origWidth;
				$this->imageInfo[1] = $origHeight - $heightDifference;
			}
			if(imagecopyresampled($newImage,$img,0,0,$startingX,$startingY,$dimension,$dimension,$xLength,$yLength)) {
				$this->sourceImage = $newImage;
			}
		}
		
		function cropToRatio($ratio) {
			$img = $this->sourceImage;
			$newImage = imagecreatetruecolor($this->imageInfo[0],$this->imageInfo[1]);
			$origWidth = $this->imageInfo[0];
			$origHeight = $this->imageInfo[1];
			$newHeight = $origWidth / $ratio;
			$newWidth = $origHeight * $ratio;
			$startingX = 0;
			$startingY = 0;
			$xLength = $origWidth;
			$yLength = $origHeight;
			$targetWidth = $origWidth;
			$targetHeight = $origHeight;
			
			if($newHeight >= $origHeight) {
				$widthDifference = $origWidth - $newWidth;
				$startingX = $widthDifference / 2;
				$targetWidth =  $origWidth - $widthDifference;
				$xLength = $newWidth;
			}
			else {
				$heightDifference = $origHeight - $targetHeight;
				$startingY = $heightDifference / 2;
				$targetHeight =  $origHeight - $heightDifference;
				$yLength = $newHeight;
			}
			
			$this->imageInfo[0] = $targetWidth;
			$this->imageInfo[1] = $targetHeight;
			if(imagecopyresampled($newImage,$img,0,0,$startingX,$startingY,$targetWidth,$targetHeight,$xLength,$yLength)) {
				$this->sourceImage = $newImage;
			}
		}
		
		function flipHorizontal() {
			$img = $this->sourceImage;
			$newImage = imagecreatetruecolor($this->destinationWidth,$this->destinationHeight);
			$temp = imagecreatetruecolor($this->imageInfo[0],$this->imageInfo[1]);
			if(imagecopyresampled($temp, $img, 0, 0, ($this->imageInfo[0]-1), 0, $this->imageInfo[0], $this->imageInfo[1], 0-$this->imageInfo[0],  $this->imageInfo[1])) {
				if(imagecopyresampled($newImage,$temp,0,0,0,0,$this->destinationWidth,$this->destinationHeight,$this->imageInfo[0],$this->imageInfo[1])) {
					return $newImage;
				}
			}
			return false;
		}
	
		public function createImage() {
			$img = $this->sourceImage;
			$newImage = imagecreatetruecolor($this->destinationWidth,$this->destinationHeight);
			$origWidth = $this->imageInfo[0];
			$origHeight = $this->imageInfo[1];
	
			if($this->destinationWidth != $origWidth && $this->destinationHeight != $origHeight) {
				if(imagecopyresampled($newImage,$img,0,0,0,0,$this->destinationWidth,$this->destinationHeight,$this->imageInfo[0],$this->imageInfo[1])) {
					return $newImage;
				}
			}
			return $this->sourceImage;
		}
	
		public function writeImage($doFlip=FALSE,$returnImage=FALSE) {
			if($this->sourceImage) {
				if(!$doFlip) {
					$newImage = $this->createImage();
				}
				else{
					$newImage = $this->flipHorizontal();
				}
	
				if($returnImage) {
					return $newImage;
				}
	
				if($newImage) {
					// always create a jpeg
					$fileName = $this->destinationFilePath . $this->destinationFileName . ".jpg";
					imagejpeg($newImage,$fileName);
					//shell_exec('chmod 777 ' . $fileName);
					@chmod($fileName,0777);
				}
			}
		}
	}
?>