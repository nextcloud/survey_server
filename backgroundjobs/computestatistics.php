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
use OCP\IDBConnection;

class ComputeStatistics extends TimedJob {

	/** @var IDBConnection */
	private $connection;

	public function __construct(IDBConnection $connection = null) {
		$this->connection = $connection ? $connection : \OC::$server->getDatabaseConnection();
	}

	protected function run($argument) {
		$result = [];
		$result['stats'] = $this->getSystemStatistics();
		$result['instances'] = $this->getNumberOfInstances();
		$result['apps'] = $this->getApps();
		$result['appStatistics'] = $this->getAppStatistics();

		// TODO write to db
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
				->where($query->expr()->eq('key', $query->createNamedParameter($key)))
				->andWhere($query->expr()->eq('category', $query->createNamedParameter('stats')))
				->execute();
			$statistics[$key] = $result->fetchAll();
			$result->closeCursor();
		}

	}

	/**
	 * get all keys of a given category
	 *
	 * @param string $category
	 * @return array
	 */
	private function getKeysOfCategory($category) {
		$getKeys = $this->connection->getQueryBuilder();
		$getKeys->selectDistinct('key')
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
		return $this->getGeneralStatistics('apps');
	}

	/**
	 * get statistics how often a specific key was reported for a given category
	 *
	 * @param $category
	 * @return array
	 */
	private function getGeneralStatistics($category) {
		$query = $this->connection->getQueryBuilder();

		$result = $query
			->select('key')
			->from($this->table)
			->where($query->expr()->eq('category', $query->createNamedParameter($category)))
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
	 * get remaining statistics beside 'apps' and 'stats'
	 */
	private function getAppStatistics() {
		$appsStatistics = [];
		$categories = $this->getCategories();
		foreach ($categories as $category) {
			if ($category !== 'apps' && $category !== 'stats') {
				$appsStatistics[$category] = $this->getGeneralStatistics($category);
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
		$getCategories->selectDistinct('category');
		$result = $getCategories->execute();
		$categories = $result->fetchAll();
		$result->closeCursor();

		return $categories;
	}
}
