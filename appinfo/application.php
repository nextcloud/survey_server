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

namespace OCA\Survey_Server\AppInfo;


use OCA\Survey_Server\Api\ExternalApi;
use OCA\Survey_Server\Service\StatisticService;
use OCP\API;
use OCP\App;


class Application extends \OCP\AppFramework\App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = array()) {
		parent::__construct('survey_server', $urlParams);
		$this->registerService();
	}

	private function registerService() {
		$container = $this->getContainer();

		$container->registerService('statisticService', function() {
			return new StatisticService(\OC::$server->getDatabaseConnection(), \OC::$server->getConfig());
		});
	}

}
