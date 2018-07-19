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

namespace OCA\RansomwareDetection\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class FileOperationMapper extends Mapper
{
    /**
     * @param IDBConnection $db
     */
    public function __construct(
        IDBConnection $db
    ) {
        parent::__construct($db, 'ransomware_detection_file');
    }

    /**
     * Find one by id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param int $id
     *
     * @return Entity
     */
    public function find($id, $userId)
    {
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection_file` '.
            'WHERE `id` = ? AND `user_id` = ?';

        return $this->findEntity($sql, [$id, $userId]);
    }

    /**
     * Find one by file name.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param string $name
     *
     * @return Entity
     */
    public function findOneByFileName($name, $userId)
    {
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection_file` '.
            'WHERE `original_name` = ? AND `user_id` = ?';

        return $this->findEntity($sql, [$name, $userId]);
    }

    /**
     * Find the one with the highest id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @return Entity
     */
    public function findOneWithHighestId($userId)
    {
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection_file` WHERE `user_id` = ?'.
            'ORDER BY id DESC LIMIT 1';

        return $this->findEntity($sql, [$userId]);
    }

    /**
     * Find all.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function findAll(array $params = [], $limit = null, $offset = null)
    {
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection_file` WHERE `user_id` = ?';

        return $this->findEntities($sql, $params, $limit, $offset);
    }

    /**
     * Find a sequence by its id.
     *
     * @param array $params
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findSequenceById(array $params = [], $limit = null, $offset = null)
    {
        $sql = 'SELECT * FROM `*PREFIX*ransomware_detection_file` WHERE `sequence` = ? AND `user_id` = ?';

        return $this->findEntities($sql, $params, $limit, $offset);
    }

    /**
     * Delete entity by id.
     *
     * @param int $id
     */
    public function deleteById($id, $userId)
    {
        $sql = 'DELETE FROM `*PREFIX*ransomware_detection_file` WHERE `id` = ? AND `user_id` = ?';
        $stmt = $this->execute($sql, [$id, $userId]);
        $stmt->closeCursor();
    }

    /**
     * Deletes a sequence of file operations.
     *
     * @param int $sequence
     */
    public function deleteSequenceById($sequence, $userId)
    {
        $sql = 'DELETE FROM `*PREFIX*ransomware_detection_file` WHERE `sequence` = ? AND `user_id` = ?';
        $stmt = $this->execute($sql, [$sequence, $userId]);
        $stmt->closeCursor();
    }

    /**
     * Delete all entries before $timestamp.
     *
     * @param int $timestamp
     */
    public function deleteFileOperationsBefore($timestamp)
    {
        $sql = 'DELETE FROM `*PREFIX*ransomware_detection_file` WHERE `timestamp` < ?';
        $stmt = $this->execute($sql, [$timestamp]);
        $stmt->closeCursor();
    }
}
