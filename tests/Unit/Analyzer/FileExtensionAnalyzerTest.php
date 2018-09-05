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
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\FileSignatureList;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCP\ILogger;
use Test\TestCase;

class FileExtensionAnalyzerTest extends TestCase
{
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var FileExtensionAnalyzer */
    protected $fileExtensionAnalyzer;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);

        $this->fileExtensionAnalyzer = new FileExtensionAnalyzer($this->logger);
    }

    public function dataAnalyze()
    {
        return [
            ['path' => 'file.jpg', 'class' => FileExtensionResult::NOT_SUSPICIOUS],
            ['path' => 'file.unknown', 'class' => FileExtensionResult::SUSPICIOUS],
            ['path' => 'file.jpg', 'class' => FileExtensionResult::NOT_SUSPICIOUS],
            ['path' => 'file.jpg1', 'class' => FileExtensionResult::SUSPICIOUS],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param string $path
     * @param int    $class
     */
    public function testAnalyze($path, $class)
    {
        $result = $this->fileExtensionAnalyzer->analyze($path);
        $this->assertInstanceOf(FileExtensionResult::class, $result);
        $this->assertEquals($result->getFileExtensionClass(), $class);
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
        $this->assertEquals($this->invokePrivate($this->fileExtensionAnalyzer, 'isFileExtensionKnown', [$extension]), $return);
    }

    public function testGetFileExtension()
    {
        $this->assertEquals($this->invokePrivate($this->fileExtensionAnalyzer, 'getFileExtension', ['filename.extension']), 'extension');
    }
}
