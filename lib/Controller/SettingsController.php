<?php
/**
 * @copyright Copyright (c) 2023, Marcel Scherello <surveyserver@scherello.de>
 *
 * @author Marcel Scherello <surveyserver@scherello.de>
 *
 * @license AGPL-3.0-or-later
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

use OCA\SurveyServer\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller {
	private LoggerInterface $logger;
	private SettingsService $SettingsService;

	public function __construct(
		$appName,
		IRequest $request,
		LoggerInterface $logger,
		SettingsService $SettingsService
	) {
		parent::__construct($appName, $request);
		$this->logger = $logger;
		$this->SettingsService = $SettingsService;
	}

	/**
	 * update settings
	 *
	 * @NoAdminRequired
	 * @param int $time
	 * @return DataResponse
	 */
	public function update(int $time): DataResponse {
		return new DataResponse($this->SettingsService->update($time));
	}
}