<?= '<?' ?>
	namespace Skeet\Generated\Page;
	
	class <?= classifyTableName($tableName) ?>ManageGeneratedPage extends \Skeet\Page\AbstractCrudManagePage {
		protected $pageName = '<?= classifyTableName($tableName) ?>Manage'; 
		protected $tableName = '<?= $tableName ?>';
		protected $primaryKey = '<?= $classSetupArray["primary_key"]["field_name"] ?>';
	}

?>