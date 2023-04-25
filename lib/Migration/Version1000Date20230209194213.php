<?php
/**
 * @copyright Copyright (c) 2023, Marcel Scherello <surveyserver@scherello.de>
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace OCA\Survey_Server\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20230209194213 extends SimpleMigrationStep
{

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     * @throws SchemaException
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
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