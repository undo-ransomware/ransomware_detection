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

namespace OCA\RansomwareDetection\Scanner;

class StorageStructure {

    /** @var integer */
    protected $numberOfFiles = 0;

    /** @var array */
    protected $files = array();

    /**
     * @param integer $numberOfFiles
     * @param array $files
     */
    public function __construct(
        $numberOfFiles = 0,
        $files = array()
    ) {
        $this->numberOfFiles = $numberOfFiles;
        $this->files = $files;
    }

    /**
     * Get number of files.
     *
     * @return integer
     */
    public function getNumberOfFiles() {
        return $this->numberOfFiles;
    }

    /**
     * Set number of files.
     *
     * @param integer $numberOfFiles
     */
    public function setNumberOfFiles($numberOfFiles) {
        $this->numberOfFiles = $numberOfFiles;
    }

    /**
     * Increase the number of files.
     *
     * @return integer
     */
    public function increaseNumberOfFiles() {
        return $this->numberOfFiles++;
    }

    /**
     * Get files.
     *
     * @return Files[]
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Set files.
     *
     * @param Files[] $files
     */
    public function setFiles($files) {
        $this->files = $files;
    }

    /**
     * Add a file.
     *
     * @param File $file
     */
    public function addFile($file) {
        array_push($this->files, $file);
    }
}
