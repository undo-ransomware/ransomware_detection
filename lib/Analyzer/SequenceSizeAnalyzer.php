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

namespace OCA\RansomwareDetection\Analyzer;

use OCA\RansomwareDetection\Monitor;

class SequenceSizeAnalyzer
{
    /**
     * Size of information files.
     *
     * @var int
     */
    const SIZE_OF_INFO_FILES = 5000000;

    /**
     * Sequence size class.
     *
     * @var int
     */
    const EQUAL_SIZE = 1;
    const DIFF_SIZE = 2;

    /**
     * Compares the sum of the size of the files written and deleted.
     *
     * @param array $sequence
     *
     * @return int
     */
    public function analyze($sequence)
    {
        $sizeOfWrittenFiles = 0;
        $sizeOfDeletedFiles = 0;

        foreach ($sequence as $file) {
            switch ($file->getCommand()) {
                case Monitor::WRITE:
                    $sizeOfWrittenFiles = $sizeOfWrittenFiles + $file->getSize();
                    break;
                case Monitor::READ:
                    break;
                case Monitor::RENAME:
                    break;
                case Monitor::DELETE:
                    $sizeOfDeletedFiles = $sizeOfDeletedFiles + $file->getSize();
                    break;
                default:
                    break;
            }
        }

        if ($sizeOfWrittenFiles <= ($sizeOfDeletedFiles + self::SIZE_OF_INFO_FILES) && $sizeOfWrittenFiles >= $sizeOfDeletedFiles) {
            return self::EQUAL_SIZE;
        } else {
            return self::DIFF_SIZE;
        }
    }
}
