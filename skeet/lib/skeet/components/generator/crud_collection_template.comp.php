<?
	$tableDescription = $this->getSetting("table_description");
	$collectionName = $tableDescription->getClassName() . "Collection";
	$excludeArray = array("last_modified_date","is_retired");
?>
<?= '<? ' ?>
$<?= $collectionName ?> = $this->getPage()->getCollection();
?>
<div id="main_upper">
	<?= '<?=' ?> $this->getPage()->getPageLink()->getLinkAsForm() ?>
	<ul>
		<li>
			<label>Search</label>
			<input type="text" name="search[terms]" value="<?= '<?=' ?>getRequestValue(array('search','terms')) ?>"/>
		</li>
		<?
			foreach($tableDescription->getTargetObjects() as $targetDescription => $targetObject) {
		?>
		<li>
			<label><?= prettifyFieldName($targetDescription) ?></label>
			<?= '<?=' ?>\<?= \Skeet\Skeet::getConfig("application_name") ?>\ModelCollectionFactory::getModelCollection("<?= $targetObject["table_name"] ?>")->getDropdown("search[<?= $targetObject["foreign_key_name"] ?>]",getRequestValue(array("search","<?= $targetObject["foreign_key_name"] ?>"))) ?>
		</li>
		<?
			}
		?>
		<li>
			<a href="#" class="button"><span>Search</span></a>
		</li>
	</ul>
</div>
<div id="main_lower">
	<a href="<?= '<?=' ?>$this->getPage()->getPageLink()->addLinkArg('do_create',1)->getLink() ?>" class="button"><span>Add A New <?= prettifyFieldName($tableDescription->getTableName()) ?></span></a>
	<table width="100%" cellspacing=0 cellpadding=0 border=0 class="list">
		<thead>
			<tr>
				<th>ID</th>
				<th>Label</th>
				<?
					foreach($tableDescription->getFields() as $fieldName => $fieldArray) {
						if($fieldName != $tableDescription->getPrimaryKeyFieldName() && !in_array($fieldName,$excludeArray)) {
							if(substr($fieldName,-3) == "_id") {
								$fieldName = substr($fieldName,0,strlen($fieldName)-3);
							}
				?>
				<th><?= prettifyFieldName($fieldName) ?></td>
				<?
						}
					}
				?>
				<th style="text-align: right;">VIEW</th>
				<th style="text-align: right;">DELETE</th>
			</tr>
		</thead>
		<tbody>
			<?= '<? ' ?>
			while($<?= $tableDescription->getClassName() ?> = $<?= $collectionName ?>->getNext()) {
			?>
			<tr>
				<td><?= '<?=' ?>$<?= $tableDescription->getClassName() ?>->getID() ?></td>
				<td><?= '<?=' ?>$<?= $tableDescription->getClassName() ?>->getDisplayLabel() ?></td>
				<?
					foreach($tableDescription->getFields() as $fieldName => $fieldArray) {
						if($fieldName != $tableDescription->getPrimaryKeyFieldName() && !in_array($fieldName,$excludeArray)) {
							if(substr($fieldName,-3) == "_id") {
								$fieldName = substr($fieldName,0,strlen($fieldName)-3);
								$output = $tableDescription->getClassName() . '->getTargetObject("' . $fieldName . '")->getDisplayLabel()';
							}
							else {
								$output = $tableDescription->getClassName() . '->get("' . $fieldName . '")';
							}
				?>
				<td><?= '<?=' ?>$<?= $output ?>?></td>
				<?
						}
					}
				?>
				<td style="text-align: right;"><a href="<?= '<?= ' ?><?= \Skeet\Skeet::getConfig("application_name") ?>\LinkFactory::getLink('<?= $tableDescription->getClassName() ?>Manage')->addLinkArg($<?= $tableDescription->getClassName() ?>->getPrimaryKeyField(),$<?= $tableDescription->getClassName() ?>->getID())->getLink() ?>">VIEW</a></td>
				<td style="text-align: right;"><a href="<?= '<?= ' ?>\<?= \Skeet\Skeet::getConfig("application_name") ?>\LinkFactory::getLink('<?= $tableDescription->getClassName() ?>List')->addLinkArg($<?= $tableDescription->getClassName() ?>->getPrimaryKeyField(),$<?= $tableDescription->getClassName() ?>->getID())->addLinkArg('do_delete',1)->getLink() ?>">DELETE</a></td>
			</tr>
			<?= '<?' ?>
			}
			?>
		</tbody>
	</table>
</div>