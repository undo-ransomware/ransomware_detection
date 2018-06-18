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

use OCA\RansomwareDetection\Entropy\Entropy;
use OCA\RansomwareDetection\Analyzer\FileNameResult;
use OCA\RansomwareDetection\FileSignatureList;
use OCA\RansomwareDetection\Analyzer\FileNameAnalyzer;
use OCP\ILogger;
use Test\TestCase;

class FileNameAnalyzerTest extends TestCase
{
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var Entropy|\PHPUnit_Framework_MockObject_MockObject */
    protected $entropy;

    /** @var FileNameAnalyzer */
    protected $fileNameAnalyzer;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->entropy = $this->createMock(Entropy::class);

        $this->fileNameAnalyzer = new FileNameAnalyzer($this->logger, $this->entropy);
    }

    public function dataAnalyze()
    {
        return [
            ['path' => 'file.jpg', 'class' => FileNameResult::NORMAL, 'isFileExtensionKnown' => true, 'entropyOfFileName' => 1.0],
            ['path' => 'file.unknown', 'class' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'isFileExtensionKnown' => false, 'entropyOfFileName' => 1.0],
            ['path' => 'file.jpg', 'class' => FileNameResult::SUSPICIOUS_FILE_NAME, 'isFileExtensionKnown' => true, 'entropyOfFileName' => 6.0],
            ['path' => 'file.unknown', 'class' => FileNameResult::SUSPICIOUS, 'isFileExtensionKnown' => false, 'entropyOfFileName' => 6.0],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param string $path
     * @param int    $class
     * @param bool   $isFileExtensionKnown
     * @param float  $entropyOfFileName
     */
    public function testAnalyze($path, $class, $isFileExtensionKnown, $entropyOfFileName)
    {
        $this->entropy->method('calculateEntropy')
            ->willReturn($entropyOfFileName);
        $result = $this->fileNameAnalyzer->analyze($path);
        $this->assertInstanceOf(FileNameResult::class, $result);
        $this->assertEquals($result->getFileNameClass(), $class);
        $this->assertEquals($result->isFileExtensionKnown(), $isFileExtensionKnown);
        $this->assertEquals($result->getEntropyOfFileName(), $entropyOfFileName);
    }

    public function dataIsFileExtensionKnown()
    {
        $signatures = FileSignatureList::getSignatures();
        $extensions = [];
        foreach ($signatures as $signature) {
            foreach ($signature['extension'] as $extension) {
                $extensions[] = $extension;
            }
        }
        $tests = [];

        foreach ($extensions as $extension) {
            $tests[] = [$extension, true];
        }
        $tests[] = ['WNCRY', false];

        return $tests;
    }

    /**
     * @dataProvider dataIsFileExtensionKnown
     *
     * @param string $extension
     * @param bool   $return
     */
    public function testIsFileExtensionKnown($extension, $return)
    {
        $this->assertEquals($this->invokePrivate($this->fileNameAnalyzer, 'isFileExtensionKnown', [$extension]), $return);
    }

    public function testGetFileName()
    {
        $this->assertEquals($this->invokePrivate($this->fileNameAnalyzer, 'getFileName', ['/test/filename.extension']), 'filename.extension');
    }

    public function testGetFileExtension()
    {
        $this->assertEquals($this->invokePrivate($this->fileNameAnalyzer, 'getFileExtension', ['filename.extension']), 'extension');
    }

    public function testCalculateEntropyOfFileName()
    {
        $this->entropy->method('calculateEntropy')
            ->willReturn('6.00');
        $this->assertEquals($this->invokePrivate($this->fileNameAnalyzer, 'calculateEntropyOfFileName', ['filename.extension']), '6.00');
    }
}
