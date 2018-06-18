<?php

/**
 * @copyright Copyright (c) 2017 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\tests\Unit\Notification;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Notification\Notifier;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IL10N;
use OCP\L10N\IFactory;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\IConfig;
use Test\TestCase;

class NotifierTest extends TestCase
{
    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var IL10N|\PHPUnit_Framework_MockObject_MockObject */
    protected $l;

    /** @var IFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $l10nFactory;

    /** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $userManager;

    /** @var IManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationManager;

    /** @var IURLGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlGenerator;

    /** @var Notifier|\PHPUnit_Framework_MockObject_MockObject */
    protected $notifier;

    public function setUp()
    {
        parent::setUp();

        $this->l = $this->createMock(IL10N::class);
        $this->l->expects($this->any())
            ->method('t')
            ->willReturnCallback(function ($string, $args) {
                return vsprintf($string, $args);
            });
        $this->l10nFactory = $this->createMock(IFactory::class);
        $this->l10nFactory->expects($this->any())
            ->method('get')
            ->willReturn($this->l);
        $this->userManager = $this->createMock(IUserManager::class);
        $this->notificationManager = $this->createMock(IManager::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);
        $this->config = $this->createMock(IConfig::class);

        $this->notifier = new Notifier($this->config, $this->l10nFactory, $this->userManager, $this->notificationManager, $this->urlGenerator, 'john');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown app
     */
    public function testPrepareWrongApp()
    {
        /** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
        $notification = $this->createMock(INotification::class);

        $notification->expects($this->once())
            ->method('getApp')
            ->willReturn('notifications');
        $notification->expects($this->never())
            ->method('getSubject');

        $this->notifier->prepare($notification, 'en');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown subject
     */
    public function testPrepareWrongSubject()
    {
        /** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
        $notification = $this->createMock(INotification::class);

        $notification->expects($this->once())
            ->method('getApp')
            ->willReturn(Application::APP_ID);
        $notification->expects($this->once())
            ->method('getSubject')
            ->willReturn('wrong subject');

        $this->notifier->prepare($notification, 'en');
    }

    public function dataPrepare()
    {
        return [
            ['ransomware_attack_detected', ['Detected suspicious file operations.'], ['Detected a sequence of suspicious file operations.'], true],

        ];
    }

    /**
     * @dataProvider dataPrepare
     *
     * @param string $subject
     * @param array  $subjectParams
     * @param array  $messageParams
     * @param bool   $setMessage
     */
    public function testPrepare($subject, $subjectParams, $messageParams, $setMessage)
    {
        /** @var INotification|\PHPUnit_Framework_MockObject_MockObject $notification */
        $notification = $this->createMock(INotification::class);

        $notification->expects($this->once())
            ->method('getApp')
            ->willReturn(Application::APP_ID);
        $notification->expects($this->once())
            ->method('getSubject')
            ->willReturn($subject);
        $notification->expects($this->once())
            ->method('getSubjectParameters')
            ->willReturn($subjectParams);
        $notification->expects($this->once())
            ->method('getMessageParameters')
            ->willReturn($messageParams);
        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with($subjectParams[0])
            ->willReturnSelf();
        if ($setMessage) {
            $notification->expects($this->once())
                ->method('setParsedMessage')
                ->with($messageParams[0])
                ->willReturnSelf();
        } else {
            $notification->expects($this->never())
                ->method('setParsedMessage');
        }
        $this->urlGenerator->expects($this->once())
            ->method('imagePath')
            ->with(Application::APP_ID, 'app-dark.svg')
            ->willReturn('icon-url');
        $notification->expects($this->once())
            ->method('setIcon')
            ->with('icon-url')
            ->willReturnSelf();
        $return = $this->notifier->prepare($notification, 'en');

        $this->assertEquals($notification, $return);
    }
}
