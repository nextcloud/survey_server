<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\SurveyServer\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20230209194213 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 * @throws SchemaException
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('survey_results')) {
			$table = $schema->createTable('survey_results');
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('category', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('key', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('value', Types::STRING, [
				'notnull' => true,
				'length' => 1024,
			]);
			$table->addColumn('source', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('timestamp', Types::INTEGER, [
				'notnull' => true,
				'default' => 0
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['key', 'category'], 'ss_survey_results');
			$table->addIndex(['source'], 'ss_source');
			$table->addIndex(['timestamp'], 'ss_timestamp');
		}
		return $schema;
	}
}