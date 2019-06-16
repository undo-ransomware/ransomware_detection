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

namespace OCA\RansomwareDetection\tests\Unit\Controller;

use OCA\RansomwareDetection\Controller\SettingsController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\Folder;
use Test\TestCase;

class SettingsControllerTest extends TestCase
{
    /** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
    protected $userSession;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var string */
    protected $userId = 'john';

    public function setUp()
    {
        parent::setUp();

        $this->request = $this->getMockBuilder('OCP\IRequest')
            ->getMock();
        $this->userSession = $this->getMockBuilder('OCP\IUserSession')
            ->getMock();
        $this->config = $this->getMockBuilder('OCP\IConfig')
            ->getMock();
    }

    public function testFindAll()
    {
        $controller = new SettingsController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            'john'
        );

        $result = $controller->findAll();
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_OK);
    }

    public function testUpdate()
    {
        $controller = new SettingsController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            'john'
        );

        $result = $controller->update(1, 0);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_OK);
    }
}
