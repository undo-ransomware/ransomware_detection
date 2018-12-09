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

use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\ILogger;
use Test\TestCase;

class EntropyAnalyzerTest extends TestCase
{
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var IRootFolder|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootFolder;

    /** @var Entropy|\PHPUnit_Framework_MockObject_MockObject */
    protected $entropy;

    /** @var string */
    protected $userId = 'john';

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->rootFolder = $this->createMock(IRootFolder::class);
        $this->entropy = $this->createMock(Entropy::class);
    }

    public function dataAnalyze()
    {
        return [
            ['entropy' => 6.0, 'standardDeviation' => 0.0, 'class' => EntropyResult::NORMAL],
            ['entropy' => 7.91, 'standardDeviation' => 0.002, 'class' => EntropyResult::ENCRYPTED],
            ['entropy' => 7.91, 'standardDeviation' => 0.05, 'class' => EntropyResult::ENCRYPTED],
            ['entropy' => 7.91, 'standardDeviation' => 0.15, 'class' => EntropyResult::COMPRESSED],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param float $entropy
     * @param float $standardDeviation
     * @param int   $class
     */
    public function testAnalyze($entropy, $standardDeviation, $class)
    {
        $entropyAnalyzer = $this->getMockBuilder(EntropyAnalyzer::class)
            ->setConstructorArgs([$this->logger, $this->rootFolder, $this->entropy, $this->userId])
            ->setMethods(array('calculateEntropyOfFile', 'createEntropyArrayFromFile', 'calculateStandardDeviationOfEntropy'))
            ->getMock();

        $entropyAnalyzer->expects($this->any())
            ->method('calculateEntropyOfFile')
            ->willReturn($entropy);

        $entropyAnalyzer->expects($this->any())
            ->method('createEntropyArrayFromFile')
            ->willReturn([]);

        $entropyAnalyzer->expects($this->any())
            ->method('calculateStandardDeviationOfEntropy')
            ->willReturn($standardDeviation);

        $result = $entropyAnalyzer->analyze('test', $this->userId);
        $this->assertInstanceOf(EntropyResult::class, $result);
        $this->assertEquals($result->getFileClass(), $class);
        $this->assertEquals($result->getStandardDeviation(), $standardDeviation);
    }

    public function testCreateEntropyArrayFromFile()
    {
        $this->markTestSkipped('getContent was removed.');
        $entropyAnalyzer = $this->getMockBuilder(EntropyAnalyzer::class)
            ->setConstructorArgs([$this->logger, $this->rootFolder, $this->entropy, $this->userId])
            ->setMethods(array('createEntropyArrayFromData'))
            ->getMock();

        $node = $this->createMock(File::class);
        $node->method('getContent')
            ->willReturn('test');

        $userRoot = $this->createMock(Folder::class);
        $userRoot->method('get')
            ->willReturn($node);

        $userFolder = $this->createMock(Folder::class);
        $userFolder->method('getParent')
            ->willReturn($userRoot);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($userFolder);

        $entropyAnalyzer->method('createEntropyArrayFromData')
            ->willReturn([]);

        $this->assertEquals($this->invokePrivate($entropyAnalyzer, 'createEntropyArrayFromFile', [$node, EntropyAnalyzer::BLOCK_SIZE]), []);
    }

    public function testCalculateStandardDeviationOfEntropy()
    {
        $entropyAnalyzer = new EntropyAnalyzer($this->logger, $this->rootFolder, $this->entropy, $this->userId);

        $this->entropy->method('sd')
            ->willReturn(0.004);

        $this->assertEquals($this->invokePrivate($entropyAnalyzer, 'calculateStandardDeviationOfEntropy', [[]]), 0.004);
    }

    public function testCalculateEntropyOfFile()
    {
        $this->markTestSkipped('must be revisited.');
        $entropyAnalyzer = $this->getMockBuilder(EntropyAnalyzer::class)
            ->setConstructorArgs([$this->logger, $this->rootFolder, $this->entropy, 'john'])
            ->setMethods(array('calculateEntropy'))
            ->getMock();

        $node = $this->createMock(File::class);
        $node->method('getContent')
            ->willReturn('test');

        $userRoot = $this->createMock(Folder::class);
        $userRoot->method('get')
            ->willReturn($node);

        $userFolder = $this->createMock(Folder::class);
        $userFolder->method('getParent')
            ->willReturn($userRoot);

        $this->rootFolder->method('getUserFolder')
            ->willReturn($userFolder);

        $this->entropy->method('calculateEntropy')
            ->willReturn(4.1);

        $this->assertEquals($this->invokePrivate($entropyAnalyzer, 'calculateEntropyOfFile', [$node]), 4.1);
    }
}
