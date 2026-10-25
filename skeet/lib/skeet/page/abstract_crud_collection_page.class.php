<?php
	declare(strict_types=1);

	namespace Skeet\Page;

	abstract class AbstractCrudCollectionPage extends AbstractPage {
		protected string $tableName = '';
		protected mixed $collection = null;
		protected string $primaryKey = '';

		public function getCollection(): mixed {
			return $this->collection;
		}

		public function getTableName(): string {
			return $this->tableName;
		}

		/**
		 * Build a generic list of a collection (display labels + a "New" button).
		 * Static so it's reusable and testable without a request; override in a
		 * subclass for custom markup.
		 */
		public static function buildList(\Skeet\Model\AbstractModelCollection $collection): string {
			$html = '<ul>' . "\n";
			$collection->reInit();
			while (($model = $collection->getNext()) !== false) {
				$html .= '<li>' . htmlspecialchars((string) $model->getDisplayLabel()) . '</li>' . "\n";
			}
			$collection->reInit();
			$html .= '</ul>' . "\n";
			$html .= '<form method="post"><input type="submit" name="do_create" value="New"></form>' . "\n";
			return $html;
		}

		public function render(): void {
			if ($this->collection instanceof \Skeet\Model\AbstractModelCollection) {
				echo self::buildList($this->collection);
			}
		}

		public function __construct() {
			parent::__construct();
			$this->collection = \Skeet\ModelCollectionFactory::getModelCollection($this->tableName);

			if (\Skeet\Util::getRequestValue("do_create")) {
				$specificObject = \Skeet\ModelFactory::getModel($this->tableName);
				$specificObject->save();
				\Skeet\LinkFactory::getLink(capString($this->tableName) . 'Manage')
					->addLinkArg($this->primaryKey, $specificObject->getID())
					->doRedirect();
			}

			if (\Skeet\Util::getRequestValue("do_delete")) {
				$specificObject = \Skeet\ModelFactory::getModel($this->tableName, \Skeet\Util::getRequestValue($this->primaryKey));
				$specificObject->doSoftDelete();
				\Skeet\LinkFactory::getLink(capString($this->tableName) . "List")->doRedirect();
			}
		}
	}
