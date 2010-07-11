<h2><?= capString(($this->getPage()->getTableName())) ?> (<a href="<?= $this->getPage()->getPageLink()->addLinkArg("do_create",1)->getLink() ?>">add</a>)</h2>

<?
	\Skeet\CrudFactory::getCrud($this->getPage()->getTableName(),'list')->render();
?>