<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer;

class EvaluateStatistics {

	const PRESENTATION_TYPE_DIAGRAM = 'diagram';
	const PRESENTATION_TYPE_NUMERICAL_EVALUATION = 'numerical evaluation';
	const PRESENTATION_TYPE_VALUE = 'value';

	private $dataSchemaFile = '/data.json';

	/** @var  array */
	private $dataSchema;

	public function __construct() {
		$dataSchema = file_get_contents(\OC_App::getAppPath('survey_server') . $this->dataSchemaFile);
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
