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

use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Db\FileOperationMapper;

class FileOperationMapperTest extends MapperTestUtility
{
    /** @var FileOperationMapper */
    protected $mapper;

    /** @var array */
    protected $fileOperations;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new FileOperationMapper($this->db);

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

        $result = $this->mapper->find($id, $userId);
        $this->assertEquals($this->fileOperations[0], $result);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindNotFound()
    {
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `id` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId]);
        $this->mapper->find($id, $userId);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function testFindMoreThanOneResultFound()
    {
        $userId = 'john';
        $id = 3;
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `id` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId], $rows);
        $this->mapper->find($id, $userId);
    }

    public function testFindOneByFileName()
    {
        $userId = 'john';
        $name = 'test';
        $rows = [['id' => $this->fileOperations[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId], $rows);

        $result = $this->mapper->findOneByFileName($name, $userId);
        $this->assertEquals($this->fileOperations[0], $result);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindOneByFileNameNotFound()
    {
        $userId = 'john';
        $name = 'test';
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId]);
        $this->mapper->findOneByFileName($name, $userId);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function testFindOneByFileNameMoreThanOneResultFound()
    {
        $userId = 'john';
        $name = 'test';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        $this->setMapperResult($sql, [$name, $userId], $rows);
        $this->mapper->findOneByFileName($name, $userId);
    }

    public function testFindOneWithHighestId()
    {
        $userId = 'john';
        $rows = [['id' => $this->fileOperations[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId], $rows);

        $result = $this->mapper->findOneWithHighestId($userId);
        $this->assertEquals($this->fileOperations[0], $result);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\DoesNotExistException
     */
    public function testFindOneWithHighestIdNotFound()
    {
        $userId = 'john';
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId]);
        $this->mapper->findOneWithHighestId($userId);
    }

    /**
     * @expectedException \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function testFindOneWithHighestIdMoreThanOneResultFound()
    {
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        $this->setMapperResult($sql, [$userId], $rows);
        $this->mapper->findOneWithHighestId($userId);
    }

    public function testFindAll()
    {
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?';

        $this->setMapperResult($sql, [$userId], $rows);
        $result = $this->mapper->findAll([$userId]);
        $this->assertEquals($this->fileOperations, $result);
    }

    public function testDelete()
    {
        $fileOperation = new FileOperation();
        $fileOperation->setId(3);

        $sql = 'DELETE FROM `*PREFIX*ransomware_detection` WHERE `id` = ?';
        $arguments = [$fileOperation->getId()];

        $this->setMapperResult($sql, $arguments, [], null, null, true);

        $this->mapper->delete($fileOperation);
    }

    public function testDeleteById()
    {
        $userId = 'john';
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($userId);
        $fileOperation->setId(3);

        $sql = 'DELETE FROM `*PREFIX*ransomware_detection` WHERE `id` = ? AND `user_id` = ?';
        $arguments = [$fileOperation->getId(), $userId];

        $this->setMapperResult($sql, $arguments, [], null, null, true);

        $this->mapper->deleteById($fileOperation->getId(), $userId);
    }
}
