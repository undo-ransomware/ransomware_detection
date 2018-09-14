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

namespace OCA\RansomwareDetection\tests\Unit;

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileCorruptionResult;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCP\App\IAppManager;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Storage\IStorage;
use OCP\Notification\IManager;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IRequest;
use OCP\ISession;
use Test\TestCase;

class MonitorTest extends TestCase
{
    /** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var ITimeFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $time;

    /** @var IAppManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $appManager;

    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var IRootFolder|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootFolder;

    /** @var EntropyAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $entropyAnalyzer;

    /** @var FileOperationMapper|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var FileExtensionAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileExtensionAnalyzer;

    /** @var FileCorruptionAnalyzer|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileCorruptionAnalyzer;

    /** @var string */
    protected $userId = 'john';

    public function setUp()
    {
        parent::setUp();

        $this->request = $this->createMock(IRequest::class);
        $this->config = $this->createMock(IConfig::class);
        $this->time = $this->createMock(ITimeFactory::class);
        $this->appManager = $this->createMock(IAppManager::class);
        $this->logger = $this->createMock(ILogger::class);
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->entropyAnalyzer = $this->createMock(EntropyAnalyzer::class);
        $this->mapper = $this->createMock(FileOperationMapper::class);
        $this->fileExtensionAnalyzer = $this->createMock(FileExtensionAnalyzer::class);
        $this->fileCorruptionAnalyzer = $this->createMock(FileCorruptionAnalyzer::class);
    }

