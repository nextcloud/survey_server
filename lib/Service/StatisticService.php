<?php
declare(strict_types=1);
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Joas Schilling <coding@schilljs.com>
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

namespace OCA\Survey_Server\Service;

use OCP\IConfig;
use OCP\IDBConnection;

class StatisticService {

	/** @var  IDBConnection */
	protected $connection;

	/** @var IConfig */
	protected $config;

	/** @var string */
	protected $table = 'survey_results';

	public function __construct(IDBConnection $connection,
								IConfig $config) {
		$this->connection = $connection;
		$this->config = $config;
	}

	/**
	 * new add data set to database
	 *
	 * @param array $data
	 */
	public function add(array $data): void {
		$source = $data['id'];
		$timestamp = time();

		$this->connection->beginTransaction();
		$query = $this->connection->getQueryBuilder();
		$query->insert($this->table)
			->values(
				[
					'source' => $query->createNamedParameter($source),
					'timestamp' => $query->createNamedParameter($timestamp),
					'category' => $query->createParameter('category'),
					'key' => $query->createParameter('key'),
					'value' => $query->createParameter('value')
				]
			);
		$this->removeOldStatistics($source);
		foreach ($data['items'] as $item) {
				$query->setParameter('category', $item[0])
					->setParameter('key', $item[1])
					->setParameter('value', $item[2]);
				$query->execute();
		}
		$this->connection->commit();

	}

	/**
	 * remove old statistic from given source
	 *
	 * @param string $source
	 */
	protected function removeOldStatistics(string $source): void {
		$query = $this->connection->getQueryBuilder();
		$query->delete($this->table)
			->where($query->expr()->eq('source', $query->createNamedParameter($source)))
			->execute();

	}

	/**
	 * get statistics stored in the database
	 *
	 * @return array
	 */
	public function get(): array {
		$data = $this->config->getAppValue('survey_server', 'evaluated_statistics', '[]');
		$result = json_decode($data, true);
		if($result === null) {
			return [];
		}

		return $result;
	}

}
