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

namespace OCA\PopularityContestServer\AppInfo;


use OCA\PopularityContestServer\Api\ExternalApi;
use OCA\PopularityContestServer\Service\StatisticService;
use OCP\API;
use OCP\App;


class Application extends \OCP\AppFramework\App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = array()) {
		parent::__construct('popularitycontestserver', $urlParams);
		$this->registerService();
	}

	private function registerService() {
		$container = $this->getContainer();

		$container->registerService('statisticService', function(IAppContainer $c) {
			return new StatisticService(\OC::$server->getDatabaseConnection());
		});
	}

	/**
	 * register OCS API Calls
	 */
	public function registerOCSApi() {

		$container = $this->getContainer();
		$server = $container->getServer();

		$api = new ExternalApi($server->getRequest(), $container->query('statisticService'));

		API::register('post',
			'/apps/popularitycontestserver/api/v1/survey',
			array($api, 'receiveSurveyResults'),
			'popularitycontestserver',
			API::GUEST_AUTH
		);

	}

}
