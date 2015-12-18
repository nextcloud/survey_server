<?php
/**
 * @author Björn Schießle <schiessle@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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


namespace OCA\PopularityContestServer;


class EvaluateStatistics {

	const PRESENTATION_TYPE_DIAGRAM = 'diagram';
	const PRESENTATION_TYPE_NUMERICAL_EVALUATION = 'numerical evaluation';
	const PRESENTATION_TYPE_VALUE = 'value';

	private $dataSchemaFile = '/data.json';

	/** @var  array */
	private $dataSchema;

	public function __construct() {
		$dataSchema = file_get_contents(\OC_App::getAppPath('popularitycontestserver') . $this->dataSchemaFile);
		$this->dataSchema = json_decode($dataSchema, true);
	}

	public function getType($key) {
		if (!isset($this->dataSchema[$key])) {
			throw new \BadMethodCallException('Key "' . $key . '" is not defined"');
		}

		return $this->dataSchema[$key]['type'];
	}

	public function getPresentationType($key) {
		if (!isset($this->dataSchema[$key])) {
			throw new \BadMethodCallException('Key "' . $key . '" is not defined"');
		}

		return $this->dataSchema[$key]['presentation'];
	}

	public function getDescription($key) {
		if (!isset($this->dataSchema[$key])) {
			throw new \BadMethodCallException('Key "' . $key . '" is not defined"');
		}

		return $this->dataSchema[$key]['description'];
	}

}
