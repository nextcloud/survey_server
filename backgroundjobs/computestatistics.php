<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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


namespace OCA\PopularityContestServer\BackgroundJobs;


use OC\BackgroundJob\TimedJob;
use OCP\IConfig;
use OCP\IDBConnection;

class ComputeStatistics extends TimedJob {

	/** @var string	*/
	protected $table = 'popularity_contest';

	/** @var IDBConnection */
	private $connection;

	/** @var IConfig */
	private $config;

	public function __construct(IDBConnection $connection = null, IConfig $config = null) {
		$this->connection = $connection ? $connection : \OC::$server->getDatabaseConnection();
		$this->config = $config = $config ? $config : \OC::$server->getConfig();
		$this->setInterval(24 * 60 * 60);
	}

	protected function run($argument) {
		$result = [];
		$result['stats'] = $this->getSystemStatistics();
		$result['instances'] = $this->getNumberOfInstances();
		$result['apps'] = $this->getApps();
		$result['appStatistics'] = $this->getAppStatistics();

		$this->config->setAppValue('popularitycontestserver', 'evaluated_statistics', json_encode($result));
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

	private function getSystemStatistics() {

		$statistics = [];

		$keys = $this->getKeysOfCategory('stats');

		$query = $this->connection->getQueryBuilder();
		foreach ($keys as $key) {
			$result = $query
				->select($query->createFunction('AVG(`value`) AS average, MAX(`value`) as max, MIN(`value`) as min'))
				->from($this->table)
				->where($query->expr()->eq('key', $query->createNamedParameter($key['key'])))
				->andWhere($query->expr()->eq('category', $query->createNamedParameter('stats')))
				->execute();
			$data = $result->fetchAll();
			$statistics[$key['key']] = $data[0];
			$result->closeCursor();
		}

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

		return $keys;
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
	 * get remaining statistics beside 'apps' and 'stats'
	 */
	private function getAppStatistics() {
		$appsStatistics = [];
		$categories = $this->getCategories();
		foreach ($categories as $category) {
			if ($category['category'] !== 'apps' && $category['category'] !== 'stats') {
				$keys = $this->getKeysOfCategory($category['category']);
				foreach ($keys as $key) {
					$generalStatistics = $this->getGeneralStatistics($category['category'], $key['key']);
					foreach($generalStatistics as $statKey => $statValue) {
						$appsStatistics[$category['category']][$statKey] = $statValue;
					}
				}
			}
		}
		return $appsStatistics;
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

		return $categories;
	}
}
