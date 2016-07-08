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


namespace OCA\PopularityContestServer\Api;


use OCA\PopularityContestServer\Service\StatisticService;
use OCP\AppFramework\Http;
use OCP\IRequest;

class ExternalApi {

	/** @var IRequest */
	private $request;

	/** @var StatisticService */
	private $service;

	/**
	 * OCSAuthAPI constructor.
	 *
	 * @param IRequest $request
	 * @param StatisticService $service
	 */
	public function __construct(
		IRequest $request,
		StatisticService $service
	) {
		$this->request = $request;
		$this->service = $service;
	}

	/**
	 * request received to ask remote server for a shared secret
	 *
	 * @return \OC_OCS_Result
	 */
	public function receiveSurveyResults() {

		$data = $this->request->getParam('data');

		$array = json_decode($data, true);

		if ($array === null) {
			return new \OC_OCS_Result(null, Http::STATUS_BAD_REQUEST, 'Invalid data supplied.');
		}

		try {
			$this->service->add($array);
		} catch (\Exception $e) {
			return new \OC_OCS_Result(null, Http::STATUS_BAD_REQUEST, 'Invalid data supplied.');
		}

		return new \OC_OCS_Result(null, Http::STATUS_OK);

	}

}
