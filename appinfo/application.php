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

	/**
	 * register OCS API Calls
	 */
	public function registerOCSApi() {

		$container = $this->getContainer();
		$server = $container->getServer();

		$request = $server->getRequest();
		$statisticService = $container->query('statisticService');
		$api = new ExternalApi($request, $statisticService, $server->getConfig());
		//$api = new ExternalApi($server->getRequest(), $container->query('statisticService'));

		API::register('post',
			'/apps/survey_server/api/v1/survey',
			array($api, 'receiveSurveyResults'),
			'survey_server',
			API::GUEST_AUTH
		);

	}

}
