<?php
	echo '<?php';
?>
	namespace Skeet\Page;
	class <?= str_replace(" ","",ucwords(str_replace("_"," ",$tableName))) ?>DetailsPage extends \Skeet\Page\AbstractCrudDetailsPage {
		protected $tableName = '<?= $tableName ?>';
		
	}
?>