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
	public const MAX_SOURCE_LENGTH = 512;
	public const MAX_CATEGORY_LENGTH = 128;
	public const MAX_KEY_LENGTH = 512;
	public const MAX_VALUE_LENGTH = 1024;
	private const NAME_PATTERN = '/^[A-Za-z0-9_.:-]+$/';

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
		self::validateData($data);

		$source = $data['id'];
		$timestamp = time();

		$this->connection->beginTransaction();
		try {
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
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			throw $e;
		}
		return true;
	}

	/**
	 * @param mixed $data
	 * @throws \InvalidArgumentException
	 */
	public static function validateData($data): void {
		if (!is_array($data) || !isset($data['id']) || !isset($data['items'])) {
			throw new \InvalidArgumentException('Invalid survey payload.');
		}

		if (!is_string($data['id']) || $data['id'] === '' || strlen($data['id']) > self::MAX_SOURCE_LENGTH) {
			throw new \InvalidArgumentException('Invalid survey source.');
		}

		if (!preg_match(self::NAME_PATTERN, $data['id'])) {
			throw new \InvalidArgumentException('Invalid survey source.');
		}

		if (!is_array($data['items']) || count($data['items']) === 0) {
			throw new \InvalidArgumentException('Invalid survey items.');
		}

		foreach ($data['items'] as $item) {
			if (!is_array($item)
				|| count($item) !== 3
				|| !array_key_exists(0, $item)
				|| !array_key_exists(1, $item)
				|| !array_key_exists(2, $item)) {
				throw new \InvalidArgumentException('Invalid survey item.');
			}

			if (!self::isValidName($item[0], self::MAX_CATEGORY_LENGTH)
				|| !self::isValidName($item[1], self::MAX_KEY_LENGTH)) {
				throw new \InvalidArgumentException('Invalid survey item name.');
			}

			if (!self::isValidValue($item[2])) {
				throw new \InvalidArgumentException('Invalid survey item value.');
			}

			if ($item[0] === 'stats' && strpos($item[1], 'num_') === 0 && !is_numeric($item[2])) {
				throw new \InvalidArgumentException('Invalid numeric survey item.');
			}
		}
	}

	/**
	 * @param mixed $value
	 */
	private static function isValidName($value, int $maxLength): bool {
		return is_string($value)
			&& $value !== ''
			&& strlen($value) <= $maxLength
			&& preg_match(self::NAME_PATTERN, $value) === 1;
	}

	/**
	 * @param mixed $value
	 */
	private static function isValidValue($value): bool {
		if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
			return false;
		}

		return strlen((string)$value) <= self::MAX_VALUE_LENGTH;
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
