<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\BackgroundJobs;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCA\SurveyServer\EvaluateStatistics;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IAppConfig;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

class ComputeStatistics extends TimedJob {

	/** @var string */
	protected $table = 'survey_results';

	/** @var IDBConnection */
	private $connection;

	/** @var EvaluateStatistics */
	private EvaluateStatistics $EvaluateStatistics;

	/** @var IAppConfig */
	private $config;

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	public function __construct(
		ITimeFactory       $time,
		IDBConnection      $connection = null,
		IAppConfig         $config = null,
		EvaluateStatistics $EvaluateStatistics = null,
		LoggerInterface    $logger
	) {
		parent::__construct($time);
		$this->logger = $logger;
		$this->connection = $connection ? $connection : \OC::$server->getDatabaseConnection();
		$this->config = $config = $config ? $config : \OC::$server->getConfig();
		$this->EvaluateStatistics = $EvaluateStatistics ? $EvaluateStatistics : new EvaluateStatistics();
		$this->setInterval(60); //todo
	}

	/**
	 * @throws Exception
	 */
	protected function run($argument) {
		// clean old data based on admin setting
		$this->logger->info('cleanup old data');
		$newResult['deleted'] = $this->cleanOldData();

		// store the current date as last update
		$newResult['lastUpdate'] = date("Y/m/d h:i:sa");

		// this is fast, so let's run this always
		$this->logger->info('computing instances');
		$newResult['instances'] = $this->getNumberOfInstances();

		$this->logger->info('computing categories');
		$newResult['categories'] = $this->getStatisticsOfCategories();

		$this->logger->info('computing apps');
		$newResult['apps'] = $this->getApps();

		$this->logger->info('computing max instances');
		$newResult['max'] = $this->getInstanceMaxUser();

		$this->config->setValueString('survey_server', 'evaluated_statistics', json_encode($newResult));
		$this->logger->info('computing done');
	}

	/**
	 * return number of instances stored in the database
	 *
	 * @return int
	 * @throws Exception
	 */
	private function getNumberOfInstances(): int {
		$sql = $this->connection->getQueryBuilder();
		$sql->select($sql->createFunction('COUNT(DISTINCT `source`) as instances'))->from($this->table);
		$statement = $sql->executeQuery();
		$result = $statement->fetch();
		$statement->closeCursor();
		return (int)$result['instances'];
	}

	/**
	 * @throws Exception
	 */
	private function getStatisticsOfCategories(): array {
		$statisticsKeys = $this->getStatisticsKeys();
		$diagramStatistics = $this->getDiagramStatistics($statisticsKeys['diagram']);
		$numericalStatistics = $this->getNumericalStatistics($statisticsKeys['numerical']);

		$result = [];
		foreach ($statisticsKeys['metadata'] as $category => $keys) {
			foreach ($keys as $key => $metadata) {
				if ($metadata['presentation'] === EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) {
					$statistics = $diagramStatistics[$category][$key] ?? [];
				} else {
					$statistics = $numericalStatistics[$category][$key] ?? [
						'average' => 0,
						'max' => null,
						'min' => null,
						'total' => null,
					];
				}

				$result[$category][$key]['statistics'] = $statistics;
				$result[$category][$key]['presentation'] = $metadata['presentation'];
				$result[$category][$key]['description'] = $metadata['description'];
			}
		}

		return $result;
	}

	/**
	 * @return array{metadata: array<string, array<string, array{presentation: string, description: string}>>, diagram: string[], numerical: string[]}
	 * @throws Exception
	 */
	private function getStatisticsKeys(): array {
		$query = $this->connection->getQueryBuilder();
		$cursor = $query->selectDistinct('category', 'key')
						->from($this->table)
						->where($query->expr()->neq('category', $query->createNamedParameter('apps')))
						->executeQuery();
		$rows = $cursor->fetchAll();
		$cursor->closeCursor();

		$metadata = [];
		$diagram = [];
		$numerical = [];
		foreach ($rows as $row) {
			$category = $row['category'];
			$key = $row['key'];

			// we don't evaluate share permissions for now
			if (strpos($key, 'permissions_') === 0) continue;

			$presentationType = $this->EvaluateStatistics->getPresentationType($key);
			switch ($presentationType) {
				case EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM:
				case EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION:
					$metadata[$category][$key] = [
						'presentation' => $presentationType,
						'description' => $this->EvaluateStatistics->getDescription($key),
					];
					break;
				case EvaluateStatistics::PRESENTATION_TYPE_VALUE:
					continue 2;
				default:
					throw new \BadMethodCallException('unknown presentation type: ' . $presentationType);
			}

			if ($presentationType === EvaluateStatistics::PRESENTATION_TYPE_NUMERICAL_EVALUATION) {
				$numerical[$key] = $key;
			} elseif ($presentationType === EvaluateStatistics::PRESENTATION_TYPE_DIAGRAM) {
				$diagram[$key] = $key;
			}
		}

		return [
			'metadata' => $metadata,
			'diagram' => array_values($diagram),
			'numerical' => array_values($numerical),
		];
	}

