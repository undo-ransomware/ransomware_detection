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

namespace OCA\RansomwareDetection\Analyzer;

class FileNameResult
{
    /**
     * File name classes.
     *
     * @var int
     */
    const NORMAL = 0;
    const SUSPICIOUS_FILE_EXTENSION = 1;
    const SUSPICIOUS_FILE_NAME = 2;
    const SUSPICIOUS = 3;

    /** @var int */
    private $fileNameClass;

    /** @var bool */
    private $isFileExtensionKnown;

    /** @var float */
    private $entropyOfFileName;

    /**
     * @param int   $fileNameClass
     * @param bool  $isFileExtensionKnown
     * @param float $entropyOfFileName
     */
    public function __construct(
        $fileNameClass,
        $isFileExtensionKnown,
        $entropyOfFileName
    ) {
        $this->fileNameClass = $fileNameClass;
        $this->isFileExtensionKnown = $isFileExtensionKnown;
        $this->entropyOfFileName = $entropyOfFileName;
    }

    /**
     * @param int $fileNameClass
     */
    public function setFileNameClass($fileNameClass)
    {
        $this->fileNameClass = $fileNameClass;
    }

    /**
     * @return int
     */
    public function getFileNameClass()
    {
        return $this->fileNameClass;
    }

    /**
     * @param bool $isFileExtensionKnown
     */
    public function setFileExtensionKnown($isFileExtensionKnown)
    {
        $this->isFileExtensionKnown = $isFileExtensionKnown;
    }

    /**
     * @return bool
     */
    public function isFileExtensionKnown()
    {
        return $this->isFileExtensionKnown;
    }

    /**
     * @param float $entropyOfFileName
     */
    public function setEntropyOfFileName($entropyOfFileName)
    {
        $this->entropyOfFileName = $entropyOfFileName;
    }

    /**
     * @return float
     */
    public function getEntropyOfFileName()
    {
        return $this->entropyOfFileName;
    }
}
