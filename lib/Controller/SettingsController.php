<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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