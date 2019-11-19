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
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {

	/** @var StatisticService */
	protected $service;

	public function __construct(string $AppName,
								IRequest $request,
								StatisticService $service) {
		parent::__construct($AppName, $request);

		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse {
		$statistics = ['statistics' => $this->service->get()];

		$response = new TemplateResponse('survey_server', 'main', $statistics);
		$csp = new ContentSecurityPolicy();
		$csp->allowEvalScript(true);
		$response->setContentSecurityPolicy($csp);
		return $response;
	}

}
