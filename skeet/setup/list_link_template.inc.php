<?
	foreach($classArray as $tableName => $classSetupArray) {
?>
	<a href="<?= '<?= ' ?>\Skeet\LinkFactory::getLink('<?= capString($tableName) ?>List')->getLink() ?>"><?= ucwords(str_replace("_"," ",$tableName)) ?></a>
	<br><br>
<?
	}
?>