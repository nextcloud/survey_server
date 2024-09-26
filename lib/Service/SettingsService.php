<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Service;

use OCP\IAppConfig;

class SettingsService {
	/** @var IAppConfig */
	protected IAppConfig $config;

	/**
	 * @param IAppConfig $config
	 */
	public function __construct(IAppConfig $config) {
		$this->config = $config;
	}

	public function update(int $time): int {
		$this->config->setValueString('survey_server', 'deletion_time', $time);
		return $time;
	}
}