<?php
declare(strict_types=1);
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


namespace OCA\Survey_Server\Controller;

use OCA\Survey_Server\Service\StatisticService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSBadRequestException;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;

class ExternalApiController extends OCSController {
	/** @var StatisticService */
	private $service;

	/** @var IConfig */
	private $config;

	/**
	 * OCSAuthAPI constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param StatisticService $service
	 * @param IConfig $config
	 */
	public function __construct(string $appName,
								IRequest $request,
								StatisticService $service,
								IConfig $config) {
		parent::__construct($appName, $request);

		$this->service = $service;
		$this->config = $config;
	}

	/**
	 * request received to ask remote server for a shared secret
	 *
	 * @param $data
	 * @return DataResponse
	 * @throws OCSBadRequestException
	 */
	public function receiveSurveyResults($data): DataResponse {
		$array = json_decode($data, true);
		$array['timestamp'] = time();

		$logFile = $this->config->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data') . '/survey.log';
		file_put_contents($logFile, json_encode($array). PHP_EOL, FILE_APPEND);

		if ($array === null) {
			throw new OCSBadRequestException('Invalid data supplied.');
		}

		try {
			$this->service->add($array);
		} catch (\Exception $e) {
			throw new OCSBadRequestException('Invalid data supplied.');
		}

		return new DataResponse([]);

	}

}
