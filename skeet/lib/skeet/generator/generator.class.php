<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Generator;

	use Skeet\DataType;
	use Skeet\CollectionType;
	use Skeet\Database\AbstractDatabase;

	/**
	 * Schema-driven code generator.
	 *
	 * Introspects a database and emits the ORM classes — generated + starter
	 * models and collections, plus the ModelFactory / ModelCollectionFactory —
	 * matching the new AbstractModel/AbstractModelCollection contract. Output is
	 * produced from readable templates under templates/ via output buffering.
	 *
	 * Skeet's conventions are preserved:
	 *  - `snake_case` table  -> StudlyCaps class;
	 *  - a table named `a2b` (both `a` and `b` are tables) is a join table and
	 *    produces a many-to-many collection on each side;
	 *  - a `foo_id` column whose `foo` is a table produces a belongs-to target
	 *    object here and a reciprocal one-to-many collection on `foo`;
	 *  - a `<table>_name` column becomes the display-name field.
	 *
	 * Per DD-009 the model variant is chosen by `phpTarget`: typed property
	 * hooks for >= 8.4, explicit typed get/set methods for 8.1–8.3.
	 *
	 * @package Skeet
	 * @subpackage Generator
	 */
	class Generator {

		private int $phpTarget;
		private string $baseNamespace;
		private string $modelNamespace;
		private string $pageNamespace;

		/**
		 * @param array{namespace?:string,phpTarget?:int} $options
		 */
		public function __construct(private AbstractDatabase $db, array $options = []) {
			$this->baseNamespace = trim((string) ($options['namespace'] ?? 'Application'), '\\');
			$this->modelNamespace = $this->baseNamespace . '\\Model';
			$this->pageNamespace = $this->baseNamespace . '\\Page';
			$this->phpTarget = (int) ($options['phpTarget'] ?? \PHP_VERSION_ID);
		}

		/* -------------------------------------------------- introspection */

		/**
		 * Build the per-table specs, with relationships resolved.
		 *
		 * @return array<string,array<string,mixed>>
		 */
		public function introspect(): array {
			$tables = $this->db->listTables();
			$tableSet = array_flip($tables);

			$specs = [];
			$joinTables = [];
			foreach ($tables as $table) {
				if (str_contains($table, '2')) {
					$parts = explode('2', $table);
					if (count($parts) === 2 && isset($tableSet[$parts[0]]) && isset($tableSet[$parts[1]])) {
						$joinTables[$table] = $parts;
						continue;
					}
				}
				$specs[$table] = [
					'table'            => $table,
					'className'        => self::classify($table),
					'primaryKey'       => 'id',
					'displayNameField' => null,
					'columns'          => [],
					'targetObjects'    => [],
					'collections'      => [],
				];
			}

			// Columns, primary keys, belongs-to + reciprocal one-to-many.
			foreach ($specs as $table => &$spec) {
				foreach ($this->db->describeTable($table) as $column) {
					$name = $column['name'];
					$spec['columns'][$name] = $column['dataType'];
					if ($column['isPrimaryKey']) {
						$spec['primaryKey'] = $name;
					}
					if ($name === $table . '_name') {
						$spec['displayNameField'] = $name;
					}
					if (!$column['isPrimaryKey'] && str_ends_with($name, '_id')) {
						$base = substr($name, 0, -3);
						if (isset($specs[$base])) {
							$spec['targetObjects'][$base] = [
								'table_name'       => $base,
								'foreign_key_name' => $name,
							];
							$specs[$base]['collections'][$table] = [
								'table_name'       => $table,
								'collection_type'  => CollectionType::OneToMany,
								'foreign_key_name' => $name,
								'where'            => [],
							];
						}
					}
				}
			}
			unset($spec);

			// Many-to-many from join tables.
			foreach ($joinTables as $joinTable => [$a, $b]) {
				$extra = $this->joinExtraFields($joinTable, [$a . '_id', $b . '_id', 'is_retired', 'creation_datetime', 'last_modified_date']);
				if (isset($specs[$a])) {
					$specs[$a]['collections'][$b] = $this->manyToMany($specs, $b, $joinTable, $b, $a, $extra);
				}
				if (isset($specs[$b]) && $b !== $a) {
					$specs[$b]['collections'][$a] = $this->manyToMany($specs, $a, $joinTable, $a, $b, $extra);
				}
			}

			ksort($specs);
			return $specs;
		}

		/**
		 * @param array<string,array<string,mixed>> $specs
		 * @param array<string,string>              $extra
		 * @return array<string,mixed>
		 */
		private function manyToMany(array $specs, string $targetTable, string $joinTable, string $targetSide, string $localSide, array $extra): array {
			return [
				'table_name'       => $targetTable,
				'join_table'       => $joinTable,
				'collection_type'  => CollectionType::ManyToMany,
				'foreign_key_name' => $specs[$targetTable]['primaryKey'] ?? 'id',
				'foreign_join_key' => $targetSide . '_id',
				'local_join_key'   => $localSide . '_id',
				'extra_fields'     => $extra,
				'where'            => [],
			];
		}

		/**
		 * @param string[] $skip
		 * @return array<string,string>
		 */
		private function joinExtraFields(string $joinTable, array $skip): array {
			$extra = [];
			foreach ($this->db->describeTable($joinTable) as $column) {
				if (!$column['isPrimaryKey'] && !in_array($column['name'], $skip, true)) {
					$extra[$column['name']] = $column['name'];
				}
			}
			return $extra;
		}

		/* -------------------------------------------------- file generation */

		/**
		 * Generate all files into $outputDir (creating model/ and factory/
		 * subdirectories). Starter classes are not overwritten.
		 *
		 * @return string[] paths written
		 */
		public function generate(string $outputDir): array {
			$specs = $this->introspect();
			$modelDir = $outputDir . '/model';
			$factoryDir = $outputDir . '/factory';
			@mkdir($modelDir, 0777, true);
			@mkdir($factoryDir, 0777, true);

			$written = [];
			foreach ($specs as $table => $spec) {
				$written[] = $this->put($modelDir . '/' . $table . '_generated.class.php', $this->renderGeneratedModel($spec));
				$written[] = $this->put($modelDir . '/' . $table . '_collection_generated.class.php', $this->renderGeneratedCollection($spec));

				$starter = $modelDir . '/' . $table . '.class.php';
				if (!is_file($starter)) {
					$written[] = $this->put($starter, $this->renderStarterModel($spec));
				}
				$starterCollection = $modelDir . '/' . $table . '_collection.class.php';
				if (!is_file($starterCollection)) {
					$written[] = $this->put($starterCollection, $this->renderStarterCollection($spec));
				}
			}

			$written[] = $this->put($factoryDir . '/model_factory.class.php', $this->renderModelFactory($specs));
			$written[] = $this->put($factoryDir . '/model_collection_factory.class.php', $this->renderCollectionFactory($specs));

			return $written;
		}

		private function put(string $path, string $contents): string {
			file_put_contents($path, $contents);
			return $path;
		}

		/**
		 * Generate opt-in CRUD page classes for the named tables (DD-018) —
		 * separate from model generation. Each requested table gets a manage
		 * page and a list page under $outputDir/page/; rendering is inherited
		 * from the typed CRUD base classes.
		 *
		 * @param string[] $tables
		 * @return string[] paths written
		 */
		public function generateCrud(array $tables, string $outputDir): array {
			$specs = $this->introspect();
			$pageDir = $outputDir . '/page';
			@mkdir($pageDir, 0777, true);

			$written = [];
			foreach ($tables as $table) {
				if (!isset($specs[$table])) {
					throw new \Skeet\Exception\UnknownModelException("No such table in schema: {$table}");
				}
				$spec = $specs[$table];
				$written[] = $this->put($pageDir . '/' . $table . '_manage_page.class.php', $this->renderCrudManagePage($spec));
				$written[] = $this->put($pageDir . '/' . $table . '_list_page.class.php', $this->renderCrudListPage($spec));
			}
			return $written;
		}

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderCrudManagePage(array $spec): string {
			return $this->renderTemplate('crud_manage_page', ['namespace' => $this->pageNamespace, 'spec' => $spec]);
		}

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderCrudListPage(array $spec): string {
			return $this->renderTemplate('crud_list_page', ['namespace' => $this->pageNamespace, 'spec' => $spec]);
		}

		/* -------------------------------------------------- rendering (templates) */

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderGeneratedModel(array $spec): string {
			return $this->renderTemplate('model_generated', [
				'namespace' => $this->modelNamespace,
				'phpTarget' => $this->phpTarget,
				'spec'      => $spec,
			]);
		}

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderGeneratedCollection(array $spec): string {
			return $this->renderTemplate('collection_generated', [
				'namespace'      => $this->modelNamespace,
				'modelNamespace' => $this->modelNamespace,
				'spec'           => $spec,
			]);
		}

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderStarterModel(array $spec): string {
			return $this->renderTemplate('starter_model', [
				'namespace' => $this->modelNamespace,
				'spec'      => $spec,
			]);
		}

		/**
		 * @param array<string,mixed> $spec
		 */
		public function renderStarterCollection(array $spec): string {
			return $this->renderTemplate('starter_collection', [
				'namespace' => $this->modelNamespace,
				'spec'      => $spec,
			]);
		}

		/**
		 * @param array<string,array<string,mixed>> $specs
		 */
		public function renderModelFactory(array $specs): string {
			return $this->renderTemplate('model_factory', [
				'namespace'      => $this->baseNamespace,
				'modelNamespace' => $this->modelNamespace,
				'specs'          => $specs,
			]);
		}

		/**
		 * @param array<string,array<string,mixed>> $specs
		 */
		public function renderCollectionFactory(array $specs): string {
			return $this->renderTemplate('model_collection_factory', [
				'namespace'      => $this->baseNamespace,
				'modelNamespace' => $this->modelNamespace,
				'specs'          => $specs,
			]);
		}

		/**
		 * Render a generator component (a .comp.php under components/generator/)
		 * with the given settings, via Skeet's own Component mechanism. The
		 * component starts in output mode and uses <?= ?> / control tags; the
		 * opening PHP tag is prepended here so components don't have to escape it.
		 *
		 * @param array<string,mixed> $settings
		 */
		private function renderTemplate(string $template, array $settings): string {
			$component = new \Skeet\Component\GenericComponent();
			$component->setFilePath(__DIR__ . '/../components/generator/');
			$component->setFileName($template . '.comp.php');
			foreach ($settings as $key => $value) {
				$component->addSetting($key, $value);
			}
			ob_start();
			$component->render();
			return "<?php\n\n" . ob_get_clean();
		}

		/* -------------------------------------------------- helpers (used by templates) */

		public static function classify(string $tableName): string {
			return str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
		}

		public static function variablize(string $tableName): string {
			return lcfirst(self::classify($tableName));
		}

		/** Nullable PHP type for a column's DataType. */
		public static function phpType(DataType $dataType): string {
			return match ($dataType) {
				DataType::Integer, DataType::TinyInt => '?int',
				DataType::Float                      => '?float',
				default                              => '?string',
			};
		}

		/** Cast expression coercing a fetched value to the column's PHP type. */
		public static function castExpr(DataType $dataType, string $valueVar): string {
			return match ($dataType) {
				DataType::Integer, DataType::TinyInt => '(int) ' . $valueVar,
				DataType::Float                      => '(float) ' . $valueVar,
				default                              => '(string) ' . $valueVar,
			};
		}

		/** `const` declaration, typed on PHP >= 8.3. */
		public static function columnsConstDecl(int $phpTarget): string {
			return $phpTarget >= 80300 ? 'public const array COLUMNS' : 'public const COLUMNS';
		}
	}
