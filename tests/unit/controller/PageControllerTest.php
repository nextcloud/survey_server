<?php
/**
 * @copyright Copyright (c) 2016, Björn Schießle <bjoern@schiessle.org>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Survey_Server\Controller;

use OCA\Survey_Server\Service\StatisticService;

use OCP\AppFramework\Http\TemplateResponse;
use Test\TestCase;


class PageControllerTest extends TestCase {

	private $controller;
	/** @var  StatisticService | \PHPUnit_Framework_MockObject_MockObject */
	private $statisticService;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->statisticService = $this->getMockBuilder('OCA\Survey_Server\Service\StatisticService')
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
