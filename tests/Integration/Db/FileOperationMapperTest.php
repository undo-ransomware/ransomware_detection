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

namespace OCA\RansomwareDetection\tests\Integration\Db;

use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Tests\Integration\AppTest;
use OCA\RansomwareDetection\Tests\Integration\Fixtures\FileOperationFixture;

class FileOperationMapperTest extends AppTest
{
    public function testFind()
    {
        $fileOperation = new FileOperationFixture();
        $fileOperation = $this->fileOperationMapper->insert($fileOperation);

        $fetched = $this->fileOperationMapper->find($fileOperation->getId(), $this->userId);

        $this->assertInstanceOf(FileOperation::class, $fetched);
        $this->assertEquals($fileOperation->getOriginalName(), $fetched->getOriginalName());
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotExisting()
    {
        $this->fileOperationMapper->find(0, $this->userId);
    }

    public function testFindOneByFileName()
    {
        $fileOperation = new FileOperationFixture();
        $fileOperation = $this->fileOperationMapper->insert($fileOperation);

        $fetched = $this->fileOperationMapper->findOneByFileName($fileOperation->getOriginalName(), $this->userId);

        $this->assertInstanceOf(FileOperation::class, $fetched);
        $this->assertEquals($fileOperation->getOriginalName(), $fetched->getOriginalName());
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindOneByFileNameNotExisting()
    {
        $this->fileOperationMapper->findOneByFileName('notthedruidwearelookingfor', $this->userId);
    }

    public function testFindOneWithHighestId()
    {
        $fileOperation = new FileOperationFixture();
        $fileOperation = $this->fileOperationMapper->insert($fileOperation);

        $fetched = $this->fileOperationMapper->findOneWithHighestId($this->userId);

        $this->assertInstanceOf(FileOperation::class, $fetched);
        $this->assertEquals($fileOperation->getOriginalName(), $fetched->getOriginalName());
    }

    /**
     * @expectedException OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindOneWithHighestIdNotExisting()
    {
        $this->fileOperationMapper->findOneWithHighestId($this->userId);
    }

    public function testFindAll()
    {
        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat2.gif',
                'newName' => 'cat2.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);
        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(FileOperation::class, $fetched);
    }

    public function testFindAllFromUserNotExisting()
    {
        $fetched = $this->fileOperationMapper->findAll(['notexistinguser']);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(0, $fetched);
    }

    public function testFindSequenceById()
    {
        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat2.gif',
                'newName' => 'cat2.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);
        $fetched = $this->fileOperationMapper->findSequenceById([1, $this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(FileOperation::class, $fetched);
    }

    public function testFindSequenceByIdNotFound()
    {
        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat2.gif',
                'newName' => 'cat2.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'corrupted' => false,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);
        $fetched = $this->fileOperationMapper->findSequenceById([2, $this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(0, $fetched);
    }

    public function testFindSequenceByIdFromUserNotExisting()
    {
        $fetched = $this->fileOperationMapper->findSequenceById([1, 'notexistinguser']);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(0, $fetched);
    }

    public function testDeleteById()
    {
        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat2.gif',
                'newName' => 'cat2.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);

        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(2, $fetched);
        $this->assertContainsOnlyInstancesOf(FileOperation::class, $fetched);

        $this->fileOperationMapper->deleteById($this->fileOperationMapper->findOneWithHighestId($this->userId)->getId(), $this->userId);
        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(1, $fetched);
        $this->assertContainsOnlyInstancesOf(FileOperation::class, $fetched);

        $this->fileOperationMapper->deleteById($this->fileOperationMapper->findOneWithHighestId($this->userId)->getId(), $this->userId);
        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(0, $fetched);
    }

    public function testDeleteSequenceById()
    {
        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat2.gif',
                'newName' => 'cat2.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);

        $this->fileOperationMapper->deleteSequenceById(1, $this->userId);
        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(0, $fetched);

        $fileOperations = [
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 158000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.04,
            ],
            [
                'userId' => 'john',
                'path' => 'files/',
                'originalName' => 'cat1.gif',
                'newName' => 'cat1.gif',
                'type' => 'file',
                'mimeType' => 'image/gif',
                'size' => 148000,
                'timestamp' => date_timestamp_get(date_create()),
                'command' => 2,
                'entropy' => 7.9123595,
                'standardDeviation' => 0.05,
            ],
        ];
        $this->loadFixtures($fileOperations);

        $this->fileOperationMapper->deleteSequenceById(1, $this->userId);
        $fetched = $this->fileOperationMapper->findAll([$this->userId]);
        $this->assertInternalType('array', $fetched);
        $this->assertCount(1, $fetched);
        $this->assertContainsOnlyInstancesOf(FileOperation::class, $fetched);
    }
}
