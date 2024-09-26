<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Service;

use OCP\DB\Exception;
use OCP\IAppConfig;
use OCP\IDBConnection;

class StatisticService {

	/** @var  IDBConnection */
	protected IDBConnection $connection;

	/** @var IAppConfig */
	protected IAppConfig $config;

	/** @var string */
	protected string $table = 'survey_results';

	/**
	 * @param IDBConnection $connection
	 * @param IAppConfig $config
	 */
	public function __construct(IDBConnection $connection, IAppConfig $config) {
		$this->connection = $connection;
		$this->config = $config;
	}

	/**
	 * new add data set to database
	 *
	 * @param array $data
	 * @return true
	 * @throws Exception
	 */
	public function add($data) {
		$source = $data['id'];
		$timestamp = time();

		$this->connection->beginTransaction();
		$query = $this->connection->getQueryBuilder();
		$query->insert($this->table)->values([
			'source' => $query->createNamedParameter($source),
			'timestamp' => $query->createNamedParameter($timestamp),
			'category' => $query->createParameter('category'),
			'key' => $query->createParameter('key'),
			'value' => $query->createParameter('value')
		]);
		$this->removeOldStatistics($source);
		foreach ($data['items'] as $item) {
			$query->setParameter('category', $item[0])->setParameter('key', $item[1])->setParameter('value', $item[2]);
			$query->executeStatement();
		}
		$this->connection->commit();
		return true;
	}

	/**
	 * remove old statistic from given source
	 *
	 * @param string $source
	 * @throws Exception
	 */
	protected function removeOldStatistics(string $source) {
		$query = $this->connection->getQueryBuilder();
		$query->delete($this->table)->where($query->expr()->eq('source', $query->createNamedParameter($source)))
			  ->executeStatement();
	}

	/**
	 * get statistics stored in the database
	 *
	 * @return array
	 */
	public function get(): array {
		$data = $this->config->getValueString('survey_server', 'evaluated_statistics');
		$result = json_decode($data, true);
		if ($result === null) {
			return [];
		}
		return $result;
	}
}