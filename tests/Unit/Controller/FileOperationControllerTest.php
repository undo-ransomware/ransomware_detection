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

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceResult;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Controller\FileOperationController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\Folder;
use Test\TestCase;

class FileOperationControllerTest extends TestCase
{
    /** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
    protected $userSession;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var Classifier|\PHPUnit_Framework_MockObject_MockObject */
    protected $classifier;

    /** @var Folder|\PHPUnit_Framework_MockObject_MockObject */
    protected $folder;

    /** @var FileOperationService|\PHPUnit_Framework_MockObject_MockObject */
    protected $service;

    /** @var SequenceAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $sequenceAnalyzer;

    /** @var string */
    protected $userId = 'john';

    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this->getMockBuilder('OCP\IRequest')
            ->getMock();
        $this->userSession = $this->getMockBuilder('OCP\IUserSession')
            ->getMock();
        $this->config = $this->getMockBuilder('OCP\IConfig')
            ->getMock();
        $this->logger = $this->getMockBuilder('OCP\ILogger')
            ->getMock();
        $this->folder = $this->getMockBuilder('OCP\Files\Folder')
            ->getMock();
        $connection = $this->getMockBuilder('OCP\IDBConnection')
            ->getMock();
        $mapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\FileOperationMapper')
            ->setConstructorArgs([$connection])
            ->getMock();
        $recoveredMapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\RecoveredFileOperationMapper')
            ->setConstructorArgs([$connection])
            ->getMock();
        $this->service = $this->getMockBuilder('OCA\RansomwareDetection\Service\FileOperationService')
            ->setConstructorArgs([$mapper, $recoveredMapper, $this->userId])
            ->getMock();
        $this->classifier = $this->getMockBuilder('OCA\RansomwareDetection\Classifier')
            ->setConstructorArgs([$this->logger, $mapper, $this->service])
            ->getMock();
        $this->sequenceAnalyzer = $this->createMock(SequenceAnalyzer::class);
    }

    public function testListFileOperations()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );
        $file = $this->getMockBuilder(FileOperation::class)
            ->setMethods(['getSequence'])
            ->getMock();

        $sequenceResult = new SequenceResult(0, 0, 0, 0, 0, 0);

        $file->method('getSequence')
            ->willReturn(1);

        $this->service->method('findAll')
            ->willReturn([$file]);

        $this->classifier->method('classifyFile');
        $this->sequenceAnalyzer->method('analyze')
            ->willReturn($sequenceResult);

        $result = $controller->listFileOperations();
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_ACCEPTED);
    }

    public function testDeleteSequence()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );
        $this->service->method('deleteSequenceById')
            ->with(1)
            ->will($this->returnValue([]));

        $result = $controller->deleteSequence(1);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_ACCEPTED);
    }

    public function dataRecover()
    {
        $fileOperationWrite = new FileOperation();
        $fileOperationWrite->setCommand(Monitor::WRITE);
        $fileOperationWrite->setPath('/admin/files');
        $fileOperationWrite->setOriginalName('test.jpg');

        $fileOperationRead = new FileOperation();
        $fileOperationRead->setCommand(Monitor::READ);
        $fileOperationRead->setPath('/admin/files');
        $fileOperationRead->setOriginalName('test.jpg');

        $fileOperationDelete = new FileOperation();
        $fileOperationDelete->setCommand(Monitor::DELETE);
        $fileOperationDelete->setPath('/admin/file');
        $fileOperationDelete->setOriginalName('test.jpg');

        $fileOperationRename = new FileOperation();
        $fileOperationRename->setCommand(Monitor::RENAME);
        $fileOperationRename->setPath('/admin/file');
        $fileOperationRename->setOriginalName('test.jpg');

        return [
            ['id' => 4, 'fileOperation' => new FileOperation(), 'deleted' => false, 'response' => Http::STATUS_OK],
            ['id' => 1, 'fileOperation' => $fileOperationRead, 'deleted' => true, 'response' => Http::STATUS_OK],
            ['id' => 2, 'fileOperation' => $fileOperationRename, 'deleted' => true, 'response' => Http::STATUS_OK],
        ];
    }

    /**
     * @dataProvider dataRecover
     *
     * @param array         $fileIds
     * @param FileOperation $fileOperation
     * @param bool          $deleted
     * @param HttpResponse  $response
     */
    public function testRecover($fileIds, $fileOperation, $deleted, $response)
    {
        $controller = $this->getMockBuilder(FileOperationController::class)
            ->setConstructorArgs(['ransomware_detection', $this->request, $this->userSession, $this->config, $this->classifier,
            $this->logger, $this->folder, $this->service, $this->sequenceAnalyzer, 'john', ])
            ->setMethods(['deleteFromStorage', 'getTrashFiles'])
            ->getMock();

        $controller->expects($this->any())
            ->method('getTrashFiles')
            ->willReturn([]);

        $this->service->method('find')
            ->willReturn($fileOperation);

        $controller->expects($this->any())
            ->method('deleteFromStorage')
            ->willReturn($deleted);

        $this->service->method('deleteById');

        $result = $controller->recover($fileIds);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), $response);
    }

    public function testRecoverMultipleObjectsReturnedException()
    {
        $controller = $this->getMockBuilder(FileOperationController::class)
            ->setConstructorArgs(['ransomware_detection', $this->request, $this->userSession, $this->config, $this->classifier,
            $this->logger, $this->folder, $this->service, $this->sequenceAnalyzer, 'john', ])
            ->setMethods(['getTrashFiles'])
            ->getMock();

        $fileOperationWrite = new FileOperation();
        $fileOperationWrite->setCommand(Monitor::WRITE);
        $fileOperationWrite->setPath('/admin/files');
        $fileOperationWrite->setOriginalName('test.jpg');

        $controller->expects($this->any())
            ->method('getTrashFiles')
            ->willReturn([]);

        $this->service->method('find')
            ->will($this->throwException(new \OCP\AppFramework\Db\MultipleObjectsReturnedException('test')));

        $result = $controller->recover(1);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_BAD_REQUEST);
    }

    public function testDoesNotExistException()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );

        $fileOperationWrite = new FileOperation();
        $fileOperationWrite->setCommand(Monitor::WRITE);
        $fileOperationWrite->setPath('/admin/files');
        $fileOperationWrite->setOriginalName('test.jpg');

        $this->service->method('find')
            ->will($this->throwException(new \OCP\AppFramework\Db\DoesNotExistException('test')));

        $result = $controller->recover(1);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_BAD_REQUEST);
    }

    public function testDeleteFromStorage()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );
        $file = $this->createMock(File::class);
        $file->method('isDeletable')
            ->willReturn(true);

        $file->method('delete');

        $this->folder->expects($this->once())
            ->method('get')
            ->willReturn($file);
        $this->assertTrue($this->invokePrivate($controller, 'deleteFromStorage', ['/admin/files/test.jpg']));
    }

    public function testDeleteFromStorageNotPossible()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );
        $file = $this->createMock(File::class);

        $file->method('isDeletable')
            ->willReturn(false);

        $file->method('delete');

        $this->folder->expects($this->once())
            ->method('get')
            ->willReturn($file);
        $this->assertFalse($this->invokePrivate($controller, 'deleteFromStorage', ['/admin/files/test.jpg']));
    }

    public function testDeleteFromStorageNotFoundException()
    {
        $controller = new FileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->classifier,
            $this->logger,
            $this->folder,
            $this->service,
            $this->sequenceAnalyzer,
            'john'
        );
        $folder = $this->createMock(Folder::class);

        $this->folder->expects($this->once())
            ->method('get')
            ->will($this->throwException(new \OCP\Files\NotFoundException('test')));

        $this->assertTrue($this->invokePrivate($controller, 'deleteFromStorage', ['/admin/files']));
    }
}
