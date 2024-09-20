<?php
/**
 * @copyright Copyright (c) 2018 Bjoern Schiessle <bjoern@schiessle.org>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
