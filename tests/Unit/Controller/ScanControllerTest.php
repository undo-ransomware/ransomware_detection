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
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceResult;
use OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileCorruptionResult;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Controller\ScanController;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Scanner\StorageStructure;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCP\Files\IRootFolder;
use OCA\Files_Trashbin\Trashbin;
use OCA\Files_Trashbin\Helper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\ILogger;
use Test\TestCase;

class ScanControllerTest extends TestCase
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

    /** @var EntropyAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $entropyAnalyzer;

    /** @var FileCorruptionAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileCorruptionAnalyzer;

    /** @var FileExtensionAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileExtensionAnalyzer;

    /** @var IDBConnection|\PHPUnit_Framework_MockObject_MockObject */
	protected $connection;

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
        $this->logger = $this->getMockBuilder('OCP\ILogger')
            ->getMock();
        $this->folder = $this->getMockBuilder('OCP\Files\Folder')
            ->getMock();
        $this->connection = $this->getMockBuilder('OCP\IDBConnection')
            ->getMock();
        $mapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\FileOperationMapper')
            ->setConstructorArgs([$this->connection])
            ->getMock();
        $this->service = $this->getMockBuilder('OCA\RansomwareDetection\Service\FileOperationService')
            ->setConstructorArgs([$mapper, $this->userId])
            ->getMock();
        $this->classifier = $this->getMockBuilder('OCA\RansomwareDetection\Classifier')
            ->setConstructorArgs([$this->logger, $mapper, $this->service])
            ->getMock();
        $sequenceSizeAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer')
            ->getMock();
        $fileTypeFunnellingAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer')
            ->getMock();
        $entropyFunnellingAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\EntropyFunnellingAnalyzer')
            ->setConstructorArgs([$this->logger])
            ->getMock();
        $this->sequenceAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\SequenceAnalyzer')
            ->setConstructorArgs([$sequenceSizeAnalyzer, $fileTypeFunnellingAnalyzer, $entropyFunnellingAnalyzer])
            ->setMethods(['analyze'])
            ->getMock();
        $rootFolder = $this->createMock(IRootFolder::class);
        $entropy = $this->createMock(Entropy::class);
        $this->entropyAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\EntropyAnalyzer')
            ->setConstructorArgs([$this->logger, $rootFolder, $entropy, $this->userId])
            ->getMock();
        $this->fileCorruptionAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer')
            ->setConstructorArgs([$this->logger, $rootFolder, $this->userId])
            ->getMock();
        $this->fileExtensionAnalyzer = $this->getMockBuilder('OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer')
            ->setConstructorArgs([$this->logger, $entropy])
            ->getMock();
    }

    public function dataRecover()
    {
        return [
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::DELETE, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => false, 'response' => Http::STATUS_OK],
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::DELETE, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => true, 'response' => Http::STATUS_OK],
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::WRITE, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => true, 'response' => Http::STATUS_OK],
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::WRITE, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => false, 'response' => Http::STATUS_OK],
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::CREATE, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => false, 'response' => Http::STATUS_BAD_REQUEST],
            ['id' => 4, 'sequence' => 5, 'command' => Monitor::RENAME, 'path' => '/', 'name' => 'test.pdf', 'timestamp' => 12345, 'restored' => false, 'response' => Http::STATUS_BAD_REQUEST],
        ];
    }

    /**
     * @dataProvider dataRecover
     *
     * @param integer       $id
     * @param integer       $sequence
     * @param integer       $command
     * @param string        $path
     * @param string        $name
     * @param integer       $timestamp
     * @param boolean       $restored
     * @param HttpResponse  $response
     */
    public function testRecover($id, $sequence, $command, $path, $name, $timestamp, $restored, $response)
    {
        $controller = $this->getMockBuilder(ScanController::class)
            ->setConstructorArgs(['ransomware_detection', $this->request, $this->userSession, $this->config, $this->classifier,
            $this->logger, $this->folder, $this->service, $this->sequenceAnalyzer, $this->entropyAnalyzer,
            $this->fileCorruptionAnalyzer, $this->fileExtensionAnalyzer, $this->connection, $this->userId])
            ->setMethods(['deleteFromStorage', 'restoreFromTrashbin'])
            ->getMock();

        $controller->expects($this->any())
            ->method('deleteFromStorage')
            ->willReturn($restored);

        $controller->expects($this->any())
            ->method('restoreFromTrashbin')
            ->willReturn($restored);

		$file = $this->getMockBuilder(File::class)
			->getMock();

		$this->folder->expects($this->any())
			->method('get')
			->willReturn($file);

		$file->expects($this->any())
			->method('isDeletable')
			->willReturn($restored);

		$file->expects($this->any())
			->method('delete')
			->willReturn($restored);

        $result = $controller->recover($id, $sequence, $command, $path, $name, $timestamp);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), $response);
    }

    public function testFilesToScan()
    {
        $controller = $this->getMockBuilder(ScanController::class)
            ->setConstructorArgs(['ransomware_detection', $this->request, $this->userSession, $this->config, $this->classifier,
            $this->logger, $this->folder, $this->service, $this->sequenceAnalyzer, $this->entropyAnalyzer,
            $this->fileCorruptionAnalyzer, $this->fileExtensionAnalyzer, $this->connection, $this->userId])
            ->setMethods(['getTrashFiles', 'getLastActivity'])
            ->getMock();

        $controller->expects($this->any())
            ->method('getTrashFiles')
            ->willReturn(array());

        $controller->expects($this->any())
            ->method('getLastActivity')
            ->willReturn(123);

        $this->folder->expects($this->any())
            ->method('getDirectoryListing')
            ->willReturn(array());

        $result = $controller->filesToScan();
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), Http::STATUS_OK);
    }

    public function dataScanSequence()
    {
        $fileOperation1 = new FileOperation();
        $fileOperation1->setCommand(Monitor::WRITE);
        $fileOperation1->setOriginalName('test.csv');
        $fileOperation1->setNewName('test.csv');
        $fileOperation1->setPath('files/test.csv');
        $fileOperation1->setSize(123000);
        $fileOperation1->setType('file');
        $fileOperation1->setMimeType('pdf');
        $fileOperation1->setCorrupted(1);
        $fileOperation1->setTimestamp(123);
        $fileOperation1->setSequence(1);
        $fileOperation1->setEntropy(7.9);
        $fileOperation1->setStandardDeviation(0.1);
        $fileOperation1->setFileClass(EntropyResult::NORMAL);
        $fileOperation1->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation1->setSuspicionClass(Classifier::SUSPICIOUS);

        $sequenceResult = new SequenceResult(1, 0.0, 1.1, 2.2, 4.5, []);

        return [
            ['sequence' => [], 'fileOperation' => new FileOperation(), 'sequenceResult' => $sequenceResult,'response' => Http::STATUS_OK],
            ['sequence' => [['timestamp' => 123, 'path' => '/files_trashbin/test.bin', 'id' => 1]], 'fileOperation' => $fileOperation1, 'sequenceResult' => $sequenceResult, 'response' => Http::STATUS_OK]
        ];
    }

    /**
     * @dataProvider dataScanSequence
     *
     * @param array          $sequence
     * @param sequenceResult $sequenceResult
     * @param FileOperation  $fileOperation
     * @param HttpResponse   $response
     */
    public function testScanSequence($sequence, $fileOperation, $sequenceResult, $response)
    {
        $controller = $this->getMockBuilder(ScanController::class)
            ->setConstructorArgs(['ransomware_detection', $this->request, $this->userSession, $this->config, $this->classifier,
            $this->logger, $this->folder, $this->service, $this->sequenceAnalyzer, $this->entropyAnalyzer,
            $this->fileCorruptionAnalyzer, $this->fileExtensionAnalyzer, $this->connection, $this->userId])
            ->setMethods(['getLastActivity', 'buildFileOperation'])
            ->getMock();

        $controller->expects($this->any())
            ->method('buildFileOperation')
            ->willReturn($fileOperation);

		$parentFolder = $this->getMockBuilder('OCP\Files\Folder')
            ->getMock();

		$this->folder->expects($this->any())
			->method('getParent')
			->willReturn($parentFolder);

		$file = $this->getMockBuilder('OCP\Files\File')
            ->getMock();

		$parentFolder->expects($this->any())
			->method('get')
			->willReturn($file);

		$file->expects($this->any())
			->method('getInternalPath')
			->willReturn('/files_trashbin/foo.bar');

        $this->sequenceAnalyzer->expects($this->any())
            ->method('analyze')
            ->willReturn($sequenceResult);

		$this->fileExtensionAnalyzer->expects($this->any())
			->method('analyze')
			->willReturn(new FileExtensionResult(FileExtensionResult::NOT_SUSPICIOUS));

		$this->fileCorruptionAnalyzer->expects($this->any())
			->method('analyze')
			->willReturn(new FileCorruptionResult(false));

		$this->entropyAnalyzer->expects($this->any())
			->method('analyze')
			->willReturn(new EntropyResult(EntropyResult::ENCRYPTED, 8.0, 0.04));

        $result = $controller->scanSequence($sequence);
        $this->assertTrue($result instanceof JSONResponse);
        $this->assertEquals($result->getStatus(), $response);
    }
}
