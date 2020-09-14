<?php

/**
 * @copyright Copyright (c) 2020 Matthias Held <matthias.held@uni-konstanz.de>
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

use OCA\RansomwareDetection\Db\RecoveredFileOperationMapper;
use OCA\RansomwareDetection\Db\FileOperationMapper;

class RecoveredFileOperationService
{
    /** @var RecoveredFileOperationMapper */
    protected $recoveredMapper;

    /** @var FileOperationMapper */
    protected $mapper;

    /** @var string */
    protected $userId;

    /**
     * @param RecoveredFileOperationMapper $recoveredMapper
     * @param FileOperationMapper $mapper
     * @param string              $userId
     */
    public function __construct(
        RecoveredFileOperationMapper $recoveredMapper,
        FileOperationMapper $mapper,
        $userId
    ) {
        $this->recoveredMapper = $recoveredMapper;
        $this->mapper = $mapper;
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
        return $this->recoveredMapper->find($id, $this->userId);
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
        return $this->recoveredMapper->findOneByFileName($name, $this->userId);
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
        return $this->recoveredMapper->findOneWithHighestId($this->userId);
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

        return $this->recoveredMapper->findAll($params, $limit, $offset);
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

        return $this->recoveredMapper->findSequenceById($params, $limit, $offset);
    }

    /**
     * Delete one by id.
     *
     * @param int $id
     */
    public function deleteById($id)
    {
        $fileOperation = $this->recoveredMapper->find($id, $this->userId);
        $this->recoveredMapper->deleteById($id, $this->userId);
        $this->mapper->insert($fileOperation->toFileOperation());
    }

    /**
     * Delete sequence by id.
     *
     * @param int $sequence
     */
    public function deleteSequenceById($sequence)
    {
        $this->recoveredMapper->deleteSequenceById($sequence, $this->userId);
    }

    /**
     * Delete all entries before $timestamp.
     *
     * @param int $timestamp
     */
    public function deleteFileOperationsBefore($timestamp)
    {
        $this->recoveredMapper->deleteFileOperationsBefore($timestamp);
    }
}
