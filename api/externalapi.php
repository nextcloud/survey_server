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


namespace OCA\Survey_Server\Api;


use OCA\Survey_Server\Service\StatisticService;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;

class ExternalApi {
	/** @var IRequest */
	private $request;
	/** @var StatisticService */
	private $service;
	/** @var IConfig */
	private $config;

	/**
	 * OCSAuthAPI constructor.
	 *
	 * @param IRequest $request
	 * @param StatisticService $service
	 * @param IConfig $config
	 */
	public function __construct(
		IRequest $request,
		StatisticService $service,
		IConfig $config
	) {
		$this->request = $request;
		$this->service = $service;
		$this->config = $config;
	}

	/**
	 * request received to ask remote server for a shared secret
	 *
	 * @return \OC\OCS\Result
	 */
	public function receiveSurveyResults() {

		$data = $this->request->getParam('data');

		$array = json_decode($data, true);
		$array['timestamp'] = time();

		$logFile = \OC::$server->getConfig()->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data') . '/survey.log';
		file_put_contents($logFile, json_encode($array). PHP_EOL, FILE_APPEND);

		if ($array === null) {
			return new \OC\OCS\Result(null, Http::STATUS_BAD_REQUEST, 'Invalid data supplied.');
		}

		try {
			$this->service->add($array);
		} catch (\Exception $e) {
			return new \OC\OCS\Result(null, Http::STATUS_BAD_REQUEST, 'Invalid data supplied.');
		}

		return new \OC\OCS\Result(null, Http::STATUS_OK);

	}

}
