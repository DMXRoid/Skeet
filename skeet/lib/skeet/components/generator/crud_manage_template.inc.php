<?
	$excludeFields = array("creation_datetime","last_modified_date",$classSetupArray["primary_key"]["field_name"]);
	$classObject = variablizeTableName($tableName); 
?><?= '<?' ?> 
	$<?= $classObject ?> = $this->getPage()->getSpecificObject();
?>

<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm() ?>
	<input type="hidden" name="<?= $classSetupArray["primary_key"]["field_name"] ?>" value="<?= '<?=' ?>$<?= $classObject ?>->getID() ?>">
	<input type="hidden" name="do_update" value="1">
<?
	$x = 0;
	$fieldCount = count($classSetupArray["data_structure"]);
	foreach($classSetupArray["data_structure"] as $fieldName => $value) {
		if(!in_array($fieldName,$excludeFields)) {
	?>
	<div>
		<label class="<?= '<?= ' ?>$this->getPage()->getErrorClass('<?= $fieldName ?>') ?>"><?= '<?= ' ?> $<?= $classObject ?>->getDisplayName('<?= $fieldName ?>') ?></label>
<?
			if(!isset($classSetupArray["target_object_fields"][$fieldName])) {
				switch($value["data_type"]) {
					
					case DATA_TYPE_TEXT:
				?>
		<textarea name="update_values[<?= $fieldName ?>]"><?= '<?=' ?>$<?= $classObject ?>->get('<?= $fieldName ?>') ?></textarea>
				<?
						break;
					
					case DATA_TYPE_TINYINT:
				?>
		<input type="checkbox" name="update_values[<?= $fieldName ?>]" value="1" <?= '<?=' ?>($<?= $classObject ?>->get('<?= $fieldName ?>')) ? 'checked' : '' ?>>
				<?
						break;
				
				
					case DATA_TYPE_STRING:
					case DATA_TYPE_DATETIME:
					default:
				?>
		<input type="text" name="update_values[<?= $fieldName ?>]" value="<?= '<?=' ?>$<?= $classObject ?>->get('<?= $fieldName ?>') ?>">
				<?
						break;
					
			}
		}
		else {
			
			?>
		<?= '<?= ' ?>\Skeet\ModelCollectionFactory::getModelCollection('<?= $classSetupArray["target_object_fields"][$fieldName] ?>')->getDropdown('update_values[<?= $fieldName ?>]',$<?= $classObject ?>->get('<?= $fieldName ?>')) ?>
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
	if(isset($classSetupArray["collection_definitions"])) {
	foreach($classSetupArray["collection_definitions"] as $collectionTable => $collectionArray) {
		if($collectionArray["collection_type"] == COLLECTION_TYPE_MANY_TO_MANY) {
			$tempCollectionName = 'temp' . classifyTableName($collectionArray["table_name"]) . "Collection";
			$tempObjectName = 'temp' . classifyTableName($collectionArray["table_name"]);
?>
<h2><?= ucwords(str_replace("_"," ",$collectionArray["table_name"])) ?></h2>
<?= '<?' ?>
	$temp<?= $collectionArray["table_name"] ?>Collection = \Skeet\ModelCollectionFactory::getModelCollection('<?= $collectionArray["table_name"] ?>');
	$<?= $tempCollectionName ?> = $<?= $classObject ?>->getCollection('<?= $collectionArray["table_name"] ?>');
?>
<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm() ?>
<input type="hidden" name="collection_object_add" value="1">
<input type="hidden" name="collection_object_table" value="<?= $collectionArray["table_name"] ?>">
<input type="hidden" name="collection_object_join_key" value="<?= $collectionArray["target_join_key"] ?>">
Add <?= ucwords(str_replace("_"," ",$collectionArray["table_name"])) ?>: <?= '<?=' ?> $temp<?= $collectionArray["table_name"] ?>Collection->getDropdown('target_join_key') ?>
&nbsp;&nbsp; <input type="submit" value="Add">
</form>

<table width="100%" cellspacing=0 cellpadding=0 border=0 class="sortable adminTable">
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
				<input type="hidden" name="collection_object_join_key" value="<?= $collectionArray["target_join_key"] ?>">
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
					if(isset($classArray[$extraTableName])) {
						$isCollection = true;
			?>
			<?= '<? ' ?>
				$optionCollection = \Skeet\ModelCollectionFactory::getModelCollection('<?= $extraTableName ?>');
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
			<td style="text-align: right;"><a href="<?= '<?= ' ?>\Skeet\LinkFactory::getLink('<?= classifyTableName($collectionArray["table_name"]) . "Manage" ?>')->addLinkArg($<?= $tempObjectName ?>->getPrimaryKeyField(),$<?= $tempObjectName ?>->getID())->getLink() ?>">Edit</a></td>
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
	}
?>
<h2>Images</h2>
<?= '<?=' ?>$this->getPage()->getPageLink()->getLinkAsForm(null,true) ?>
<input type="hidden" name="add_image" value="1">
<input type="file" name="new_image">
&nbsp;&nbsp;<input type="submit" value="Upload">
<table width="100%" cellspacing=0 cellpadding=0 border=0>
	<tr>
<?= '<?' ?>
	$x = 0;
	while($image = $<?= $classObject ?>->getImages()->getNext()) {
?>
		<td>
			<a href="#model_image_<?= '<?= ' ?>$image->getID() ?>" class="zoomable"><img src="<?= '<?=' ?> $image->getImage() ?>"></a>
			<div id="model_image_<?= '<?= ' ?>$image->getID() ?>"><img src="<?= '<?=' ?>$image->getImage('XL') ?>"></div>
		</td>
<?= '<?' ?>
		$x++;
	}
?>
	</tr>
</table>
