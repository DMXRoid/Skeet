<?
	$tableDescription = $this->getSetting("table_description");
	$excludeFields = array("creation_datetime","last_modified_date",$tableDescription->getPrimaryKeyFieldName());
	$classObject = lcfirst($tableDescription->getClassName());
?><?= '<?' ?>
	$<?= $classObject ?> = $this->getPage()->getSpecificObject();
?>

<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm() ?>
	<input type="hidden" name="<?= $tableDescription->getPrimaryKeyFieldName() ?>" value="<?= '<?=' ?>$<?= $classObject ?>->getID() ?>">
	<input type="hidden" name="do_update" value="1">
<?
	$x = 0;
	$fieldCount = count($tableDescription->getFields());
	foreach($tableDescription->getFields() as $fieldName => $fieldArray) {
		if(!in_array($fieldName,$excludeFields)) {
	?>
	<div>
		<label class="<?= '<?= ' ?>$this->getPage()->getErrorClass('<?= $fieldName ?>') ?>"><?= '<?= ' ?> $<?= $classObject ?>->getDisplayName('<?= $fieldName ?>') ?></label>
<?
			if(!$tableDescription->hasTargetObjectField($fieldName)) {
				switch($fieldArray["data_type"]) {

					case \Skeet\Util::DATA_TYPE_TEXT:
				?>
		<textarea name="update_values[<?= $fieldName ?>]"><?= '<?=' ?>$<?= $classObject ?>->get('<?= $fieldName ?>') ?></textarea>
				<?
						break;

					case \Skeet\Util::DATA_TYPE_TINYINT:
				?>
		<input type="checkbox" name="update_values[<?= $fieldName ?>]" value="1" <?= '<?=' ?>($<?= $classObject ?>->get('<?= $fieldName ?>')) ? 'checked' : '' ?>>
				<?
						break;


					case \Skeet\Util::DATA_TYPE_STRING:
					case \Skeet\Util::DATA_TYPE_DATETIME:
					default:
				?>
		<input type="text" name="update_values[<?= $fieldName ?>]" value="<?= '<?=' ?>$<?= $classObject ?>->get('<?= $fieldName ?>') ?>">
				<?
						break;

			}
		}
		else {

			?>
		<?= '<?= ' ?>\<?= \Skeet\Skeet::getConfig("application_name") ?>\ModelCollectionFactory::getModelCollection('<?= $tableDescription->getTargetObjectByField($fieldName) ?>')->getDropdown('update_values[<?= $fieldName ?>]',$<?= $classObject ?>->get('<?= $fieldName ?>')) ?>
			<?
		}
			?>

</div>
	<?
		}
	}
?>

	<input type="submit" value="Save">
