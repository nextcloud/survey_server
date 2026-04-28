<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Controller;

use OCA\SurveyServer\Service\StatisticService;
use OCP\AppFramework\Http\DataResponse;
use OCP\DB\Exception;
use OCP\IRequest;

/**
 * Authenticated app API endpoints.
 *
 * These routes are consumed as API calls, not browser form submissions. They
 * intentionally extend Nextcloud's ApiController and opt out of CSRF checks.
 */
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
	 *
	 * @param string $data
	 * @return DataResponse
	 * @throws Exception
	 */
	public function add(string $data) {
		$array = json_decode($data, true);
		$result = $this->StatisticService->add($array);
		return new DataResponse($result);
	}
}
