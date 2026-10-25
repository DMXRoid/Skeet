<?php
	declare(strict_types=1);

	namespace Skeet\Page;

	class AbstractCrudManagePage extends AbstractPage {
		protected string $tableName = '';
		protected string $primaryKey = '';
		protected mixed $crudObject = null;
		protected mixed $specificObject = null;

		public function __construct() {
			parent::__construct();
			$this->specificObject = \Skeet\ModelFactory::getModel($this->getTableName(), \Skeet\Util::getRequestValue($this->primaryKey));

			if (!$this->specificObject->getID()) {
				return;
			}

			if (\Skeet\Util::getRequestValue("do_update")) {
				$this->processInput();
			}
			elseif (\Skeet\Util::getRequestValue("collection_object_add")) {
				$targetObject = \Skeet\ModelFactory::getModel(\Skeet\Util::getRequestValue("collection_object_table"), \Skeet\Util::getRequestValue('target_join_key'));
				$this->getSpecificObject()->addObjectToCollection(\Skeet\Util::getRequestValue("collection_object_table"), $targetObject);
				\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey, $this->getSpecificObject()->getID())->doRedirect();
			}
			elseif (\Skeet\Util::getRequestValue("add_image")) {
				if (isset($_FILES["new_image"]) && file_exists($_FILES["new_image"]["tmp_name"])) {
					$image = \Skeet\ModelFactory::getModel("image");
					$image->set("target_table", $this->getTableName());
					$image->set("target_table_key_id", $this->getSpecificObject()->getID());
					$image->save();
					$image->setImageFromFile($_FILES["new_image"]["tmp_name"]);
				}
			}
			elseif (\Skeet\Util::getRequestValue("collection_object_set_extra_field")) {
				foreach (($_REQUEST["extra_field"] ?? []) as $extraField => $value) {
					$this->specificObject->setExtraCollectionField(
						\Skeet\Util::getRequestValue("collection_object_table"),
						$extraField,
						\Skeet\ModelFactory::getModel(\Skeet\Util::getRequestValue("collection_object_table"), \Skeet\Util::getRequestValue("collection_object_join_key_value")),
						$value
					);
				}
				\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey, $this->getSpecificObject()->getID())->doRedirect();
			}
		}

		public function getTableName(): string {
			return $this->tableName;
		}

		public function getSpecificObject(): mixed {
			return $this->specificObject;
		}

		/**
		 * Build a generic edit form for a model — one input per column (the
		 * primary key becomes a hidden field). Static so it's reusable and
		 * testable without a request; override in a subclass for custom markup.
		 */
		public static function buildForm(\Skeet\Model\AbstractModel $model, string $primaryKeyField): string {
			$html = '<form method="post">' . "\n";
			foreach ($model->getColumns() as $column) {
				if ($column === $primaryKeyField) {
					continue;
				}
				$label = ucwords(str_replace('_', ' ', $column));
				$value = htmlspecialchars((string) $model->get($column));
				$html .= '<label>' . htmlspecialchars($label) . '</label>'
					. '<input name="update_values[' . htmlspecialchars($column) . ']" value="' . $value . '">' . "\n";
			}
			if ($primaryKeyField !== '') {
				$html .= '<input type="hidden" name="' . htmlspecialchars($primaryKeyField) . '" value="'
					. htmlspecialchars((string) $model->getID()) . '">' . "\n";
			}
			$html .= '<input type="submit" name="do_update" value="Save">' . "\n</form>\n";
			return $html;
		}

		public function render(): void {
			if ($this->specificObject instanceof \Skeet\Model\AbstractModel) {
				echo self::buildForm($this->specificObject, $this->primaryKey);
			}
		}

		public function checkInput(): void {
			$this->errorMessages = $this->getSpecificObject()->validateInput(\Skeet\Util::getRequestValue("update_values"));
		}

		public function processInput(): void {
			$this->checkInput();
			if (count($this->getErrorMessages()) === 0) {
				foreach (\Skeet\Util::getRequestValue("update_values") as $key => $value) {
					$this->getSpecificObject()->set($key, $value);
				}
				$this->getSpecificObject()->save();
				\Skeet\LinkFactory::getLink($this->getPageName())->addLinkArg($this->primaryKey, $this->getSpecificObject()->getID())->doRedirect();
			}
		}
	}
