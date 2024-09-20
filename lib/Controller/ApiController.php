<?php
/**
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Marcel Scherello <surveyserver@scherello.de>
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

namespace OCA\SurveyServer\Controller;

use OCA\SurveyServer\Service\StatisticService;
use OCP\AppFramework\Http\DataResponse;
use OCP\DB\Exception;
use OCP\IRequest;

class ApiController extends \OCP\AppFramework\ApiController {
	private StatisticService $StatisticService;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param StatisticService $service
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		StatisticService $StatisticService
	) {
		parent::__construct($AppName, $request);
		$this->StatisticService = $StatisticService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function get() {
		$result = $this->StatisticService->get();
		return new DataResponse($result);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param string $data
	 * @return DataResponse
	 * @throws Exception
	 */
	public function add(string $data) {
		$params = $this->request->getParams();
		$array = json_decode($data, true);
		$result = $this->StatisticService->add($array);
		return new DataResponse($result);
	}
}