</form>
<br><br>
<?
	if(count($tableDescription->getManyToManyCollections())) {


	foreach($tableDescription->getManyToManyCollections() as $foreignDescription => $collectionArray) {
			$tempCollectionName = 'temp' . $collectionArray["class_name"] . "Collection";
			$tempObjectName = 'temp' . $collectionArray["class_name"];
?>
<h2><?= ucwords(str_replace("_"," ",$collectionArray["table_name"])) ?></h2>
<?= '<?' ?>
	$temp<?= $collectionArray["table_name"] ?>Collection = \<?= \Skeet\Skeet::getConfig("application_name") ?>\ModelCollectionFactory::getModelCollection('<?= $collectionArray["table_name"] ?>');
	$<?= $tempCollectionName ?> = $<?= $classObject ?>->getCollection('<?= $collectionArray["table_name"] ?>');
?>
<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm() ?>
<input type="hidden" name="collection_object_add" value="1">
<input type="hidden" name="collection_object_table" value="<?= $collectionArray["table_name"] ?>">
<input type="hidden" name="collection_object_join_key" value="<?= $collectionArray["foreign_join_key"] ?>">
Add <?= ucwords(str_replace("_"," ",$collectionArray["table_name"])) ?>: <?= '<?=' ?> $temp<?= $collectionArray["table_name"] ?>Collection->getDropdown('foreign_join_key') ?>
&nbsp;&nbsp; <input type="submit" value="Add">
</form>

<table width="100%" cellspacing=0 cellpadding=0 border=0 class="">
	<thead>
		<tr>
			<th>Label</th>
			<?
				$hasExtra = false;
				if(isset($collectionArray["extra_fields"])) {
					$hasExtra = true;
					foreach($collectionArray["extra_fields"] as $extraField) {
			?>
			<th><?= ucwords(str_replace("_"," ",$extraField)) ?></th>
			<?
					}
				}
				if($hasExtra) {
			?>
			<th style="text-align: right;">Update</th>
			<?
				}
			?>
			<th style="text-align: right;">Edit</th>
			<th style="text-align: right;">Delete</th>
		</tr>
	</thead>
	<tbody>
	<?= '<?' ?>
		while($<?= $tempObjectName ?> = $<?= $tempCollectionName ?>->getNext()) {
	?>
		<tr>
			<td>
				<?
					if($hasExtra) {
				?>
				<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm() ?>
				<input type="hidden" name="collection_object_set_extra_field" value="1">
				<input type="hidden" name="collection_object_table" value="<?= $collectionArray["table_name"] ?>">
				<input type="hidden" name="collection_object_join_key" value="<?= $collectionArray["foreign_join_key"] ?>">
				<input type="hidden" name="collection_object_join_key_value" value="<?= '<?= ' ?>$<?= $tempObjectName ?>->getID() ?>">
				<?
					}
				?>
				<?= '<?=' ?>$<?= $tempObjectName ?>->getDisplayLabel() ?>
			</td>
			<?
				if(isset($collectionArray["extra_fields"])) {
					foreach($collectionArray["extra_fields"] as $extraField) {
			?><td style="text-align: center;">
			<?
				$isCollection = false;
				if(substr($extraField,-3) == "_id") {
					$extraTableName = substr_replace($extraField,'',-3);
					if($this->getSetting("generator")->hasTable($extraTableName)) {
						$isCollection = true;
			?>
			<?= '<? ' ?>
				$optionCollection = \<?= \Skeet\Skeet::getConfig("application_name") ?>\ModelCollectionFactory::getModelCollection('<?= $extraTableName ?>');
				echo $optionCollection->getDropdown('extra_field[<?= $extraField ?>]',$<?= $classObject ?>->getExtraCollectionField('<?= $collectionArray["table_name"] ?>','<?= $extraField ?>',$<?= $tempObjectName ?>));
				?>
			<?
					}
				}
				if(!$isCollection) {
			?>
				<input type="text" name="extra_field[<?= $extraField ?>]" value="<?= '<?=' ?>$<?= $classObject ?>->getExtraCollectionField('<?= $collectionArray["table_name"] ?>','<?= $extraField ?>',$<?= $tempObjectName ?>) ?>" size="5">
			<? } ?>

			</td>

			<?
					}
				}

				if($hasExtra) {
				?>
			<td style="text-align: right;"><input type="submit" value="Update"></form></td>

				<?
					}
				?>
			<td style="text-align: right;"><a href="<?= '<?= ' ?>\<?= \Skeet\Skeet::getConfig("application_name") ?>\LinkFactory::getLink('<?= $collectionArray["class_name"] . "Manage" ?>')->addLinkArg($<?= $tempObjectName ?>->getPrimaryKeyField(),$<?= $tempObjectName ?>->getID())->getLink() ?>">Edit</a></td>
			<td style="text-align: right;">
				<a href="">Delete</a>

			</td>
		</tr>
	<?= '<?' ?>
		}
	?>
	</tbody>
</table>
<?

		}
	}
?>
<?= '<?' ?>
	\edi\ComponentFactory::getComponent("utils",$this->getPage())->addSetting("target_object",$this->getPage()->getSpecificObject())->render();
?>