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

namespace OCA\RansomwareDetection\tests\Unit\Connector\Sabre;

use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Connector\Sabre\RequestPlugin;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceResult;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCP\Notification\IManager;
use OCP\IUserSession;
use OCP\ISession;
use OCP\ILogger;
use OCP\IConfig;
use OCP\IUser;
use Sabre\DAV\Server;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Test\TestCase;

class RequestPluginTest extends TestCase
{
    /** @var RequestPlugin|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestPlugin;

    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
    protected $userSession;

    /** @var ISession|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var FileOperationService|\PHPUnit_Framework_MockObject_MockObject */
    protected $service;

    /** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $notifications;

    /** @var Classifier|\PHPUnit_Framework_MockObject_MockObject */
    protected $classifier;

    /** @var SequenceAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $sequenceAnalyzer;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->config = $this->getMockBuilder(IConfig::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->userSession = $this->getMockBuilder(IUserSession::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $this->session = $this->createMock(ISession::class);
        $this->service = $this->getMockBuilder(FileOperationService::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->notifications = $this->createMock(IManager::class);

        $this->classifier = $this->createMock(Classifier::class);

        $this->sequenceAnalyzer = $this->createMock(SequenceAnalyzer::class);
        $this->sequenceAnalyzer->method('analyze')
            ->willReturn(new SequenceResult(0, 0, 0, 0, 0, 0));

        $this->service->expects($this->any())
                    ->method('findSequenceById')
                    ->willReturn(['0']);

        $user = $this->getMockBuilder(IUser::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $user->expects($this->any())
                    ->method('getUID');

        $this->userSession->expects($this->any())
                    ->method('getUser')
                    ->willReturn($user);

        $this->config->expects($this->any())
            ->method('getAppValue')
            ->willReturn(3);

        $this->requestPlugin = new RequestPlugin($this->logger, $this->config, $this->userSession, $this->session, $this->service, $this->notifications, $this->classifier, $this->sequenceAnalyzer);
    }

    public function testInitialize()
    {
        $this->requestPlugin->initialize($this->createMock(Server::class));
        $this->assertTrue(true);
    }

    public function testBeforeHttpPropFind()
    {
        $this->requestPlugin->beforeHttpPropFind($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));

        $this->config->expects($this->any())
                    ->method('getUserValue')
                    ->willReturn(10);
        $this->requestPlugin->beforeHttpPropFind($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertTrue(true);
    }

    public function testBeforeHttpPut()
    {
        $this->requestPlugin->beforeHttpPut($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertTrue(true);
    }

    public function testBeforeHttpDelete()
    {
        $this->requestPlugin->beforeHttpDelete($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertTrue(true);
    }

    public function testBeforeHttpGet()
    {
        $this->requestPlugin->beforeHttpGet($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertTrue(true);
    }

    public function testBeforeHttpPost()
    {
        $this->requestPlugin->beforeHttpPost($this->createMock(RequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertTrue(true);
    }
}
