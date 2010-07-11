<?= '<?' ?>
	namespace Skeet\Generated\Page;
	class <?= classifyTableName($tableName) ?>ListGeneratedPage extends \Skeet\Page\AbstractCrudCollectionPage {
		protected $pageName = '<?= classifyTableName($tableName) ?>List';
		protected $tableName = '<?= $tableName ?>';
		protected $primaryKey = '<?= $classSetupArray["primary_key"]["field_name"] ?>';
	}
	
?>