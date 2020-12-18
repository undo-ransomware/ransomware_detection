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

namespace OCA\RansomwareDetection\tests\Unit\Service;

use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Db\RecoveredFileOperationMapper;
use OCA\RansomwareDetection\Tests\Unit\Db\MapperTestUtility;

class FileOperationServiceTest extends MapperTestUtility
{
    /** @var FileOperationService */
    protected $service;

    /** @var FileOperationMapper */
    protected $mapper;

    /** @var RecoveredFileOperationMapper */
    protected $recoveredMapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper = new FileOperationMapper($this->db);
        $connection = $this->getMockBuilder('OCP\IDBConnection')
            ->getMock();
        $this->recoveredMapper = $this->getMockBuilder('OCA\RansomwareDetection\Db\RecoveredFileOperationMapper')
            ->setConstructorArgs([$connection])
            ->getMock();
        $this->recoveredMapper->method('insert')
            ->willReturn(true);
        $this->service = new FileOperationService($this->mapper, $this->recoveredMapper, 'john');

        // create mock FileOperation
        $fileOperation1 = new FileOperation();
        $fileOperation2 = new FileOperation();

        $this->fileOperations = [$fileOperation1, $fileOperation2];

        $this->twoRows = [
            ['id' => $this->fileOperations[0]->getId()],
            ['id' => $this->fileOperations[1]->getId()],
        ];
    }

    public function testFind()
    {
        $userId = 'john';
        $id = 3;
        $rows = [['id' => $this->fileOperations[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `id` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId], $rows);

        $result = $this->service->find($id);
        $this->assertEquals($this->fileOperations[0], $result);
    }

    public function testFindNotFound()
    {
        $this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `id` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId]);
        $this->service->find($id);
    }

    public function testFindMoreThanOneResultFound()
    {
        $this->expectException(\OCP\AppFramework\Db\MultipleObjectsReturnedException::class);
        $userId = 'john';
        $id = 3;
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `id` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId], $rows);
        $this->service->find($id);
    }

    public function testFindOneByFileName()
    {
        $userId = 'john';
        $name = 'test';
        $rows = [['id' => $this->fileOperations[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId], $rows);

        $result = $this->service->findOneByFileName($name);
        $this->assertEquals($this->fileOperations[0], $result);
    }

    public function testFindOneByFileNameNotFound()
    {
        $this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
        $userId = 'john';
        $name = 'test';
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId]);
        $this->service->findOneByFileName($name);
    }

    public function testFindOneByFileNameMoreThanOneResultFound()
    {
        $this->expectException(\OCP\AppFramework\Db\MultipleObjectsReturnedException::class);
        $userId = 'john';
        $name = 'test';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId], $rows);
        $this->service->findOneByFileName($name);
    }

    public function testFindOneWithHighestId()
    {
        $userId = 'john';
        $rows = [['id' => $this->fileOperations[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId], $rows);

        $result = $this->service->findOneWithHighestId();
        $this->assertEquals($this->fileOperations[0], $result);
    }

    public function testFindOneWithHighestIdNotFound()
    {
        $this->expectException(\OCP\AppFramework\Db\DoesNotExistException::class);
        $userId = 'john';
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId]);
        $this->service->findOneWithHighestId();
    }

    public function testFindOneWithHighestIdMoreThanOneResultFound()
    {
        $this->expectException(\OCP\AppFramework\Db\MultipleObjectsReturnedException::class);
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId], $rows);
        $this->service->findOneWithHighestId();
    }

    public function testFindAll()
    {
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?';

        $this->setMapperResult($sql, [$userId], $rows);
        $result = $this->service->findAll();
        $this->assertEquals($this->fileOperations, $result);
    }

    public function testFindSequenceById()
    {
        $userId = 'john';
        $sequence = '1';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `sequence` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$sequence, $userId], $rows);
        $result = $this->service->findSequenceById([$sequence]);
        $this->assertEquals($this->fileOperations, $result);
    }
    /*
    public function testDeleteById()
    {
        $userId = 'john';
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($userId);
        $fileOperation->setId(3);

        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `id` = ? AND `user_id` = ?';
        $rows = [['id' => $fileOperation->getId()]];
        $this->setMapperResult($sql, [$fileOperation->getId(), $userId], $rows);

        $sql2 = 'DELETE FROM `*PREFIX*ransomware_detection` WHERE `id` = ? AND `user_id` = ?';
        $this->setMapperResult($sql2, [$fileOperation->getId(), $userId], [], null, null, true);

        $this->service->deleteById($fileOperation->getId());
    }

    public function testDeleteSequenceById()
    {
        $userId = 'john';
        $fileOperation = new FileOperation();
        $fileOperation->setId(3);
        $fileOperation->setUserId($userId);
        $fileOperation->setSequence(1);

        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `sequence` = ? AND `user_id` = ?';
        $rows = [['id' => $fileOperation->getId()]];
        $this->setMapperResult($sql, [$fileOperation->getSequence(), $userId], $rows);

        $sql2 = 'DELETE FROM `*PREFIX*ransomware_detection` WHERE `sequence` = ? AND `user_id` = ?';
        $this->setMapperResult($sql2, [$fileOperation->getSequence(), $userId], [], null, null, true);

        $this->service->deleteSequenceById($fileOperation->getSequence());
    }
    */
    public function testDeleteFileOperationsBefore()
    {
        $userId = 'john';
        $fileOperation = new FileOperation();
        $fileOperation->setId(3);
        $fileOperation->setUserId($userId);
        $fileOperation->setSequence(1);
        $fileOperation->setTimestamp(strtotime('-1 week'));

        $sql = 'DELETE FROM `*PREFIX*ransomware_detection` WHERE `timestamp` < ?';
        $time = time();
        $arguments = [$time];

        $this->setMapperResult($sql, $arguments, [], null, null, true);

        $this->service->deleteFileOperationsBefore($time);
    }
}
