<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SurveyServer\Controller;

use OCA\SurveyServer\Service\StatisticService;

use OCP\AppFramework\Http\TemplateResponse;
use Test\TestCase;

class PageControllerTest extends TestCase {

	private $controller;
	/** @var  StatisticService | \PHPUnit_Framework_MockObject_MockObject */
	private $statisticService;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->statisticService = $this->getMockBuilder('OCA\SurveyServer\Service\StatisticService')
			->disableOriginalConstructor()->getMock();

		$this->controller = new PageController(
			'survey_server', $request, $this->statisticService
		);
	}


	public function testIndex() {

		$this->statisticService->expects($this->once())->method('get')
			->willReturn(['stat1' => 42]);

		$result = $this->controller->index();

		$this->assertEquals(['statistics' => ['stat1' => 42]], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}