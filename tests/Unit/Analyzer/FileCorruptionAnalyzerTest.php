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

namespace OCA\RansomwareDetection\tests\Unit\Analyzer;

use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\ILogger;
use Test\TestCase;

class FileCorruptionAnalyzerTest extends TestCase
{
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var IRootFolder|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootFolder;

    /** @var string */
    protected $userId = 'john';

    /** @var FileCorruptionAnalyzer */
    protected $fileCorruptionAnalyzer;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->rootFolder = $this->createMock(IRootFolder::class);

        $this->fileCorruptionAnalyzer = $this->getMockBuilder(FileCorruptionAnalyzer::class)
            ->setConstructorArgs([$this->logger, $this->rootFolder, $this->userId])
            ->setMethods(array('isCorrupted'))
            ->getMock();
    }

    public function dataAnalyze()
    {
        return [
            ['isCorrupted' => true],
            ['isCorrupted' => false],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param bool $isCorrupted
     */
    public function testAnalyze($isCorrupted)
    {
        $this->markTestSkipped('must be revisited.');
        $file = $this->createMock(File::class);
        $file->method('getContent')
            ->willReturn('test');

        $userRoot = $this->createMock(Folder::class);
        $userRoot->method('get')
            ->willReturn($file);

        $userFolder = $this->createMock(Folder::class);
        $userFolder->method('getParent')
            ->willReturn($userRoot);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($userFolder);

        $this->fileCorruptionAnalyzer->method('isCorrupted')
            ->willReturn($isCorrupted);

        $result = $this->fileCorruptionAnalyzer->analyze($file);

        $this->assertEquals($isCorrupted, $result);
    }

    public function dataIsCorrupted()
    {
        return [
            ['data' => 'ffff', 'extension' => 'unknown', 'result' => false],
            ['data' => 'ffd8ffffffff', 'extension' => 'csv', 'result' => false],
            ['data' => 'ffd8ffe000104a46494600ffffffd9', 'extension' => 'jpg', 'result' => false],
            ['data' => 'FFD8FFE136B5457869660000ffffffd9', 'extension' => 'jpg', 'result' => false],
            ['data' => 'ffd8ffe000104a46494600ffff', 'extension' => 'jpg', 'result' => true],
            ['data' => '25504446ff0d2525454f460d', 'extension' => 'pdf', 'result' => false],
            ['data' => 'ffff', 'extension' => 'jpg', 'result' => true],
            ['data' => '69616d67726f6f74', 'extension' => 'txt', 'result' => false],
        ];
    }

    /**
     * @dataProvider dataIsCorrupted
     *
     * @param string $data
     * @param string $extension
     * @param bool   $result
     */
    public function testIsCorrupted($data, $extension, $result)
    {
        $this->markTestSkipped('must be revisited.');
        $isCorrupted = self::getMethod('isCorrupted');

        $node = $this->createMock(File::class);

        $node->expects($this->once())
            ->method('getContent')
            ->willReturn(hex2bin($data));

        $node->expects($this->any())
            ->method('getPath')
            ->willReturn('/admin/files/file.'.$extension);

        $this->assertEquals($isCorrupted->invokeArgs($this->fileCorruptionAnalyzer, [$node])->isCorrupted(), $result);
    }

    public function testIsCorruptedCatchException()
    {
        $this->markTestSkipped('must be revisited.');
        $isCorrupted = self::getMethod('isCorrupted');

        $node = $this->createMock(File::class);

        $node->expects($this->once())
            ->method('getContent')
            ->will($this->throwException(new \OCP\Files\NotPermittedException()));

        $this->assertEquals($isCorrupted->invokeArgs($this->fileCorruptionAnalyzer, [$node])->isCorrupted(), false);
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
        $class = new \ReflectionClass(FileCorruptionAnalyzer::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
