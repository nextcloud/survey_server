<?php
/**
 * Survey Server
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the LICENSE.md file.
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 * @copyright 2023 Marcel Scherello
 */
declare(strict_types=1);

namespace OCA\Survey_Server\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version1000Date20230209194213 extends SimpleMigrationStep
{

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
    {
    }

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
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('category', 'string', [
                'notnull' => true,
                'length' => 128,
            ]);
            $table->addColumn('key', 'string', [
                'notnull' => true,
                'length' => 512,
            ]);
            $table->addColumn('value', 'string', [
                'notnull' => true,
                'length' => 1024,
            ]);
            $table->addColumn('source', 'string', [
                'notnull' => true,
                'length' => 512,
            ]);
            $table->addColumn('timestamp', 'integer', [
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['key', 'category'], 'sh_survey_results');
            $table->addIndex(['source'], 'ss_source');
        }
        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
    {
    }
}
