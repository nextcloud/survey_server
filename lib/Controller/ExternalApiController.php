<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Controller;

use OCA\SurveyServer\Service\StatisticService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class ExternalApiController extends OCSController {

	/** @var StatisticService */
	private $service;

	/**
	 * OCSAuthAPI constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param StatisticService $service
	 */
	public function __construct(
		$appName,
		IRequest $request,
		StatisticService $service
	) {
		parent::__construct($appName, $request);
		$this->service = $service;
	}

	/**
	 * request received to ask remote server for a shared secret
	 *
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param string $data
	 * @return DataResponse
	 */
	public function receiveSurveyResults(string $data) {

		$array = json_decode($data, true);
		$array['timestamp'] = time();
		$logFile = \OC::$server->getConfig()
							   ->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data') . '/survey.log';
		file_put_contents($logFile, json_encode($array) . PHP_EOL, FILE_APPEND);

		if ($array === null) {
			return new DataResponse(['message' => 'Invalid data supplied.'], Http::STATUS_BAD_REQUEST);
		}

		try {
			$this->service->add($array);
		} catch (\Exception $e) {
			return new DataResponse(['message' => 'Invalid data supplied.'], Http::STATUS_BAD_REQUEST);
		}

		return new DataResponse();
	}
}
