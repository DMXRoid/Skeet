<?
	/**
	* @package Skeet
	* @subpackage Generator
	* @version 1.0
	* @author Matthew Schiros <schiros@invisihosting.com>
	* @copyright Copyright (c) 2011, Matthew Schiros
	*/

	namespace Skeet\Generator;

	/**
	* MySQL-based code generator
	*/

	class MysqlCodeGenerator extends AbstractCodeGenerator {

		protected function loadTables() {
			$tableArray = array();
			$sql = "SHOW TABLES";
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			while($row = $result->getRow()) {
				$rowKey = array_shift(array_keys($row));
				$tableArray[$row[$rowKey]] = $row[$rowKey];
			}
			$this->tableArray = $tableArray;
		}

		protected function processTable($tableName) {
			$sql = "DESCRIBE " . $tableName;
			$result = \Skeet\DatabaseFactory::getDatabase()->doQuery($sql);
			$manyToManyMatches = array();

			/**
			 * First, we want to check and see if this is a many to many
			 * linking table, or a table that contains actual data, using the
			 * regular expression pattern defined in AbstractCodeGenerator.  The
			 * default pattern is <table_1>2<table_2>, but you can change that to whatever
			 * if you want to sub-class the generator.
			 */


			if(preg_match($this->getManyToManyPattern(),$tableName,$manyToManyMatches)) {
				$tempSourceTableName = $manyToManyMatches[1];
				$tempDestinationTableName = $manyToManyMatches[2];

				/**
				 * Similar to what we do below with target objects, we allow for prefixing
				 * in table names, for example, user2child_user, with both pointing at
				 * the user table.  See below for details on how it checks.  Here we check
				 * and make sure that both tables the linking table describes exist.
				 */


				$tempSourceTableArray = explode("_",$tempSourceTableName);
				$tempDestinationTableArray = explode("_",$tempDestinationTableName);
				$hasFoundSourceTable = false;
				$hasFoundDestinationTable = false;

				while(count($tempSourceTableArray) && !$hasFoundSourceTable) {
					$tempTableName = implode("_",$tempSourceTableArray);
					if($this->hasTable($tempTableName)) {
						$hasFoundSourceTable = true;
						$sourceTableName = $tempTableName;
					}
					else {
						array_shift($tempSourceTableArray);
					}
				}

				while(count($tempDestinationTableArray) && !$hasFoundDestinationTable) {
					$tempTableName = implode("_",$tempDestinationTableArray);
					if($this->hasTable($tempTableName)) {
						$hasFoundDestinationTable = true;
						$destinationTableName = $tempTableName;
					}
					else {
						array_shift($tempDestinationTableArray);
					}
				}
				/**
				 * If they exist, we start processing.
				 */
				if($hasFoundDestinationTable && $hasFoundSourceTable) {
					/**
					 * In the same way that primary keys have to be in the format
					 * <table_name>_id, foreign keys in join tables have to be in the format
					 * <prefix_plus_table_name>_id.
					 */

					$sourceTableJoinKeyName = $tempSourceTableName . "_id";
					$destinationTableJoinKeyName = $tempDestinationTableName . "_id";

					/**
					 * We want to see if there are addition columns in the join table,
					 * for example, a sort order, but we want to exclude columns from the
					 * check.  With creation_datetime, last_modified_date, and is_retired
					 * as required fields on all tables, we add the join keys that we already
					 * know about.  Anything left over is an additional column that we want
					 * to give objects access to.
					 */

					$extraColumnExcludeList = array(
						"creation_datetime",
						"last_modified_date",
						"is_retired",
						$tableName . "_id",
						$sourceTableJoinKeyName,
						$destinationTableJoinKeyName
					);


					$extraColumnArray = array();

					while($row = $result->getRow()) {
						if(!in_array($row["Field"],$extraColumnExcludeList)) {
							$extraColumnArray[$row["Field"]] = $row["Field"];
						}
					}
					
					$sourceTableDescription = $this->getTableDescription($sourceTableName);
					$destinationTableDescription = $this->getTableDescription($destinationTableName);

					$sourceTableDescription->addManyToManyCollection($destinationTableName, $tableName, $tempDestinationTableName, $destinationTableDescription->getPrimaryKeyFieldName(), $destinationTableJoinKeyName, $sourceTableJoinKeyName, $extraColumnArray);
					$destinationTableDescription->addManyToManyCollection($sourceTableName, $tableName, $tempSourceTableName, $sourceTableDescription->getPrimaryKeyFieldName(), $sourceTableJoinKeyName, $destinationTableJoinKeyName, $extraColumnArray);
				}

			}
			else {
				$tableDescription = $this->getTableDescription($tableName);
				while($row = $result->getRow()) {
					$tableDescription->addField($row["Field"],\Skeet\Util::getDatatypeFromSQL($row["Type"]),$row["Default"]);
					if($row["Field"] == $tableName . "_name") {
						$tableDescription->setDisplayNameField($row["Field"]);
					}

					/**
					 * Check to see if the field is the primary key for the table.
					 * If it is, just set that.  If it's not, continue to process
					 * the field and figure out what relationships it has with other
					 * tables and such.
					 */

					if($row["Key"] == "PRI") {
						$tableDescription->setPrimaryKeyFieldName($row["Field"]);
					}
					else {
						/**
						 * Right now, the only thing we care about is if this field
						 * ends with _id, which means that it points to the primary
						 * key of another table.  We could figure it out via foreign
						 * key relationships, but a.) I don't want to require that
						 * people use FK's, because not everyone is great at them, and
						 * b.) because this forces a naming convention that makes a
						 * table structure easy to interpret by looking at it.
						 *
						 * What we're looking for is $tableName_id, ie: user_id.
						 */

						if(substr(strtolower($row["Field"]),-3) == "_id") {
							/**
							 * I've found that, in using Skeet and it's predecessors,
							 * it's necessary to allow for prefixing column names,
							 * either to avoid conflicts with other relationships or
							 * to link tables back onto themselves.  So, what we do is
							 * check to see if the full field matches the primary key of
							 * another table, and if it doesn't, knock off chunks of the
							 * field name delimited by _'s.
							 *
							 * Example:
							 * You have a table called user with a primary key of (of course),
							 * user_id.  You have a field on another table called
							 * baby_eating_user_id.  Here's how it'd check:
							 *
							 * baby_eating_user => fail
							 * eating_user => fail
							 * user => success!
							 *
							 * The prefixes are retained in the collection/target definitions,
							 * so in the example above, it'd be something like:
							 * $otherObject->getTargetObject("baby_eating_user")
							 */

							$tempFieldArray = explode("_",$row["Field"]);
							$tempFieldName = substr_replace($row["Field"],'',-3);
							/* we know that the last element is going to be _id, and we don't want it */
							array_pop($tempFieldArray);
							$hasFoundTable = false;
							while(count($tempFieldArray) && !$hasFoundTable) {
								$tempTableName = implode("_",$tempFieldArray);

								/**
								 * If the table exists in the database...
								 */

								if($this->hasTable($tempTableName)) {
									$hasFoundTable = true;

									/**
									 * Add a target object to this table.
									 */

									$tableDescription->addTargetObject($tempTableName,$tempFieldName,$row["Field"]);

									/**
									 * We want to maintain prefixing in both directions, so we replace instances
									 * of the target table's name with this table's name when describing
									 * the one to many collection.  So, continuing the example above, it'd be:
									 *
									 * $user->getCollection("baby_eating_other_object")
									 */

									$targetName = str_replace($tempTableName,$tableName,$tempFieldName);
									$targetTableDescription = $this->getTableDescription($tempTableName);
									$targetTableDescription->addOneToManyCollection($tableName, $targetName, $row["Field"]);
								}
								/**
								 * Otherwise pop the first element off of the array, and do it again.
								 */
								else {
									array_shift($tempFieldArray);
								}
							}
						}
					}
				}
			}
		}
	}

?>
