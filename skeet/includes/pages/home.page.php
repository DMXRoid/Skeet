<?
	namespace Skeet;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?= $this->getPageTitle() ?></title>
		<meta name="description" content="<?= $this->getMetaDescription() ?>">
		<meta name="keywords" content="<?= implode(",",$this->getKeywords()) ?>">
		<meta name="ROBOTS" content="<?= implode(",",$this->getRobotsRules()) ?>">
		<?= $this->getCSS() ?>
		<?= $this->getJavascript() ?>
	</head>
	<body>
		<? 
			ComponentFactory::getComponent("header",$this)->render(); 
			ComponentFactory::getComponent("mainbody",$this)->render();
			ComponentFactory::getComponent("footer",$this)->render();
		?>
	</body>
</html>
