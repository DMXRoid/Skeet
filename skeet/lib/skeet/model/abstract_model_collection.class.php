<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2010, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Model;

	use Skeet\Database\AbstractDatabase;

	/**
	 * Holds and iterates a collection of models.
	 *
	 * The collection is populated by running a query (SQL + bound params) and
	 * hydrating each row into an instance of $modelClass via loadRow(). The
	 * query itself is composed by the caller (typically AbstractModel for the
	 * standard relationship cases, or hand-written SQL for anything else —
	 * DD-014); this class just runs it and holds the results.
	 *
	 * @package Skeet
	 * @subpackage Model
	 */
	abstract class AbstractModelCollection {

		protected string $currentNamespace = '';

		/** Iteration cursor for hasNext()/getNext(). */
		protected int $counter = 0;

		/** @var array<int,AbstractModel> */
		protected array $collection = [];

		/** @var array<int|string,int|string> primary keys present, for has()/hasID() */
		protected array $cachedPrimaryKeyIDList = [];

		/** The table this collection draws from. */
		protected string $tableName = '';

		/** FQCN of the model class rows are hydrated into (generated subclasses set this). */
		protected ?string $modelClass = null;

		/** Optional column used for default ordering and display. */
		protected ?string $nameField = null;

		/** Named database config (null = default). */
		protected ?string $databaseName = null;

		/** Connection seam (DD-007); resolved lazily when null. */
		protected ?AbstractDatabase $db = null;

		/** Total count ignoring any LIMIT (set when a count query is supplied). */
		protected int $noLimitCount = 0;

		/** The SQL that populated the collection. */
		protected string $initSql = '';

		/**
		 * @param array<string,mixed>   $options sql, params, count_sql, count_params, create_empty
		 * @param AbstractDatabase|null $db      injected connection (DD-007)
		 * @param string|null           $modelClass override the model class to hydrate
		 */
		public function __construct(array $options = [], ?AbstractDatabase $db = null, ?string $modelClass = null) {
			$namespaceArray = explode('\\', get_class($this));
			$this->currentNamespace = '\\' . array_shift($namespaceArray);

			if ($db !== null) {
				$this->db = $db;
			}
			if ($modelClass !== null) {
				$this->modelClass = $modelClass;
			}

			$this->preConstruct();

			if (empty($options['create_empty'])) {
				$this->populate($options);
			}

			$this->postConstruct();
		}

		protected function preConstruct(): void {}
		protected function postConstruct(): void {}

		/**
		 * Run the query and hydrate rows into models.
		 *
		 * @param array<string,mixed> $options
		 */
		protected function populate(array $options): void {
			$db = $this->getDatabase();

			$sql = $options['sql'] ?? ('SELECT ' . $db->quoteIdentifier($this->tableName) . '.* FROM ' . $db->quoteIdentifier($this->tableName));
			$params = $options['params'] ?? [];

			if (!empty($options['count_sql'])) {
				$countRow = $db->doQuery($options['count_sql'], $options['count_params'] ?? [])->getRow();
				$this->noLimitCount = $countRow === false ? 0 : (int) reset($countRow);
			}

			$result = $db->doQuery($sql, $params);
			$this->initSql = $db->getQuery();

			while (($row = $result->getRow()) !== false) {
				$model = $this->hydrate($row);
				$id = $model->getID();
				if ($id !== null) {
					$this->cachedPrimaryKeyIDList[$id] = $id;
				}
				$this->collection[] = $model;
			}

			if ($this->noLimitCount === 0) {
				$this->noLimitCount = count($this->collection);
			}
		}

		/**
		 * @param array<string,mixed> $row
		 */
		protected function hydrate(array $row): AbstractModel {
			if ($this->modelClass === null) {
				throw new \Skeet\Exception\InvalidCollectionException('No $modelClass set on collection ' . static::class);
			}
			$class = $this->modelClass;
			$model = new $class(null, $this->getDatabase());
			$model->loadRow($row);
			return $model;
		}

		protected function getDatabase(): AbstractDatabase {
			if ($this->db === null) {
				$appClass = $this->currentNamespace . '\\DatabaseFactory';
				$factory = class_exists($appClass) ? $appClass : '\\Skeet\\DatabaseFactory';
				$this->db = $this->databaseName !== null
					? $factory::getDatabase($this->databaseName)
					: $factory::getDatabase();
			}
			return $this->db;
		}

		public function getCurrentNamespace(): string {
			return $this->currentNamespace;
		}

		public function setCurrentNamespace(string $currentNamespace): void {
			$this->currentNamespace = $currentNamespace;
		}

		public function getTableName(): string {
			return $this->tableName;
		}

		public function getDatabaseName(): ?string {
			return $this->databaseName;
		}

		public function getInitSql(): string {
			return $this->initSql;
		}

		/* -------------------------------------------------- iteration */

		public function hasNext(): bool {
			return isset($this->collection[$this->counter]);
		}

		public function getCounter(): int {
			return $this->counter;
		}

		public function getNext(): AbstractModel|false {
			if ($this->hasNext()) {
				return $this->collection[$this->counter++];
			}
			return false;
		}

		public function reInit(): void {
			$this->counter = 0;
		}

		public function getCount(): int {
			return count($this->collection);
		}

		/** @return array<int,AbstractModel> */
		public function getCollection(): array {
			return $this->collection;
		}

		public function getFirst(): ?AbstractModel {
			return $this->collection[0] ?? null;
		}

		public function getLast(): ?AbstractModel {
			if ($this->collection === []) {
				return null;
			}
			return $this->collection[count($this->collection) - 1];
		}

		public function has(AbstractModel $model): bool {
			return $this->hasID($model->getID());
		}

		public function hasID(int|string|null $id): bool {
			return $id !== null && isset($this->cachedPrimaryKeyIDList[$id]);
		}

		public function add(AbstractModel $object): void {
			$this->collection[] = $object;
			$id = $object->getID();
			if ($id !== null) {
				$this->cachedPrimaryKeyIDList[$id] = $id;
			}
		}

		public function remove(AbstractModel $object): void {
			foreach ($this->collection as $key => $tempObject) {
				if ($tempObject->getID() == $object->getID()) {
					unset($this->collection[$key]);
				}
			}
			$this->collection = array_values($this->collection);
			unset($this->cachedPrimaryKeyIDList[$object->getID()]);
			$this->reInit();
		}

		/* -------------------------------------------------- views */

		public function getDropdown(string $name, int|string|array|null $selected = null, string $extras = '', bool $skipLabel = false): string {
			$this->reInit();
			$output = '<select name="' . $name . '" ' . $extras . '>' . "\n";
			if (!$skipLabel) {
				$output .= '<option value="">--- Select One ---</option>' . "\n";
			}
			while (($model = $this->getNext()) !== false) {
				$id = $model->getID();
				$isSelected = ($id == $selected || (is_array($selected) && in_array($id, $selected)))
					? ' selected '
					: '';
				$output .= '<option value="' . $id . '"' . $isSelected . '>' . $model->getDisplayLabel() . '</option>' . "\n";
			}
			$output .= '</select>' . "\n";
			$this->reInit();
			return $output;
		}

		/**
		 * id => display label map.
		 *
		 * @return array<int|string,string>
		 */
		public function toArray(): array {
			$returnArray = [];
			foreach ($this->collection as $model) {
				$returnArray[$model->getID()] = $model->getDisplayLabel();
			}
			return $returnArray;
		}

		public function getNoLimitCount(): int {
			return $this->noLimitCount;
		}
	}
