<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Marcel Scherello <survey@scherello.de>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Survey_Server\BackgroundJobs;

use OCA\Analytics\Service\DataloadService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCA\Survey_Server\EvaluateStatistics;
use OCP\DB\Exception;
use OCP\IConfig;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class ComputeStatistics extends TimedJob
{

    /** @var string */
    protected $table = 'survey_results';

    /** @var IDBConnection */
    private $connection;

    /** @var EvaluateStatistics */
    private EvaluateStatistics $evaluateStatistics;

    /** @var IConfig */
    private $config;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    public function __construct(
        ITimeFactory       $time,
        IDBConnection      $connection = null,
        IConfig            $config = null,
        EvaluateStatistics $evaluateStatistics = null,
        LoggerInterface    $logger
    )
    {
        parent::__construct($time);
        $this->logger = $logger;
        $this->connection = $connection ? $connection : \OC::$server->getDatabaseConnection();
        $this->config = $config = $config ? $config : \OC::$server->getConfig();
        $this->evaluateStatistics = $evaluateStatistics ? $evaluateStatistics : new EvaluateStatistics();
        $this->setInterval(60 * 60);
    }

    protected function run($argument)
    {
        // clean old data based on admin setting
        $this->logger->error('cleanup');
        $newResult['deleted'] = $this->cleanOldData();

        // store the current date as last update
        $newResult['lastUpdate'] = date("Y/m/d h:i:sa");

        // this is fast, so let's run this always
        $this->logger->error('instances');
        $newResult['instances'] = $this->getNumberOfInstances();

        $this->logger->error('categories');
        $newResult['categories'] = $this->getStatisticsOfCategories();

        $this->logger->error('apps');
        $newResult['apps'] = $this->getApps();

        $this->config->setAppValue('survey_server', 'evaluated_statistics', json_encode($newResult));
    }

    /**
     * return number of instances stored in the database
     *
     * @return int
     * @throws Exception
     */
    private function getNumberOfInstances(): int
    {
        $sql = $this->connection->getQueryBuilder();
        $sql->select($sql->createFunction('COUNT(DISTINCT `source`) as instances'))
            ->from($this->table);
        $statement = $sql->executeQuery();
        $result = $statement->fetch();
        $statement->closeCursor();
        return (int)$result['instances'];
    }

    private function getStatisticsOfCategories(): array
    {
        $categories = $this->getCategories();
        $result = [];
        foreach ($categories as $category) {
            if ($category !== 'apps') {
                $keys = $this->getKeysOfCategory($category);
                foreach ($keys as $key) {
                    // we don't evaluate share permissions for now
                    if (strpos($key, 'permissions_') === 0) continue;
                    $presentationType = $this->evaluateStatistics->getPresentationType($key);
                    switch ($presentationType) {
                        case EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM:
                            $result[$category][$key]['statistics'] = $this->getStatisticsDiagram($category, $key);
                            $result[$category][$key]['presentation'] = $presentationType;
                            $result[$category][$key]['description'] = $this->evaluateStatistics->getDescription($key);
                            break;
                        case EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION:
                            $result[$category][$key]['statistics'] = $this->getNumericalEvaluatedStatistics($category, $key);
                            $result[$category][$key]['presentation'] = $presentationType;
                            $result[$category][$key]['description'] = $this->evaluateStatistics->getDescription($key);
                            break;
                        case EvaluateStatistics::PRESENTATION_TYPE_VALUE:
                            break;
                        default:
                            throw new \BadMethodCallException('unknown presentation type: ' . $presentationType);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * get all categories
     *
     * @return array
     * @throws Exception
     */
    private function getCategories(): array
    {
        $getCategories = $this->connection->getQueryBuilder();
        $getCategories->selectDistinct('category')->from($this->table);
        $result = $getCategories->execute();
        $categories = $result->fetchAll();
        $result->closeCursor();

        return array_map(function ($array) {
            return $array['category'];
        }, $categories);
    }

    /**
     * get all keys of a given category
     *
     * @param string $category
     * @return array
     * @throws Exception
     */
    private function getKeysOfCategory(string $category): array
    {
        $getKeys = $this->connection->getQueryBuilder();
        $getKeys->selectDistinct('key')
            ->from($this->table)
            ->where($getKeys->expr()->eq('category', $getKeys->createNamedParameter($category)));
        $result = $getKeys->execute();
        $keys = $result->fetchAll();
        $result->closeCursor();

        return array_map(function ($array) {
            return $array['key'];
        }, $keys);
    }

    private function getStatisticsDiagram($category, $key): array
    {
        $query = $this->connection->getQueryBuilder();
        $result = $query
            ->select('value')
            ->selectAlias($query->func()->count('source'), 'count')
            ->from($this->table)
            ->where($query->expr()->eq('category', $query->createNamedParameter($category)))
            ->andWhere($query->expr()->eq('key', $query->createNamedParameter($key)))
            ->addGroupBy('value')
            ->execute();
        $values = $result->fetchAll();
        $result->closeCursor();

        $statistics = [];
        foreach ($values as $value) {
            $name = $this->clearValue($category, $key, $value['value']);
            if (isset($statistics[$name])) {
                $statistics[$name] = $statistics[$name] + $value['count'];
            } else {
                $statistics[$name] = $value['count'];
            }
        }
        arsort($statistics, SORT_NUMERIC);
        return $statistics;
    }

    private function getNumericalEvaluatedStatistics($category, $key)
    {
        $query = $this->connection->getQueryBuilder();
        $result = $query
            ->select($query->createFunction('AVG(CAST(`value` AS int)) AS average, MAX(CAST(`value` AS int)) AS max, MIN(CAST(`value` AS int)) AS min'))
            ->addSelect($query->createFunction('SUM(CAST(`value` AS int)) AS total'))
            ->from($this->table)
            ->where($query->expr()->eq('key', $query->createNamedParameter($key)))
            ->andWhere($query->expr()->eq('category', $query->createNamedParameter($category)))
            ->execute();
        $data = $result->fetchAll();
        $data[0]['average'] = round((float)$data[0]['average'], 2);
        $statistics = $data[0];
        $result->closeCursor();

        return $statistics;
    }

    private function clearValue($category, $key, $value): string
    {
        if (strpos($key, 'memcache.') === 0) {
            return $value !== '' ? trim($value, '\\') : 'none';
        }

        if ($key === 'version') {
            $version = explode('.', $value);
            $majorMinorVersion = $version[0] . '.' . (int)$version[1];

            if ($category === 'server') {
                return $majorMinorVersion . '.' . $version[2];
            }

            if ($category === 'database') {
                switch ($version[0]) {
                    case '2':
                    case '3':
                        return 'SQLite ' . $majorMinorVersion;
                    case '5':
                    case '6':
                        return 'MySQL ' . $majorMinorVersion;
                    case '10':
                    case '11':
                        return 'MariaDB ' . $majorMinorVersion;
                    default:
                        return $majorMinorVersion;
                }
            }

            return $majorMinorVersion;
        }

        if ($key === 'max_execution_time') {
            return $value . 's';
        }

        return (string)$value;
    }


    /**
     * get statistic of enabled apps
     *
     * @return array
     * @throws Exception
     */
    private function getApps(): array
    {
        $query = $this->connection->getQueryBuilder();

        $result = $query
            ->select('key')
            ->selectAlias($query->func()->count('source'), 'count')
            ->from($this->table)
            ->where($query->expr()->eq('category', $query->createNamedParameter('apps')))
            ->andWhere($query->expr()->neq('value', $query->createNamedParameter('disabled')))
            ->addGroupBy('key')
            ->execute();
        $keys = $result->fetchAll();
        $result->closeCursor();

        $statistics = [];
        foreach ($keys as $key) {
            if (isset($statistics[$key['key']])) {
                $statistics[$key['key']] = $statistics[$key['key']] + $key['count'];
            } else {
                $statistics[$key['key']] = $key['count'];
            }
        }

        $apps = \OC::$server->getAppManager()->getAlwaysEnabledApps();
        $apps = array_flip($apps);

        $statistics = array_filter($statistics, function($key) use ($apps) {
            return !isset($apps[$key]);
        }, ARRAY_FILTER_USE_KEY);

        arsort($statistics);
        return $statistics;
    }


    private function cleanOldData()
    {
        $years = $this->config->getAppValue('survey_server', 'deletion_years', '3');
        $timestamp = time(); // Get the current timestamp
        $new_timestamp = strtotime("-$years years", $timestamp);

        $sql = $this->connection->getQueryBuilder();
        $sql->delete($this->table)
            ->where($sql->expr()->lt('timestamp', $sql->createNamedParameter($new_timestamp)));
        $statement = $sql->executeStatement();
        return $statement;
    }
}