	/**
	 * @throws Exception
	 */
	private function getDiagramStatistics(array $keys): array {
		if ($keys === []) {
			return [];
		}

		$query = $this->connection->getQueryBuilder();
		$result = $query->select('category', 'key', 'value')
						->selectAlias($query->func()->count('source'), 'count')
						->from($this->table)
						->where($query->expr()->in('key', $query->createNamedParameter($keys, IQueryBuilder::PARAM_STR_ARRAY)))
						->andWhere($query->expr()->neq('category', $query->createNamedParameter('apps')))
						->addGroupBy('category')
						->addGroupBy('key')
						->addGroupBy('value')
						->executeQuery();
		$values = $result->fetchAll();
		$result->closeCursor();

		$statistics = [];
		foreach ($values as $value) {
			$category = $value['category'];
			$key = $value['key'];
			$name = $this->clearValue($category, $key, $value['value']);
			if (isset($statistics[$category][$key][$name])) {
				$statistics[$category][$key][$name] = $statistics[$category][$key][$name] + $value['count'];
			} else {
				$statistics[$category][$key][$name] = $value['count'];
			}
		}

		foreach ($statistics as $category => $keys) {
			foreach ($keys as $key => $keyStatistics) {
				arsort($keyStatistics, SORT_NUMERIC);
				$statistics[$category][$key] = $keyStatistics;
			}
		}

		return $statistics;
	}

	/**
	 * @throws Exception
	 */
	private function getNumericalStatistics(array $keys): array {
		if ($keys === []) {
			return [];
		}

		$query = $this->connection->getQueryBuilder();
		$result = $query->select('category', 'key')
						->selectAlias($query->createFunction('AVG(CAST(`value` AS int))'), 'average')
						->selectAlias($query->createFunction('MAX(CAST(`value` AS int))'), 'max')
						->selectAlias($query->createFunction('MIN(CAST(`value` AS int))'), 'min')
						->selectAlias($query->createFunction('SUM(CAST(`value` AS int))'), 'total')
						->from($this->table)
						->where($query->expr()->in('key', $query->createNamedParameter($keys, IQueryBuilder::PARAM_STR_ARRAY)))
						->andWhere($query->expr()->neq('category', $query->createNamedParameter('apps')))
						->addGroupBy('category')
						->addGroupBy('key')
						->executeQuery();
		$data = $result->fetchAll();
		$result->closeCursor();

		$statistics = [];
		foreach ($data as $row) {
			$category = $row['category'];
			$key = $row['key'];
			$statistics[$category][$key] = [
				'average' => round((float)$row['average'], 2),
				'max' => $row['max'],
				'min' => $row['min'],
				'total' => $row['total'],
			];
		}

		return $statistics;
	}

	private function clearValue($category, $key, $value): string {
		if ($category === 'server' && strpos($key, 'memcache.') === 0) {
			return $value !== '' ? trim($value, '\\') : 'none';
		}

		if ($key === 'version') {
			return $this->clearVersionValue($category, $value);
		}

		if ($key === 'max_execution_time') {
			return $value . 's';
		}

		return (string)$value;
	}

	private function clearVersionValue($category, $value): string {
		$versionAggregation = $this->config->getValueString('survey_server', 'version_aggregation', '25');

		$version = explode('.', $value);
		$majorMinorVersion = $version[0] . '.' . (int)$version[1];

		if ($category === 'server') {
			if ($version[0] < $versionAggregation) {
				// for old versions, we aggregate to minor only
				$version[2] = 'x';
			}
			return $majorMinorVersion . '.' . $version[2];
		}

		if ($category === 'database') {
			return $this->clearDatabaseVersion($version[0], $majorMinorVersion);
		}

		return $majorMinorVersion;
	}

	private function clearDatabaseVersion($majorVersion, $majorMinorVersion): string {
		switch ($majorVersion) {
			case '2':
			case '3':
				return 'SQLite ' . $majorMinorVersion;
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
				return 'MySQL ' . $majorMinorVersion;
			case '10':
			case '11':
				return 'MariaDB ' . $majorMinorVersion;
			default:
				return $majorMinorVersion;
		}
	}


	/**
	 * get statistic of enabled apps
	 *
	 * @return array
	 * @throws Exception
	 */
	private function getApps(): array {
		$query = $this->connection->getQueryBuilder();

		$result = $query->select('key')->selectAlias($query->func()->count('source'), 'count')->from($this->table)
						->where($query->expr()->eq('category', $query->createNamedParameter('apps')))
						->andWhere($query->expr()->neq('value', $query->createNamedParameter('disabled')))
						->addGroupBy('key')->executeQuery();
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

		$statistics = array_filter($statistics, function ($key) use ($apps) {
			return !isset($apps[$key]);
		}, ARRAY_FILTER_USE_KEY);

		arsort($statistics);
		return $statistics;
	}

	private function getInstanceMaxUser() {
		$query = $this->connection->getQueryBuilder();
		$result = $query->select('source')
						->from($this->table)
						->where($query->expr()->eq('category', $query->createNamedParameter('stats')))
						->andWhere($query->expr()->eq('key', $query->createNamedParameter('num_users')))
						->orderBy('value', 'DESC')
						->setMaxResults(5)
						->executeQuery();

		$top5Ids = $result->fetchAll();
		$result->closeCursor();

		return $top5Ids;
	}

	/**
	 * @throws Exception
	 */
	private function cleanOldData() {
		$years = $this->config->getValueString('survey_server', 'deletion_time', '99');
		$timestamp = time(); // Get the current timestamp
		$new_timestamp = strtotime("-$years years", $timestamp);

		$sql = $this->connection->getQueryBuilder();
		$sql->delete($this->table)->where($sql->expr()->lt('timestamp', $sql->createNamedParameter($new_timestamp)));
		return $sql->executeStatement();
	}
}
