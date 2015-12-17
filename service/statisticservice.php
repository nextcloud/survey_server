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
		$result['stats'] = $this->getSystemStatistics();
		$result['instances'] = $this->getNumberOfInstances();
		$result['apps'] = $this->getApps();
		$result['appStatistics'] = $this->getAppStatistics();
		return $result;
	}

}
