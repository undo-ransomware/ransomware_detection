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

namespace OCA\RansomwareDetection\Service;

use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Db\RecoveredFileOperationMapper;

class FileOperationService
{
    /** @var FileOperationMapper */
    protected $mapper;

    /** @var RecoveredFileOperationMapper */
    protected $recoveredMapper;

    /** @var string */
    protected $userId;

    /**
     * @param FileOperationMapper $mapper
     * @param RecoveredFileOperationMapper $recoveredMapper
     * @param string              $userId
     */
    public function __construct(
        FileOperationMapper $mapper,
        RecoveredFileOperationMapper $recoveredMapper,
        $userId
    ) {
        $this->mapper = $mapper;
        $this->recoveredMapper = $recoveredMapper;
        $this->userId = $userId;
    }

    /**
     * Find one by the id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param int $id
     *
     * @return Entity
     */
    public function find($id)
    {
        return $this->mapper->find($id, $this->userId);
    }

    /**
     * Find one by the file name.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param string $name
     *
     * @return Entity
     */
    public function findOneByFileName($name)
    {
        return $this->mapper->findOneByFileName($name, $this->userId);
    }

    /**
     * Find one with the highest id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @return Entity
     */
    public function findOneWithHighestId()
    {
        return $this->mapper->findOneWithHighestId($this->userId);
    }

    /**
     * Find all.
     *
     * @param array $params
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findAll(array $params = [], $limit = null, $offset = null)
    {
        array_push($params, $this->userId);

        return $this->mapper->findAll($params, $limit, $offset);
    }

    /**
     * Find sequence by id.
     *
     * @param array $params
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findSequenceById(array $params = [], $limit = null, $offset = null)
    {
        array_push($params, $this->userId);

        return $this->mapper->findSequenceById($params, $limit, $offset);
    }

    /**
     * Delete one by id.
     *
     * @param int $id
     */
    public function deleteById($id, $addToHistory = false)
    {
        $fileOperation = $this->mapper->find($id, $this->userId);
        $this->mapper->deleteById($id, $this->userId);
        if ($addToHistory) {
            $this->recoveredMapper->insert($fileOperation->toRecoveredFileOperation());
        }

    }

    /**
     * Delete sequence by id.
     *
     * @param int $sequence
     */
    public function deleteSequenceById($sequence)
    {
        $params = [];
        array_push($params, $sequence);
        array_push($params, $this->userId);

        $fileOperations = $this->mapper->findSequenceById($params, null, null);
        $this->mapper->deleteSequenceById($sequence, $this->userId);
        foreach ($fileOperations as $fileOperation) {
            $this->recoveredMapper->insert($fileOperation->toRecoveredFileOperation());
        }
    }

    /**
     * Delete all entries before $timestamp.
     *
     * @param int $timestamp
     */
    public function deleteFileOperationsBefore($timestamp)
    {
        $this->mapper->deleteFileOperationsBefore($timestamp);
    }
}
