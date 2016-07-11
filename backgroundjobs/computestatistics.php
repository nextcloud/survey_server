<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
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


namespace OCA\Survery_Server\BackgroundJobs;


use OC\BackgroundJob\TimedJob;
use OCA\Survey_Server\EvaluateStatistics;
use OCP\IConfig;
use OCP\IDBConnection;

class ComputeStatistics extends TimedJob {

	/** @var string	*/
	protected $table = 'survey_results';

	/** @var IDBConnection */
	private $connection;

	/** @var EvaluateStatistics  */
	private $evaluateStatistics;

	/** @var IConfig */
	private $config;

	public function __construct(
		IDBConnection $connection = null,
		IConfig $config = null,
		EvaluateStatistics $evaluateStatistics = null
	) {
		$this->connection = $connection ? $connection : \OC::$server->getDatabaseConnection();
		$this->config = $config = $config ? $config : \OC::$server->getConfig();
		$this->evaluateStatistics = $evaluateStatistics ? $evaluateStatistics : new EvaluateStatistics();
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument) {
		$result = [];
		$result['instances'] = $this->getNumberOfInstances();
		$result['categories'] = $this->getStatisticsOfCategories();
		$result['apps'] = $this->getApps();

		$this->config->setAppValue('survey_server', 'evaluated_statistics', json_encode($result));
	}

	/**
	 * return number of instances stored in the database
	 *
	 * @return int
	 */
	private function getNumberOfInstances() {
		$countInstances = $this->connection->getQueryBuilder();
		$i = $countInstances->select($countInstances->createFunction('COUNT(DISTINCT `source`) as instances'))
			->from($this->table)->execute()->fetch();

		return (int)$i['instances'];
	}

	private function getStatisticsOfCategories() {
		$categories = $this->getCategories();
		$result = [];
		foreach ($categories as $category) {
			if ($category !== 'apps') {
				$keys = $this->getKeysOfCategory($category);
				foreach ($keys as $key) {
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

	private function getStatisticsDiagram($category, $key) {
		$query = $this->connection->getQueryBuilder();

		$result = $query
			->select('value')
			->from($this->table)
			->where($query->expr()->eq('category', $query->createNamedParameter($category)))
			->andWhere($query->expr()->eq('key', $query->createNamedParameter($key)))
			->execute();
		$values = $result->fetchAll();
		$result->closeCursor();

		$statistics = [];
		foreach ($values as $value) {
			if (isset($statistics[$value['value']])) {
				$statistics[$value['value']] = $statistics[$value['value']] + 1;
			} else {
				$statistics[$value['value']] = 1;
			}
		}

		return $statistics;
	}

	private function getNumericalEvaluatedStatistics($category, $key) {

		$query = $this->connection->getQueryBuilder();
		$result = $query
			->select($query->createFunction('AVG(`value`) AS average, MAX(`value`) as max, MIN(`value`) as min'))
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

	/**
	 * get all keys of a given category
	 *
	 * @param string $category
	 * @return array
	 */
	private function getKeysOfCategory($category) {
		$getKeys = $this->connection->getQueryBuilder();
		$getKeys->selectDistinct('key')->from($this->table)
			->where($getKeys->expr()->eq('category', $getKeys->createNamedParameter($category)));
		$result = $getKeys->execute();
		$keys = $result->fetchAll();
		$result->closeCursor();

		return array_map(function($array) { return $array['key']; }, $keys);
	}


	/**
	 * get statistic of enablebled apps
	 *
	 * @return array
	 */
	private function getApps() {
		$query = $this->connection->getQueryBuilder();

		$result = $query
			->select('key')
			->from($this->table)
			->where($query->expr()->eq('category', $query->createNamedParameter('apps')))
			->andWhere($query->expr()->neq('value', $query->createNamedParameter('disabled')))
			->execute();
		$keys = $result->fetchAll();
		$result->closeCursor();

		$statistics = [];
		foreach ($keys as $key) {
			if (isset($statistics[$key['key']])) {
				$statistics[$key['key']] = $statistics[$key['key']] + 1;
			} else {
				$statistics[$key['key']] = 1;
			}
		}

		$max = $statistics['files'];

		foreach ($statistics as $key => $value) {
			$statistics[$key] = 100/$max*$value;
		}

		arsort($statistics);

		return $statistics;
	}

	/**
	 * get statistics how often a specific key was reported for a given category
	 *
	 * @param string $category
	 * @param string $key
	 * @return array
	 */
	private function getGeneralStatistics($category, $key) {
		$query = $this->connection->getQueryBuilder();

		$result = $query
			->select('value')
			->from($this->table)
			->where($query->expr()->eq('category', $query->createNamedParameter($category)))
			->andWhere($query->expr()->eq('key', $query->createNamedParameter($key)))
			->execute();
		$values = $result->fetchAll();
		$result->closeCursor();

		$statistics = [];
		foreach ($values as $value) {
			if (isset($statistics[$key][$value['value']])) {
				$statistics[$key][$value['value']] = $statistics[$key][$value['value']] + 1;
			} else {
				$statistics[$key][$value['value']] = 1;
			}
		}

		return $statistics;
	}

	/**
	 * get all categories
	 *
	 * @return array
	 */
	private function getCategories() {
		$getCategories = $this->connection->getQueryBuilder();
		$getCategories->selectDistinct('category')->from($this->table);
		$result = $getCategories->execute();
		$categories = $result->fetchAll();
		$result->closeCursor();

		return array_map(function($array) { return $array['category']; }, $categories);
	}
}
