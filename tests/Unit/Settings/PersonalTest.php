<?php

/**
 * @copyright Copyright (c) 2018 Matthias Held <matthias.held@uni-konstanz.de>
 * @author Matthias Held <matthias.held@uni-konstanz.de>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection\tests\Unit\Settings;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Settings\Personal;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use Test\TestCase;

class PersonalTest extends TestCase {

	/** @var Personal */
	private $personal;

	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

	public function setUp(): void
	{
		parent::setUp();

        $this->config = $this->getMockForAbstractClass( IConfig::class, array(), '', FALSE, TRUE, TRUE, array( 'getUserValue' ) );

		$this->personal = new Personal($this->config, 'john');
	}

	public function dataGetForm() {
		return [
            ['colorMode' => 0, 'colorActive' => ['code' => 0, 'name' => 'Normal'], 'color' => ['code' => 1, 'name' => 'Color blind']],
			['colorMode' => 1, 'colorActive' => ['code' => 1, 'name' => 'Color blind'], 'color' => ['code' => 0, 'name' => 'Normal']],
		];
	}

	/**
     * @dataProvider dataGetForm
     *
     * @param int       $colorMode
     * @param array     $colorActive
     * @param array     $color
     */
	public function testGetForm($colorMode, $colorActive, $color) {
		$this->config->expects($this->once())
				->method('getUserValue')
				->willReturn($colorMode);

		$expected = new TemplateResponse(Application::APP_ID, 'personal',
							['color_active' => $colorActive, 'color' => $color,], '');

		$this->assertEquals($expected, $this->personal->getForm());
	}

    public function testGetSection() {
    	$this->assertSame(Application::APP_ID, $this->personal->getSection());
    }

    public function testGetPriority() {
    	$this->assertSame(40, $this->personal->getPriority());
    }
}
