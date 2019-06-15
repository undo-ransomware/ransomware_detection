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
use OCA\RansomwareDetection\Settings\Admin;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use Test\TestCase;

class AdminTest extends TestCase {

	/** @var Admin */
	private $admin;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

	public function setUp() {
		parent::setUp();

        $this->config = $this->getMockForAbstractClass( IConfig::class, array(), '', FALSE, TRUE, TRUE, array( 'getAppValue' ) );

		$this->admin = new Admin($this->config);
	}

    public function dataGetForm()
    {
        return [
            ['serviceUri' => 'http://localhost:8080/api/status']
        ];
    }

    /**
     * @dataProvider dataGetForm
     *
     * @param int     $serviceUri
     */
	public function testGetForm($serviceUri) {

        $this->config->expects($this->any())
            ->method('getAppValue')
            ->withConsecutive([Application::APP_ID, 'service_uri', $this->anything()])
            ->willReturnOnConsecutiveCalls($serviceUri);

		$expected = new TemplateResponse(Application::APP_ID, 'admin',
                            ['service_uri' => $serviceUri], '');

        $this->assertEquals($expected, $this->admin->getForm());
	}

	public function testGetSection() {
		$this->assertSame(Application::APP_ID, $this->admin->getSection());
	}

	public function testGetPriority() {
		$this->assertSame(1, $this->admin->getPriority());
	}
}
