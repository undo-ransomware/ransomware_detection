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

namespace OCA\RansomwareDetection\Db;

use OCP\AppFramework\Db\Entity;
use OCA\RansomwareDetection\Db\FileOperation;

class RecoveredFileOperation extends Entity
{
    /** @var string */
    public $userId;

    /** @var string */
    public $path;

    /** @var string */
    public $originalName;

    /** @var string */
    public $newName;

    /** @var string */
    public $type;

    /** @var string */
    public $mimeType;

    /** @var int */
    public $size;

    /** @var int */
    public $corrupted;

    /** @var string */
    public $timestamp;

    /** @var int */
    public $command;

    /** @var int */
    public $sequence;

    /** @var float */
    public $entropy;

    /** @var float */
    public $standardDeviation;

    /** @var string */
    public $fileClass;

    /** @var string */
    public $fileExtensionClass;

    /** @var int */
    public $suspicionClass;

    public function __construct()
    {
        // Add types in constructor
        $this->addType('size', 'integer');
        $this->addType('corrupted', 'integer');
        $this->addType('command', 'integer');
        $this->addType('sequence', 'integer');
        $this->addType('entropy', 'float');
        $this->addType('standardDeviation', 'float');
        $this->addType('suspicionClass', 'integer');
        $this->addType('fileExtensionClass', 'integer');
        $this->addType('fileClass', 'integer');
    }

    public function toFileOperation() {
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($this->getUserId());
        $fileOperation->setPath($this->getPath());
        $fileOperation->setOriginalName($this->getOriginalName());
        $fileOperation->setNewName($this->getNewName());
        $fileOperation->setType($this->getType());
        $fileOperation->setMimeType($this->getMimeType());
        $fileOperation->setSize($this->getSize());
        $fileOperation->setTimestamp($this->getTimestamp());
        $fileOperation->setCorrupted($this->getCorrupted());
        $fileOperation->setCommand($this->getCommand());
        $fileOperation->setSequence($this->getSequence());
        $fileOperation->setEntropy($this->getEntropy());
        $fileOperation->setStandardDeviation($this->getStandardDeviation());
        $fileOperation->setFileClass($this->getFileClass());
        $fileOperation->setFileExtensionClass($this->getFileExtensionClass());
        return $fileOperation;
    }
}
