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

namespace OCA\RansomwareDetection\tests\Unit\Scanner;

use OCA\RansomwareDetection\Scanner\StorageStructure;
use Test\TestCase;

class StorageStructureTest extends TestCase
{
    /** @var StorageStructure */
    protected $storageStructure;

    public function setUp(): void
    {
        parent::setUp();

        $this->storageStructure = new StorageStructure();
    }

    public function testDefaultParameters() {
        $this->assertEquals($this->storageStructure->getNumberOfFiles(), 0);
        $this->assertEquals($this->storageStructure->getFiles(), []);
    }

    public function testGetNumberOfFiles() {
        $this->assertEquals($this->storageStructure->getNumberOfFiles(), 0);
    }

    public function testSetNumberOfFiles() {
        $this->storageStructure->setNumberOfFiles(10);
        $this->assertEquals($this->storageStructure->getNumberOfFiles(), 10);
    }

    public function testIncreaseNumberOfFiles() {
        $this->storageStructure->increaseNumberOfFiles();
        $this->assertEquals($this->storageStructure->getNumberOfFiles(), 1);
    }

    public function testGetFiles() {
        $this->assertEquals($this->storageStructure->getFiles(), []);
    }

    public function testSetFiles() {
        $this->storageStructure->setFiles([10]);
        $this->assertEquals($this->storageStructure->getFiles(), [10]);
    }

    public function testAddFiles() {
        $this->storageStructure->addFile(11);
        $this->storageStructure->addFile(10);
        $this->assertEquals($this->storageStructure->getFiles(), [11, 10]);
    }
}
