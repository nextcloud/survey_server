<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Controller;

use OCA\SurveyServer\Service\StatisticService;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {

	/** @var StatisticService */
	protected StatisticService $service;

	/**
	 * PageController constructor.
	 *
	 * @param string $AppName
	 * @param IRequest $request
	 * @param StatisticService $service
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		StatisticService $service
	) {
		parent::__construct($AppName, $request);
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$statistics = ['statistics' => $this->service->get()];
		return new TemplateResponse('survey_server', 'main', $statistics);
	}

}
