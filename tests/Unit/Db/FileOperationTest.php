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

namespace OCA\RansomwareDetection\tests\Unit\Db;

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Db\FileOperation;
use Test\TestCase;

class FileOperationTest extends TestCase
{
    /** @var FileOperation */
    protected $entity;

    public function setUp()
    {
        parent::setUp();

        $this->entity = new FileOperation();
    }

    public function dataFileOperation()
    {
        $data = [
            ['field' => 'userId', 'value' => 'john'],
            ['field' => 'path', 'value' => 'files/'],
            ['field' => 'originalName', 'value' => 'test.jpg'],
            ['field' => 'newName', 'value' => 'test.jpg'],
            ['field' => 'type', 'value' => 'file'],
            ['field' => 'mimeType', 'value' => 'image/jpg'],
            ['field' => 'size', 'value' => 123],
            ['field' => 'timestamp', 'value' => new \DateTime()],
            ['field' => 'command', 'value' => Monitor::WRITE],
            ['field' => 'command', 'value' => Monitor::READ],
            ['field' => 'command', 'value' => Monitor::RENAME],
            ['field' => 'command', 'value' => Monitor::DELETE],
            ['field' => 'entropy', 'value' => 7.99],
            ['field' => 'standardDeviation', 'value' => 0.004],
        ];

        return $data;
    }

    /**
     * @dataProvider dataFileOperation
     *
     * @param string $field
     * @param mixed  $value
     */
    public function testFileOperation($field, $value)
    {
        $setMethod = 'set'.ucfirst($field);
        $this->entity->$setMethod($value);
        $getMethod = 'get'.ucfirst($field);
        $this->assertEquals($this->entity->$getMethod(), $value);
    }
}
