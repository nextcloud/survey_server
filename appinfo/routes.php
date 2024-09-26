<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\PopularityContestServer\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],

        // settings
        ['name' => 'settings#update', 'url' => '/settings', 'verb' => 'POST'],

		// api
		['name' => 'api#add', 'url' => '/api/v1/data', 'verb' => 'POST'],
		['name' => 'api#get', 'url' => '/api/v1/data', 'verb' => 'GET']
	],
	'ocs' => [
		['name' => 'ExternalApi#receiveSurveyResults', 'url' => '/api/v1/survey', 'verb' => 'POST'],
	],
];
