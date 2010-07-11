<?
	$collectionObject = variablizeTableName($tableName) . "Collection";
	$classObject = variablizeTableName($tableName);
?>
<?= '<? ' ?>
$<?= $collectionObject ?> = $this->getPage()->getCollection();
?>
<table width="100%" cellspacing=0 cellpadding=0 border=0 class="sortable adminTable">
	<thead>
		<tr>
			<th>Label</th>
			<th style="text-align: right;">Edit</th>
			<th style="text-align: right;">Delete</th>
		</tr>
	</thead>
	<tbody>
		<?= '<? ' ?>
		while($<?= $classObject ?> = $<?= $collectionObject ?>->getNext()) {
		?>
		<tr>
			<td><?= '<?=' ?>$<?= $classObject ?>->getDisplayLabel() ?></td>
			<td style="text-align: right;"><a href="<?= '<?= ' ?>\Skeet\LinkFactory::getLink('<?= classifyTableName($tableName) ?>Manage')->addLinkArg($<?= $classObject ?>->getPrimaryKeyField(),$<?= $classObject ?>->getID())->getLink() ?>">Edit</a></td>
			<td style="text-align: right;"><a href="<?= '<?= ' ?>\Skeet\LinkFactory::getLink('<?= classifyTableName($tableName) ?>List')->addLinkArg($<?= $classObject ?>->getPrimaryKeyField(),$<?= $classObject ?>->getID())->addLinkArg('do_delete',1)->getLink() ?>">Delete</a></td>
		</tr>
		<?= '<?' ?>
		}
		?>
	</tbody>
</table>