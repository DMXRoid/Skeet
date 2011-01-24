<?php
	echo '<?php';
?>
	namespace Skeet\Page;
	class <?= str_replace(" ","",ucwords(str_replace("_"," ",$tableName))) ?>ManagePage extends \Skeet\Page\AbstractCrudManagePage {
		protected $tableName = '<?= $tableName ?>';
		
	}
?>