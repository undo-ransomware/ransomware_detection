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
use OCA\RansomwareDetection\Db\RecoveredFileOperation;
use OCA\RansomwareDetection\Controller\RecoveredFileOperationController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Files_Trashbin\Trash\ITrashManager;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IUserManager;
use Test\TestCase;

class RecoveredFileOperationControllerTest extends TestCase
{
    /** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var ITrashManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $trashManager;

    /** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $userManager;

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
        $this->trashManager = $this->getMockBuilder('OCA\Files_Trashbin\Trash\ITrashManager')
            ->getMock();
        $this->userManager = $this->getMockBuilder('OCP\IUserManager')
            ->getMock();
        $connection = $this->getMockBuilder('OCP\IDBConnection')
            ->getMock();
        $mapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\FileOperationMapper')
            ->setConstructorArgs([$connection])
            ->getMock();
        $recoveredMapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\RecoveredFileOperationMapper')
            ->setConstructorArgs([$connection])
            ->getMock();
        $this->service = $this->getMockBuilder('OCA\RansomwareDetection\Service\RecoveredFileOperationService')
            ->setConstructorArgs([$recoveredMapper, $mapper, $this->userId])
            ->getMock();
        $this->classifier = $this->getMockBuilder('OCA\RansomwareDetection\Classifier')
            ->setConstructorArgs([$this->logger, $mapper, $this->service])
            ->getMock();
        $this->sequenceAnalyzer = $this->createMock(SequenceAnalyzer::class);
    }

    public function testFindAll()
    {
        $controller = new RecoveredFileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->logger,
            $this->folder,
            $this->service,
            $this->classifier,
            $this->trashManager,
            $this->userManager,
            'john'
        );
        $file = $this->getMockBuilder(RecoveredFileOperation::class)
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

        $result = $controller->findAll();
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_OK);
    }

    public function dataRecover()
    {
        $fileOperationWrite = new RecoveredFileOperation();
        $fileOperationWrite->setCommand(Monitor::WRITE);
        $fileOperationWrite->setPath('/admin/files');
        $fileOperationWrite->setFileId(1);
        $fileOperationWrite->setOriginalName('test.jpg');

        $fileOperationRead = new RecoveredFileOperation();
        $fileOperationRead->setCommand(Monitor::READ);
        $fileOperationRead->setPath('/admin/files');
        $fileOperationRead->setFileId(1);
        $fileOperationRead->setOriginalName('test.jpg');

        $fileOperationDelete = new RecoveredFileOperation();
        $fileOperationDelete->setCommand(Monitor::DELETE);
        $fileOperationDelete->setPath('/admin/file');
        $fileOperationDelete->setFileId(1);
        $fileOperationDelete->setOriginalName('test.jpg');

        $fileOperationRename = new RecoveredFileOperation();
        $fileOperationRename->setCommand(Monitor::RENAME);
        $fileOperationRename->setPath('/admin/file');
        $fileOperationRename->setFileId(1);
        $fileOperationRename->setOriginalName('test.jpg');

        $fileOperationUserFolder = new RecoveredFileOperation();
        $fileOperationUserFolder->setCommand(Monitor::RENAME);
        $fileOperationUserFolder->setPath('/admin/file');
        $fileOperationUserFolder->setFileId(3);
        $fileOperationUserFolder->setOriginalName('test.jpg');

        $fileOperationEmptyPath = new RecoveredFileOperation();
        $fileOperationEmptyPath->setCommand(Monitor::RENAME);
        $fileOperationEmptyPath->setFileId(2);
        $fileOperationEmptyPath->setOriginalName('test.jpg');

        $fileOperationEmptyName= new RecoveredFileOperation();
        $fileOperationEmptyName->setCommand(Monitor::RENAME);
        $fileOperationEmptyName->setPath('/admin/file');
        $fileOperationEmptyName->setFileId(2);

        $fileOperationEmptyFileId = new RecoveredFileOperation();
        $fileOperationEmptyFileId->setCommand(Monitor::RENAME);
        $fileOperationEmptyFileId->setPath('/admin/file');
        $fileOperationEmptyFileId->setOriginalName('test.jpg');

        return [
            ['id' => 4, 'fileOperation' => new RecoveredFileOperation(), 'deleted' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 3, 'fileOperation' => $fileOperationUserFolder, 'deleted' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 2, 'fileOperation' => $fileOperationEmptyPath, 'deleted' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 2, 'fileOperation' => $fileOperationEmptyName, 'deleted' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 2, 'fileOperation' => $fileOperationEmptyFileId, 'deleted' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 2, 'fileOperation' => $fileOperationRead, 'deleted' => true, 'response' => Http::STATUS_OK],
            ['id' => 2, 'fileOperation' => $fileOperationRename, 'deleted' => true, 'response' => Http::STATUS_OK],
            // needs more mocking
            //['id' => 2, 'fileOperation' => $fileOperationDelete, 'deleted' => true, 'response' => Http::STATUS_OK],
            ['id' => 2, 'fileOperation' => $fileOperationWrite, 'deleted' => true, 'response' => Http::STATUS_OK],
        ];
    }

    /**
     * @dataProvider dataRecover
     *
     * @param array         $id
     * @param FileOperation $fileOperation
     * @param bool          $deleted
     * @param HttpResponse  $response
     */
    public function testRecover($id, $fileOperation, $deleted, $response)
    {
        $controller = $this->getMockBuilder(RecoveredFileOperationController::class)
            ->setConstructorArgs([
                'ransomware_detection',
                $this->request,
                $this->userSession,
                $this->config,
                $this->logger,
                $this->folder,
                $this->service,
                $this->classifier,
                $this->trashManager,
                $this->userManager,
                'john'
            ])
            ->setMethods(['deleteFromStorage'])
            ->getMock();

        $this->service->method('find')
            ->willReturn($fileOperation);

        $controller->expects($this->any())
            ->method('deleteFromStorage')
            ->willReturn($deleted);

        $this->service->method('deleteById');

        $this->folder->method('getId')
            ->willReturn(3);

        $result = $controller->recover([$id]);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), $response);
    }

    public function testRecoverMultipleObjectsReturnedException()
    {
        $controller = new RecoveredFileOperationController(
                'ransomware_detection',
                $this->request,
                $this->userSession,
                $this->config,
                $this->logger,
                $this->folder,
                $this->service,
                $this->classifier,
                $this->trashManager,
                $this->userManager,
                'john'
            );

        $this->service->method('find')
            ->will($this->throwException(new \OCP\AppFramework\Db\MultipleObjectsReturnedException('test')));

        $result = $controller->recover([1]);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_BAD_REQUEST);
    }

    public function testDoesNotExistException()
    {
        $controller = new RecoveredFileOperationController(
            'ransomware_detection',
                $this->request,
                $this->userSession,
                $this->config,
                $this->logger,
                $this->folder,
                $this->service,
                $this->classifier,
                $this->trashManager,
                $this->userManager,
                'john'
        );

        $this->service->method('find')
            ->will($this->throwException(new \OCP\AppFramework\Db\DoesNotExistException('test')));

        $result = $controller->recover([1]);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_BAD_REQUEST);
    }

    public function testDeleteFromStorage()
    {
        $controller = new RecoveredFileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->logger,
            $this->folder,
            $this->service,
            $this->classifier,
            $this->trashManager,
            $this->userManager,
            'john'
        );
        $file = $this->createMock(File::class);
        $file->method('isDeletable')
            ->willReturn(true);

        $file->method('delete');

        $this->folder->method('getId')
            ->willReturn(3);

        $this->folder->method('getById')
            ->willReturn([$file]);

        $this->assertTrue($this->invokePrivate($controller, 'deleteFromStorage', [2]));
    }

    public function testDeleteFromStorageNotPossible()
    {
        $controller = new RecoveredFileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->logger,
            $this->folder,
            $this->service,
            $this->classifier,
            $this->trashManager,
            $this->userManager,
            'john'
        );
        $file = $this->createMock(File::class);

        $file->method('isDeletable')
            ->willReturn(false);

        $file->method('delete');

        $this->folder->method('getId')
            ->willReturn(3);

        $this->folder->method('getById')
            ->willReturn([$file]);
        $this->assertFalse($this->invokePrivate($controller, 'deleteFromStorage', [1]));
    }

    public function testDeleteFromStorageNotFoundException()
    {
        $controller = new RecoveredFileOperationController(
            'ransomware_detection',
            $this->request,
            $this->userSession,
            $this->config,
            $this->logger,
            $this->folder,
            $this->service,
            $this->classifier,
            $this->trashManager,
            $this->userManager,
            'john'
        );
        $file = $this->createMock(File::class);

        $file->method('isDeletable')
            ->willReturn(false);

        $file->method('delete');

        $this->folder->method('getId')
            ->willReturn(3);

        $this->folder->method('getById')
            ->willReturn([]);

        $this->assertTrue($this->invokePrivate($controller, 'deleteFromStorage', [1]));
    }
}