    public function dataAnalyze()
    {
        return [
            ['paths' => ['files/', 'files/'], 'mode' => Monitor::RENAME, 'userAgent' => true, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => Monitor::RENAME, 'userAgent' => false, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => Monitor::RENAME, 'userAgent' => true, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => Monitor::READ, 'userAgent' => true, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => Monitor::WRITE, 'userAgent' => true, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => Monitor::DELETE, 'userAgent' => true, 'timestamp' => time()],
            ['paths' => ['/admin/files/test/files.extension', 'files/'], 'mode' => 100, 'userAgent' => true, 'timestamp' => time()],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param array $paths
     * @param int   $mode
     * @param bool  $userAgent
     * @param int   $timestamp
     */
    public function testAnalyze($paths, $mode, $userAgent, $timestamp)
    {
        $monitor = $this->getMockBuilder(Monitor::class)
            ->setConstructorArgs([$this->request, $this->config, $this->time,
                $this->appManager, $this->logger, $this->rootFolder,
                $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
                $this->fileCorruptionAnalyzer, $this->userId])
            ->setMethods(['isUploadedFile', 'isCreatingSkeletonFiles', 'classifySequence', 'resetProfindCount'])
            ->getMock();

        $storage = $this->createMock(IStorage::class);

        $monitor->expects($this->any())
            ->method('isUploadedFile')
            ->with($storage, $paths[0])
            ->willReturn(true);

        $monitor->expects($this->any())
            ->method('isCreatingSkeletonFiles')
            ->willReturn(false);

        $monitor->expects($this->any())
            ->method('classifySequence');

        $monitor->expects($this->any())
            ->method('resetProfindCount');

        $entropyResult = new EntropyResult(EntropyResult::COMPRESSED, 7.99, 0.004);

        $this->entropyAnalyzer->method('analyze')
            ->willReturn($entropyResult);

        $fileExtensionResult = new FileExtensionResult(FileExtensionResult::NOT_SUSPICIOUS, true, 4.0);

        $this->fileExtensionAnalyzer->method('analyze')
            ->willReturn($fileExtensionResult);

        $this->request->method('isUserAgent')
            ->willReturn($userAgent);

        $node = $this->createMock(File::class);
        $node->method('getInternalPath')
            ->willReturn('/admin/files/test.file');

        $userRoot = $this->createMock(Folder::class);
        $userRoot->method('get')
            ->willReturn($node);

        $folder = $this->createMock(Folder::class);
        $folder->method('getParent')
            ->willReturn($userRoot);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($folder);

        $fileOperation = new FileOperation();
        $fileOperation->setTimestamp($timestamp);

        $entity = new FileOperation();
        $entity->id = 1;

        $this->mapper->method('insert')
            ->willReturn($entity);

        $fileCorruptionResult = new FileCorruptionResult(true);
        $this->fileCorruptionAnalyzer->method('analyze')
            ->willReturn($fileCorruptionResult);

        $monitor->analyze($storage, $paths, $mode);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param array $paths
     * @param int   $mode
     * @param bool  $userAgent
     * @param int   $timestamp
     */
    public function testAnalyzeNotFoundException($paths, $mode, $userAgent, $timestamp)
    {
        $monitor = $this->getMockBuilder(Monitor::class)
            ->setConstructorArgs([$this->request, $this->config, $this->time,
                $this->appManager, $this->logger, $this->rootFolder,
                $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
                $this->fileCorruptionAnalyzer, $this->userId])
            ->setMethods(['isUploadedFile', 'isCreatingSkeletonFiles', 'resetProfindCount'])
            ->getMock();

        $storage = $this->createMock(IStorage::class);

        $monitor->expects($this->any())
            ->method('isUploadedFile')
            ->with($storage, $paths[0])
            ->willReturn(true);

        $monitor->expects($this->any())
            ->method('isCreatingSkeletonFiles')
            ->willReturn(false);

        $this->request->method('isUserAgent')
            ->willReturn($userAgent);

        $monitor->expects($this->any())
            ->method('resetProfindCount');

        $node = $this->createMock(Folder::class);
        $node->method('getInternalPath')
            ->willReturn('/admin/files/test.file');

        $userRoot = $this->createMock(Folder::class);
        $userRoot->method('get')
            ->willReturn($node);

        $folder = $this->createMock(Folder::class);
        $folder->method('getParent')
            ->willReturn($userRoot);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($folder);

        $fileOperation = new FileOperation();
        $fileOperation->setTimestamp($timestamp);

        $entity = new FileOperation();
        $entity->id = 1;

        $this->mapper->method('insert')
            ->willReturn($entity);

        $fileCorruptionResult = new FileCorruptionResult(true);
        $this->fileCorruptionAnalyzer->method('analyze')
            ->willReturn($fileCorruptionResult);

        $monitor->analyze($storage, $paths, $mode);
        $this->assertTrue(true);
    }

    public function dataGetFileSize()
    {
        return [
            ['path' => '/files_trashbin/test.jpg', 'size' => 10],
            ['path' => '/files/test.jpg', 'size' => 10],
        ];
    }

    /**
     * @dataProvider dataGetFileSize
     *
     * @param string $path
     * @param int    $size
     */
    public function testGetFileSize($path, $size)
    {
        $getFileSize = self::getMethod('getFileSize');

        $monitor = new Monitor($this->request, $this->config, $this->time,
            $this->appManager, $this->logger, $this->rootFolder,
            $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
            $this->fileCorruptionAnalyzer, $this->userId);

        $node = $this->createMock(File::class);
        $node->method('getSize')
            ->willReturn($size);

        $this->rootFolder->method('get')
            ->willReturn($node);

        $folder = $this->createMock(Folder::class);
        $userRoot = $this->createMock(Folder::class);
        $folder->method('getParent')
            ->willReturn($userRoot);

        $userRoot->method('get')
            ->willReturn($node);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($folder);

        $this->assertEquals($getFileSize->invokeArgs($monitor, [$path]), $size);
    }

    public function dataGetFileSizeNotFoundException()
    {
        return [
            ['path' => '/files_trashbin/test.jpg'],
            ['path' => '/files/test.jpg'],
        ];
    }

    /**
     * @expectedException OCP\Files\NotFoundException
     * @dataProvider dataGetFileSizeNotFoundException
     *
     * @param string $path
     */
    public function testGetFileSizeNotFoundException($path)
    {
        $getFileSize = self::getMethod('getFileSize');

        $monitor = new Monitor($this->request, $this->config, $this->time,
            $this->appManager, $this->logger, $this->rootFolder,
            $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
            $this->fileCorruptionAnalyzer, $this->userId);

        $node = $this->createMock(Folder::class);

        $this->rootFolder->method('get')
            ->willReturn($node);

        $folder = $this->createMock(Folder::class);
        $userRoot = $this->createMock(Folder::class);
        $folder->method('getParent')
            ->willReturn($userRoot);

        $userRoot->method('get')
            ->willReturn($node);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($folder);

        $getFileSize->invokeArgs($monitor, [$path]);
    }

    public function dataIsUploadedFile()
    {
        return [
            ['path' => '/files/files.ocTransferId1234', 'return' => false],
            ['path' => '/files/files.extension', 'return' => false],
            ['path' => '/admin/files/test/files.extension', 'return' => true],
            ['path' => '/admin/thumbnails/test/files.extension', 'return' => true],
            ['path' => '/admin/files_versions/test/files.extension', 'return' => true],
        ];
    }

    /**
     * @dataProvider dataIsUploadedFile
     *
     * @param string $path
     * @param bool   $return
     */
    public function testIsUploadedFile($path, $return)
    {
        $monitor = new Monitor($this->request, $this->config, $this->time,
            $this->appManager, $this->logger, $this->rootFolder,
            $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
            $this->fileCorruptionAnalyzer, $this->userId);

        $isUploadedFile = self::getMethod('isUploadedFile');
        $storage = $this->createMock(IStorage::class);
        $this->assertEquals($isUploadedFile->invokeArgs($monitor, [$storage, $path]), $return);
    }

    public function testIsCreatingSkeletonFiles()
    {
        $monitor = new Monitor($this->request, $this->config, $this->time,
            $this->appManager, $this->logger, $this->rootFolder,
            $this->entropyAnalyzer, $this->mapper, $this->fileExtensionAnalyzer,
            $this->fileCorruptionAnalyzer, $this->userId);

        $isCreateingSkeletonFiles = self::getMethod('isCreatingSkeletonFiles');
        $this->assertFalse($isCreateingSkeletonFiles->invokeArgs($monitor, []));
    }

    /**
     * Get protected method.
     *
     * @param string $name
     *
     * @return $method
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass(Monitor::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Sets a protected property on a given object via reflection.
     *
     * @param $object - instance in which protected value is being modified
     * @param $property - property on instance being modified
     * @param $value - new value of the property being modified
     */
    public static function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}
