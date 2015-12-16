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


namespace OCA\PopularityContestServer\Service;


use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class StatisticService {

	/** @var  IDBConnection */
	protected $connection;

	/** @var string	*/
	protected $table = 'popularity_contest';

	/**
	 * @param IDBConnection $connection
	 */
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * new add data set to database
	 *
	 * @param array $data
	 */
	public function add($data) {

		$this->connection->beginTransaction();
		$query = $this->connection->getQueryBuilder();
		$query->insert($this->table);
		$source = $data['id'];
		$timestamp = time();
		$this->removeOldStatistics($source);
		foreach ($data['items'] as $item) {
				$query->values(
					[
						'source' => $query->createNamedParameter($source),
						'timestamp' => $query->createNamedParameter($timestamp),
						'category' => $query->createNamedParameter($item[0]),
						'key' => $query->createNamedParameter($item[1]),
						'value' => $query->createNamedParameter($item[2])
					]
				);
				$query->execute();
		}
		$this->connection->commit();

	}

	/**
	 * remove old statistic from given source
	 *
	 * @param string $source
	 */
	protected function removeOldStatistics($source) {
		$query = $this->connection->getQueryBuilder();
		$query->delete($this->table)
			->where($query->expr()->eq('source', ':source'))
			->setParameter('source', $source)->execute();

	}

	/**
	 * get statistics stored in the database
	 *
	 * @return array
	 */
	public function get() {
		$result = array();
		$result['instances'] = $this->getNumberOfInstances();
		$result['users'] = $this->getUserStatistics();
		$result['apps'] = $this->getStatistics('apps', 'enabled');
		$result['system']['phpversion'] = $this->getStatistics('system', 'phpversion');
		$result['system']['ocversion'] = $this->getStatistics('system', 'ocversion');

		return $result;
	}


	/**
	 * return number of instances stored in the database
	 *
	 * @return int
	 */
	private function getNumberOfInstances() {
		$countInstances = $this->connection->createQueryBuilder();
		$i = $countInstances->select('COUNT(DISTINCT `source`) as instances')
			->from($this->table)->execute()->fetch();

		return (int)$i['instances'];
	}

	/**
	 * get user statistics
	 *
	 * @return array
	 */
	private function getUserStatistics() {
		$statistics = $this->connection->getQueryBuilder();
		$expr = $statistics->expr();

		$result = $statistics
			->select('AVG(`value`) AS average, MAX(`value`) as max, MIN(`value`) as min')
			->from($this->table)
			->where($expr->eq('`key`', $expr->literal('users')))
			->andWhere($expr->eq('category', $expr->literal('system')))->execute()->fetch();

		return $result;
	}

	/**
	 * get statistics stored in database.
	 * Counts how often a "value" was reported for a specific category and key
	 *
	 * @param string $category the category of the setting, e.g. 'apps' or 'system'
	 * @param string $key the key of the settings, e.g. 'enabled' or 'ocversion'
	 * @return array
	 */
	private function getStatistics($category, $key) {
		$statistics = $this->connection->getQueryBuilder();
		$expr = $statistics->expr();

		$stats = $statistics
			->select('`value`')
			->from($this->table)
			->where($expr->eq('`category`', $expr->literal($category)))
			->andWhere($expr->eq('`key`', $expr->literal($key)))
			->execute()->fetchAll();

		$result = array();
		foreach($stats as $s) {
			if (isset($result[$s['value']])) {
				$result[$s['value']] = $result[$s['value']] + 1;
			} else {
				$result[$s['value']] = 1;
			}
		}

		return $result;
	}

}
