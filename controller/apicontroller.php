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


namespace OCA\PopularityContestServer\Controller;


use OCA\PopularityContestServer\Service\StatisticService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ApiController extends \OCP\AppFramework\ApiController {

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param StatisticService $service
	 */
	public function __construct($AppName, IRequest $request,
								StatisticService $service){
		parent::__construct($AppName, $request);
		$this->service = $service;
	}

	/**
	 * @PublicPage
	 * @CORS
	 * @NoCSRFRequired
	 *
	 * @param string $data json encoded data
	 * @return DataResponse
	 */
	public function add($data) {
		$array = json_decode($data, true);

		if ($array === null) {
			return new DataResponse(
				['message' => 'Invalid data supplied.'],
				Http::STATUS_BAD_REQUEST
			);
		}

		try {
			$this->service->add($array);
		} catch (\Exception $e) {
			return new DataResponse(
				['message' => 'Invalid data supplied.'],
				Http::STATUS_BAD_REQUEST
			);
		}

		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return DataResponse
	 */
	public function get() {
		$result = $this->service->get();
		return new DataResponse($result);
	}

}
