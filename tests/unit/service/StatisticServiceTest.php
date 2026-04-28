<?php
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Tests\Unit\Service;

use OCA\SurveyServer\Service\StatisticService;
use Test\TestCase;

class StatisticServiceTest extends TestCase {

	public function testValidateDataAcceptsValidPayload(): void {
		StatisticService::validateData([
			'id' => 'randomID_454354',
			'items' => [
				['server', 'version', '25.0.1.1'],
				['apps', 'files_sharing', '1.17.0'],
				['stats', 'num_users', 2],
			],
		]);

		$this->assertTrue(true);
	}

	public function testValidateDataRejectsEmptyItems(): void {
		$this->expectException(\InvalidArgumentException::class);

		StatisticService::validateData([
			'id' => 'randomID_454354',
			'items' => [],
		]);
	}

	public function testValidateDataRejectsInvalidItemName(): void {
		$this->expectException(\InvalidArgumentException::class);

		StatisticService::validateData([
			'id' => 'randomID_454354',
			'items' => [
				['server', '../version', '25.0.1.1'],
			],
		]);
	}

	public function testValidateDataRejectsOversizedValue(): void {
		$this->expectException(\InvalidArgumentException::class);

		StatisticService::validateData([
			'id' => 'randomID_454354',
			'items' => [
				['server', 'version', str_repeat('a', StatisticService::MAX_VALUE_LENGTH + 1)],
			],
		]);
	}

	public function testValidateDataRejectsNonNumericStatsValue(): void {
		$this->expectException(\InvalidArgumentException::class);

		StatisticService::validateData([
			'id' => 'randomID_454354',
			'items' => [
				['stats', 'num_users', 'many'],
			],
		]);
	}
}
