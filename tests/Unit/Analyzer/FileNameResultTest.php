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

use OCA\RansomwareDetection\Analyzer\FileNameResult;
use Test\TestCase;

class FileNameResultTest extends TestCase
{
    /** @var FileNameResult */
     protected $fileNameResult;

    public function setUp()
    {
        parent::setUp();

        $this->fileNameResult = new FileNameResult(FileNameResult::NORMAL, true, 3.0);
    }

    public function testConstruct()
    {
        $this->assertEquals($this->fileNameResult->getFileNameClass(), FileNameResult::NORMAL);
        $this->assertEquals($this->fileNameResult->isFileExtensionKnown(), true);
        $this->assertEquals($this->fileNameResult->getEntropyOfFileName(), 3.0);
    }

    public function testFileNameClass()
    {
        $this->fileNameResult->setFileNameClass(FileNameResult::SUSPICIOUS_FILE_EXTENSION);
        $this->assertEquals($this->fileNameResult->getFileNameClass(), FileNameResult::SUSPICIOUS_FILE_EXTENSION);
        $this->fileNameResult->setFileNameClass(FileNameResult::SUSPICIOUS_FILE_NAME);
        $this->assertEquals($this->fileNameResult->getFileNameClass(), FileNameResult::SUSPICIOUS_FILE_NAME);
        $this->fileNameResult->setFileNameClass(FileNameResult::SUSPICIOUS);
        $this->assertEquals($this->fileNameResult->getFileNameClass(), FileNameResult::SUSPICIOUS);
    }

    public function testIsFileExtensionKnown()
    {
        $this->fileNameResult->setFileExtensionKnown(true);
        $this->assertEquals($this->fileNameResult->isFileExtensionKnown(), true);
        $this->fileNameResult->setFileExtensionKnown(false);
        $this->assertEquals($this->fileNameResult->isFileExtensionKnown(), false);
    }

    public function testEntropyOfFileName()
    {
        $this->assertEquals($this->fileNameResult->getEntropyOfFileName(), 3.0);
        $this->fileNameResult->setEntropyOfFileName(3.1);
        $this->assertEquals($this->fileNameResult->getEntropyOfFileName(), 3.1);
    }
}
