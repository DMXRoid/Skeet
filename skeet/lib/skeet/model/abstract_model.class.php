<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2010, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Model;

	use Skeet\DataType;
	use Skeet\Database\AbstractDatabase;

	/**
	 * The base class of all models.
	 *
	 * Field values live in a single backing array ($data); column metadata
	 * (type per column) lives in $columnTypes, which generated subclasses
	 * populate along with typed accessors over $data (DD-001/002). Writes are
	 * tracked in $updatedFields (DD-003) and persisted through the parameterized
	 * database API (DD-008). The database connection is an overridable seam
	 * (DD-007): it defaults to the application's DatabaseFactory but can be
	 * injected for testing or multi-connection use.
	 *
	 * @package Skeet
	 * @subpackage Model
	 */
	abstract class AbstractModel {

		/** The current (calling) namespace of the object, e.g. "\App". */
		protected string $currentNamespace = '';

		/** Named database config this model uses (null = default). */
		protected ?string $databaseName = null;

		/** The table the model maps to. */
		protected string $tableName = '';

		/** The primary key column name. */
		protected string $primaryKeyField = '';

		/**
		 * Column metadata: column name => DataType. This is the authoritative
		 * list of persistable columns; generated subclasses populate it.
		 *
		 * @var array<string,DataType>
		 */
		protected array $columnTypes = [];

		/**
		 * Backing store of column values.
		 *
		 * @var array<string,mixed>
		 */
		protected array $data = [];

		/**
		 * Columns changed since load/save (dirty set), column => value.
		 *
		 * @var array<string,mixed>
		 */
		protected array $updatedFields = [];

		/** The connection seam; resolved lazily when null (DD-007). */
		protected ?AbstractDatabase $db = null;

		/** @var array<string,array<string,mixed>> collection definitions */
		protected array $collectionDefinitions = [];

		/** @var array<string,mixed> cached collection objects */
		protected array $collections = [];

		/** @var array<string,array<string,mixed>> one-to-one target definitions */
		protected array $targetObjectDefinitions = [];

		/** @var array<string,mixed> cached target objects */
		protected array $targetObjects = [];

		/** @var array<string,string> */
		protected array $requiredFields = [];

		/** @var array<string,\Skeet\ValidationType> per-field validation overrides */
		protected array $validationTypes = [];

		/** @var array<string,string> */
		protected array $validationErrors = [];

		/** @var array<string,string> */
		protected array $displayNames = [];

		/** @var array<string,string> */
		protected array $displayErrors = [];

		protected ?string $displayNameField = null;

		/**
		 * @param int|string|null       $primaryKeyId load this row by primary key when given
		 * @param AbstractDatabase|null $db           inject a connection (DD-007); null = resolve lazily
		 */
		public function __construct(int|string|null $primaryKeyId = null, ?AbstractDatabase $db = null) {
			$namespaceArray = explode('\\', get_class($this));
			$this->currentNamespace = '\\' . array_shift($namespaceArray);

			if ($db !== null) {
				$this->db = $db;
			}

			$this->preConstruct();

			// Ensure every known column exists in $data so toArray() is complete.
			foreach (array_keys($this->columnTypes) as $column) {
				if (!array_key_exists($column, $this->data)) {
					$this->data[$column] = null;
				}
			}

			if ($primaryKeyId !== null) {
				$database = $this->getDatabase();
				$sql = 'SELECT * FROM ' . $database->quoteIdentifier($this->tableName)
					. ' WHERE ' . $database->quoteIdentifier($this->primaryKeyField) . ' = ?';
				$row = $database->doQuery($sql, [$primaryKeyId])->getRow();
				if ($row !== false) {
					$this->loadRow($row);
				}
			}

			$this->postConstruct();
		}

		/** Hook run at the front of the constructor. */
		protected function preConstruct(): void {}

		/** Hook run at the end of the constructor. */
		protected function postConstruct(): void {}

		/** Hook run after save(); $isInsert is true for a freshly inserted row. */
		protected function postSave(bool $isInsert = false): void {}

		/**
		 * Populate $data from a DB row without marking fields dirty (loaded
		 * state is clean). Only known columns are taken.
		 *
		 * @param array<string,mixed> $row
		 */
		public function loadRow(array $row): void {
			foreach ($row as $key => $value) {
				if (array_key_exists($key, $this->columnTypes) || $key === $this->primaryKeyField) {
					$this->data[$key] = $value;
				}
			}
		}

		/* -------------------------------------------------- connection seam */

		public function setDatabase(AbstractDatabase $db): void {
			$this->db = $db;
		}

		protected function getDatabase(): AbstractDatabase {
			if ($this->db === null) {
				$factory = $this->resolveClass('DatabaseFactory');
				$this->db = $this->databaseName !== null
					? $factory::getDatabase($this->databaseName)
					: $factory::getDatabase();
			}
			return $this->db;
		}

		/**
		 * Resolve a factory/service class, preferring the application's
		 * namespace and falling back to the framework's.
		 */
		protected function resolveClass(string $shortName): string {
			$appClass = $this->currentNamespace . '\\' . $shortName;
			return class_exists($appClass) ? $appClass : '\\Skeet\\' . $shortName;
		}

		/* -------------------------------------------------- identity */

		public function getPrimaryKeyField(): string {
			return $this->primaryKeyField;
		}

		public function setPrimaryKeyID(int|string $primaryKeyId): void {
			$this->data[$this->primaryKeyField] = $primaryKeyId;
		}

		public function getID(): int|string|null {
			return $this->data[$this->primaryKeyField] ?? null;
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

		/* -------------------------------------------------- values */

		/**
		 * Get a column value. Throws if the column is not part of the model.
		 */
		public function get(string $key): mixed {
			if (!array_key_exists($key, $this->columnTypes)) {
				throw new \Skeet\Exception\InvalidGetCallException($key);
			}
			return $this->data[$key] ?? null;
		}

		/**
		 * Set a column value and mark it dirty. Throws if the column is not
		 * part of the model.
		 */
		public function set(string $key, mixed $value): void {
			if (!array_key_exists($key, $this->columnTypes)) {
				throw new \Skeet\Exception\InvalidSetCallException($key);
			}
			$this->data[$key] = $value;
			$this->updatedFields[$key] = $value;
		}

		/**
		 * Persist changed fields: INSERT a new row or UPDATE the existing one.
		 */
		public function save(): void {
			$db = $this->getDatabase();

			$isInsert = !$this->getID();

			$fields = [];
			foreach ($this->updatedFields as $key => $value) {
				if ($key === $this->primaryKeyField) {
					continue;
				}
				$fields[$key] = $value;
			}

			$this->doApplyTimestamps($fields, $isInsert);

			if (!$isInsert) {
				if ($fields !== []) {
					$db->doUpdate($this->tableName, $fields, [$this->primaryKeyField => $this->getID()]);
				}
			}
			else {
				$newId = $db->doInsert($this->tableName, $fields);
				$this->setPrimaryKeyID($newId);
			}

			$this->updatedFields = [];
			$this->postSave($isInsert);
		}

		/**
		 * When timestamp management is on (config `manage_timestamps`, default
		 * on), stamp the created/updated columns that exist on this model —
		 * created on insert, updated on every write. When off, leave them to the
		 * database (defaults / ON UPDATE).
		 *
		 * @param array<string,mixed> $fields
		 */
		protected function doApplyTimestamps(array &$fields, bool $isInsert): void {
			if (!$this->doesManageTimestamps()) {
				return;
			}
			$now = date('Y-m-d H:i:s');
			$createdField = $this->getCreatedAtField();
			$updatedField = $this->getUpdatedAtField();

			if ($isInsert && array_key_exists($createdField, $this->columnTypes) && !isset($fields[$createdField])) {
				$fields[$createdField] = $now;
				$this->data[$createdField] = $now;
			}
			if (array_key_exists($updatedField, $this->columnTypes)) {
				$fields[$updatedField] = $now;
				$this->data[$updatedField] = $now;
			}
		}

		/* -------------------------------------------------- bulk value access (DD-001/002) */

		/**
		 * All column values. O(1) over the backing array.
		 *
		 * @return array<string,mixed>
		 */
		public function toArray(): array {
			return $this->data;
		}

		/** Alias of {@see toArray()}. @return array<string,mixed> */
		public function getValuesAsArray(): array {
			return $this->data;
		}

		/** @return string[] the model's column names */
		public function getColumns(): array {
			return array_keys($this->columnTypes);
		}

		/* -------------------------------------------------- conventions (config-driven) */

		protected function getConfigValue(string $key): mixed {
			return \Skeet\Skeet::getConfig($key);
		}

		/** Soft-delete column name (config `soft_delete_field`, default `is_retired`). */
		protected function getSoftDeleteField(): string {
			$value = $this->getConfigValue('soft_delete_field');
			return (is_string($value) && $value !== '') ? $value : 'is_retired';
		}

		/** The "active" value for the soft-delete column (config, default 0). */
		protected function getSoftDeleteActiveValue(): mixed {
			$value = $this->getConfigValue('soft_delete_active_value');
			return $value === false ? 0 : $value;
		}

		/** The "retired" value for the soft-delete column (config, default 1). */
		protected function getSoftDeleteRetiredValue(): mixed {
			$value = $this->getConfigValue('soft_delete_retired_value');
			return $value === false ? 1 : $value;
		}

		/** Whether the model manages timestamps (config `manage_timestamps`, default on). */
		protected function doesManageTimestamps(): bool {
			$value = $this->getConfigValue('manage_timestamps');
			return $value === false ? true : filter_var($value, FILTER_VALIDATE_BOOLEAN);
		}

		protected function getCreatedAtField(): string {
			$value = $this->getConfigValue('created_at_field');
			return (is_string($value) && $value !== '') ? $value : 'creation_datetime';
		}

		protected function getUpdatedAtField(): string {
			$value = $this->getConfigValue('updated_at_field');
			return (is_string($value) && $value !== '') ? $value : 'last_modified_date';
		}

		/**
		 * Soft-delete this record: set the soft-delete column to its retired
		 * value and save. Skeet never hard-deletes model rows (DD-019); a no-op
		 * if the model has no soft-delete column.
		 */
		public function doSoftDelete(): void {
			$field = $this->getSoftDeleteField();
			if (array_key_exists($field, $this->columnTypes)) {
				$this->set($field, $this->getSoftDeleteRetiredValue());
				$this->save();
			}
		}

		/**
		 * Diff this model's current values against a previous value array,
		 * returning human-readable "Field: old -> new" lines.
		 *
		 * @param array<string,mixed> $oldValues
		 * @return string[]
		 */
		public function compareValueArray(array $oldValues): array {
			$compareArray = [];
			foreach ($this->data as $fieldName => $newValue) {
				$oldValue = $oldValues[$fieldName] ?? null;
				if ($oldValue != $newValue) {
					$compareArray[] = $this->prettifyFieldName($fieldName) . ': ' . $oldValue . ' -> ' . $newValue;
				}
			}
			return $compareArray;
		}

		public function getHash(): string {
			return sha1(implode('', array_map(static fn($v) => (string) $v, $this->data)));
		}

		/**
		 * Back-compat view of the legacy $dataStructure shape, derived on the
		 * fly from column metadata + values.
		 *
		 * @return array<string,array<string,mixed>>
		 */
		public function getDataStructure(): array {
			$structure = [];
			foreach ($this->columnTypes as $field => $type) {
				$structure[$field] = ['data_type' => $type, 'value' => $this->data[$field] ?? null];
			}
			return $structure;
		}

		/* -------------------------------------------------- validation */

		/**
		 * @param array<string,mixed> $inputArray
		 * @return array<string,string> field => error message
		 */
		public function validateInput(array $inputArray): array {
			$errorMessages = [];
			foreach ($this->getRequiredFields() as $requiredField) {
				if (!isset($inputArray[$requiredField])) {
					$errorMessages[$requiredField] = 'You did not enter a value for ' . $this->getDisplayName($requiredField);
				}
			}
			$validationFactory = $this->resolveClass('ValidationFactory');
			foreach ($inputArray as $key => $value) {
				if (array_key_exists($key, $this->columnTypes)) {
					$validationType = $this->validationTypes[$key] ?? \Skeet\ValidationType::fromDataType($this->columnTypes[$key]);
					if (!$validationFactory::validate($value, $validationType)) {
						$errorMessages[$key] = $this->displayErrors[$key]
							?? ('Invalid input for ' . $this->getDisplayName($key) . '.');
					}
				}
			}
			$this->validationErrors = $errorMessages;
			return $errorMessages;
		}

		public function addRequiredField(string $requiredField): void {
			if (array_key_exists($requiredField, $this->columnTypes)) {
				$this->requiredFields[$requiredField] = $requiredField;
			}
		}

		/** @return array<string,string> */
		public function getRequiredFields(): array {
			return $this->requiredFields;
		}

		/** @return array<string,string> */
		public function getValidationErrors(): array {
			return $this->validationErrors;
		}

		public function getDisplayName(string $field): string {
			if (isset($this->displayNames[$field]) && $this->displayNames[$field] !== '') {
				return $this->displayNames[$field];
			}
			if (str_ends_with($field, '_id')) {
				$field = substr($field, 0, -3);
			}
			return ucwords(str_replace('_', ' ', $field));
		}

		public function getDisplayLabel(): string {
			if ($this->displayNameField !== null && $this->displayNameField !== '') {
				return (string) $this->get($this->displayNameField);
			}
			return '';
		}

		public function getDisplayNameField(): ?string {
			return $this->displayNameField;
		}

		private function prettifyFieldName(string $fieldName): string {
			return ucwords(str_replace('_', ' ', $fieldName));
		}

		/* -------------------------------------------------- relationships */

		/** @return array<string,array<string,mixed>> */
		public function getCollectionDefinitions(): array {
			return $this->collectionDefinitions;
		}

		/** @return array<string,array<string,mixed>> */
		public function getTargetObjectDefinitions(): array {
			return $this->targetObjectDefinitions;
		}

		/**
		 * Get a one-to-one related object via the application's ModelFactory.
		 */
		public function getTargetObject(string $tableName): mixed {
			if (!isset($this->targetObjectDefinitions[$tableName])) {
				throw new \Skeet\Exception\InvalidTargetObjectException("No target-object definition for '{$tableName}' on " . $this->tableName);
			}
			if (!isset($this->targetObjects[$tableName]) || !is_object($this->targetObjects[$tableName])) {
				$modelFactory = $this->resolveClass('ModelFactory');
				$this->targetObjects[$tableName] = $modelFactory::getModel(
					$this->targetObjectDefinitions[$tableName]['table_name'],
					$this->get($this->targetObjectDefinitions[$tableName]['foreign_key_name']),
					$this->getDatabase()
				);
			}
			return $this->targetObjects[$tableName];
		}

		/**
		 * Link a model into a many-to-many collection (join-table row).
		 */
		public function addObjectToCollection(string $tableName, AbstractModel $modelObject): void {
			$definition = $this->requireCollectionDefinition($tableName);
			$db = $this->getDatabase();
			$joinTable = $definition['join_table'];

			$keys = [
				$definition['local_join_key']   => $this->getID(),
				$definition['foreign_join_key'] => $modelObject->getID(),
			];
			$fields = $keys + [$this->getSoftDeleteField() => $this->getSoftDeleteActiveValue()];
			if ($this->doesManageTimestamps()) {
				$fields[$this->getCreatedAtField()] = date('Y-m-d H:i:s');
			}

			$this->getCollection($tableName)->add($modelObject);
			$db->doInsertOrUpdate($joinTable, $fields, $keys);
		}

		/**
		 * Unlink a model from a many-to-many collection (soft delete).
		 */
		public function removeObjectFromCollection(string $tableName, AbstractModel $modelObject): void {
			$definition = $this->requireCollectionDefinition($tableName);
			$db = $this->getDatabase();

			$where = [
				$definition['local_join_key']   => $this->getID(),
				$definition['foreign_join_key'] => $modelObject->getID(),
			];
			$this->getCollection($tableName)->remove($modelObject);
			$db->doUpdate($definition['join_table'], [$this->getSoftDeleteField() => $this->getSoftDeleteRetiredValue()], $where);
		}

		public function setExtraCollectionField(string $tableName, string $fieldName, AbstractModel $modelObject, mixed $value): void {
			$definition = $this->requireCollectionDefinition($tableName);
			if (!isset($definition['extra_fields'][$fieldName])) {
				throw new \Skeet\Exception\InvalidCollectionException("No extra collection field '{$fieldName}' on '{$tableName}'.");
			}
			$this->writeJoinField($definition, $fieldName, $modelObject, $value);
		}

		public function setNonExtraCollectionField(string $tableName, string $fieldName, AbstractModel $modelObject, mixed $value): void {
			$definition = $this->requireCollectionDefinition($tableName);
			$this->writeJoinField($definition, $fieldName, $modelObject, $value);
		}

		/**
		 * @param array<string,mixed> $definition
		 */
		private function writeJoinField(array $definition, string $fieldName, AbstractModel $modelObject, mixed $value): void {
			$db = $this->getDatabase();
			$where = [
				$definition['local_join_key']   => $this->getID(),
				$definition['foreign_join_key'] => $modelObject->getID(),
			];
			$db->doUpdate($definition['join_table'], [$fieldName => $value], $where);
		}

		public function getExtraCollectionField(string $tableName, string $fieldName, AbstractModel $modelObject): mixed {
			$definition = $this->requireCollectionDefinition($tableName);
			if (!isset($definition['extra_fields'][$fieldName])) {
				throw new \Skeet\Exception\InvalidCollectionException("No extra collection field '{$fieldName}' on '{$tableName}'.");
			}
			$db = $this->getDatabase();
			$sql = 'SELECT ' . $db->quoteIdentifier($fieldName)
				. ' FROM ' . $db->quoteIdentifier($definition['join_table'])
				. ' WHERE ' . $db->quoteIdentifier($definition['local_join_key']) . ' = ?'
				. ' AND ' . $db->quoteIdentifier($definition['foreign_join_key']) . ' = ?';
			$row = $db->doQuery($sql, [$this->getID(), $modelObject->getID()])->getRow();
			return $row === false ? false : $row[$fieldName];
		}

		/**
		 * @return array<string,mixed>
		 */
		private function requireCollectionDefinition(string $tableName): array {
			if (!isset($this->collectionDefinitions[$tableName]) || !is_array($this->collectionDefinitions[$tableName])) {
				throw new \Skeet\Exception\InvalidCollectionException("No collection definition for '{$tableName}' on " . $this->tableName);
			}
			return $this->collectionDefinitions[$tableName];
		}

		/**
		 * Get a related collection object via the application's
		 * ModelCollectionFactory, populated by the model-composed query for the
		 * relationship (see checkCollectionOptionArray()).
		 */
		public function getCollection(string $tableName): mixed {
			$this->requireCollectionDefinition($tableName);
			if (!isset($this->collections[$tableName]) || !is_object($this->collections[$tableName])) {
				$this->checkCollection($tableName);
			}
			return $this->collections[$tableName];
		}

		protected function checkCollection(string $tableName): void {
			$collectionFactory = $this->resolveClass('ModelCollectionFactory');
			$this->collections[$tableName] = $collectionFactory::getModelCollection(
				$this->collectionDefinitions[$tableName]['table_name'],
				$this->getCollectionOptionArray($tableName),
				$this->getDatabase()
			);
		}

		/**
		 * @return array<string,mixed>
		 */
		protected function getCollectionOptionArray(string $tableName): array {
			if (!isset($this->collectionDefinitions[$tableName]['collection_options'])
				|| !is_array($this->collectionDefinitions[$tableName]['collection_options'])) {
				$this->checkCollectionOptionArray($tableName);
			}
			return $this->collectionDefinitions[$tableName]['collection_options'];
		}

		/**
		 * Compose the query (SQL + bound params) for a relationship collection
		 * and cache it as the collection's options.
		 *
		 * The model composes this SQL on purpose — the standard one-to-many /
		 * many-to-many cases are the straightforward ones Skeet handles for you
		 * (DD-014). Values are bound (no interpolation) and identifiers go
		 * through the dialect's quoteIdentifier(). For anything beyond the
		 * standard shapes, write SQL and pass it as the collection's `sql`
		 * option directly.
		 */
		protected function checkCollectionOptionArray(string $tableName): void {
			$definition = $this->collectionDefinitions[$tableName];
			$db = $this->getDatabase();
			$table = $db->quoteIdentifier($definition['table_name']);
			$getSoftDeleteField = $this->getSoftDeleteField();
			$activeValue = $this->getSoftDeleteActiveValue();

			$params = [];
			$conditions = [];
			$joinSql = '';
			$distinct = '';

			switch ($definition['collection_type']) {
				case \Skeet\CollectionType::ManyToMany:
					$joinTable = $db->quoteIdentifier($definition['join_table']);
					$distinct = 'DISTINCT ';
					$joinSql = ' INNER JOIN ' . $joinTable
						. ' ON ' . $table . '.' . $db->quoteIdentifier($definition['foreign_key_name'])
						. ' = ' . $joinTable . '.' . $db->quoteIdentifier($definition['foreign_join_key']);
					$conditions[] = $joinTable . '.' . $db->quoteIdentifier($definition['local_join_key']) . ' = ?';
					$params[] = $this->getID();
					$conditions[] = $joinTable . '.' . $db->quoteIdentifier($getSoftDeleteField) . ' = ?';
					$params[] = $activeValue;
					if (empty($definition['show_retired'])) {
						$conditions[] = $table . '.' . $db->quoteIdentifier($getSoftDeleteField) . ' = ?';
						$params[] = $activeValue;
					}
					break;

				case \Skeet\CollectionType::OneToMany:
				default:
					$conditions[] = $table . '.' . $db->quoteIdentifier($definition['foreign_key_name']) . ' = ?';
					$params[] = $this->getID();
					$conditions[] = $table . '.' . $db->quoteIdentifier($getSoftDeleteField) . ' = ?';
					$params[] = $activeValue;
					break;
			}

			// Extra developer-provided equality conditions (keys are raw column refs).
			foreach ($definition['where'] ?? [] as $column => $value) {
				$conditions[] = $column . ' = ?';
				$params[] = $value;
			}

			$sql = 'SELECT ' . $distinct . $table . '.* FROM ' . $table . $joinSql;
			if ($conditions !== []) {
				$sql .= ' WHERE ' . implode(' AND ', $conditions);
			}
			if (isset($definition['order_by'])) {
				$sql .= ' ORDER BY ' . implode(', ', (array) $definition['order_by']);
			}

			$this->collectionDefinitions[$tableName]['collection_options'] = ['sql' => $sql, 'params' => $params];
		}
	}
